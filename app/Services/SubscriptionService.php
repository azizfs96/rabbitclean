<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\CustomerSubscription;
use App\Models\Subscription;
use App\Models\SubscriptionPayment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SubscriptionService
{
    protected CreditService $creditService;

    public function __construct(CreditService $creditService)
    {
        $this->creditService = $creditService;
    }

    /**
     * Get all active subscription plans
     */
    public function getActivePlans()
    {
        return Subscription::active()->ordered()->get();
    }

    /**
     * Get customer's active subscription
     */
    public function getActiveSubscription(Customer $customer): ?CustomerSubscription
    {
        return CustomerSubscription::where('customer_id', $customer->id)
            ->active()
            ->with('subscription')
            ->first();
    }



    /**
     * Check if customer has active subscription
     */
    public function hasActiveSubscription(Customer $customer): bool
    {
        return $this->getActiveSubscription($customer) !== null;
    }

    /**
     * Purchase a subscription
     */
    public function purchaseSubscription(
        Customer $customer,
        Subscription $plan,
        string $paymentGateway = 'paytabs',
        ?string $transactionRef = null
    ): CustomerSubscription {
        return DB::transaction(function () use ($customer, $plan, $paymentGateway, $transactionRef) {
            // Calculate dates
            $startDate = now();
            $endDate = $this->calculateEndDate($plan);

            // Create customer subscription
            $customerSubscription = CustomerSubscription::create([
                'customer_id' => $customer->id,
                'subscription_id' => $plan->id,
                'credit_balance' => $plan->credit_amount ? $plan->credit_amount : ($plan->price ?? 0),
                'total_credits_used' => 0,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'status' => CustomerSubscription::STATUS_PENDING,
                'amount_paid' => $plan->price,
                'payment_reference' => $transactionRef,
            ]);

            // Create payment record
            $payment = SubscriptionPayment::create([
                'customer_id' => $customer->id,
                'subscription_id' => $plan->id,
                'customer_subscription_id' => $customerSubscription->id,
                'amount' => $plan->price,
                'currency' => 'SAR',
                'payment_gateway' => $paymentGateway,
                'payment_status' => SubscriptionPayment::STATUS_PENDING,
                'transaction_ref' => $transactionRef,
            ]);

            return $customerSubscription;
        });
    }

    /**
     * Activate subscription after successful payment
     * If customer already has an active subscription, add credits to it instead of creating new
     */
    public function activateSubscription(CustomerSubscription $subscription, ?string $transactionRef = null, ?array $gatewayResponse = null): void
    {
        DB::transaction(function () use ($subscription, $transactionRef, $gatewayResponse) {
            // Check for existing subscription with status 'active'
            $existingSubscription = CustomerSubscription::where('customer_id', $subscription->customer_id)
                ->where('id', '!=', $subscription->id)
                ->where('status', CustomerSubscription::STATUS_ACTIVE)
                ->first();

            if ($existingSubscription) {
                // Check if existing subscription should still be active
                $isExpired = $existingSubscription->end_date < now()->toDateString();
                $hasBalance = $existingSubscription->credit_balance > 0;

                if ($isExpired || !$hasBalance) {
                    // Old subscription has expired or has no balance - mark it as expired
                    $existingSubscription->status = CustomerSubscription::STATUS_EXPIRED;
                    $existingSubscription->save();

                    \Log::info('Old subscription marked as expired for renewal', [
                        'old_subscription_id' => $existingSubscription->id,
                        'was_expired_by_date' => $isExpired,
                        'had_balance' => $hasBalance,
                    ]);

                    // Activate new subscription with fresh credits
                    $subscription->status = CustomerSubscription::STATUS_ACTIVE;
                    if ($transactionRef) {
                        $subscription->payment_reference = $transactionRef;
                    }
                    $subscription->save();

                    // Log initial credits for new subscription
                    $this->logInitialCredits($subscription);
                } else {
                    // Old subscription is still valid - add credits to it
                    $creditAmount = $subscription->credit_balance ?? 0;
                    $existingSubscription->credit_balance += $creditAmount;

                    // Extend the end date if the new one is longer
                    if ($subscription->end_date > $existingSubscription->end_date) {
                        $existingSubscription->end_date = $subscription->end_date;
                    }

                    $existingSubscription->save();

                    // Log the credit addition
                    \App\Models\CreditTransaction::create([
                        'customer_id' => $subscription->customer_id,
                        'customer_subscription_id' => $existingSubscription->id,
                        'credit_type' => 'balance',
                        'amount' => $creditAmount,
                        'transaction_type' => 'credit',
                        'reference_type' => 'subscription_renewal',
                        'reference_id' => $subscription->id,
                        'balance_before' => $existingSubscription->credit_balance - $creditAmount,
                        'balance_after' => $existingSubscription->credit_balance,
                        'notes' => "إضافة رصيد من اشتراك جديد: {$creditAmount} ريال",
                    ]);

                    // Mark the new subscription as merged (not active)
                    $subscription->status = 'merged';
                    $subscription->save();

                    \Log::info('Credits added to existing active subscription', [
                        'new_subscription_id' => $subscription->id,
                        'existing_subscription_id' => $existingSubscription->id,
                        'credits_added' => $creditAmount,
                        'new_balance' => $existingSubscription->credit_balance,
                    ]);
                }
            } else {
                // No existing subscription - activate this one normally
                $subscription->status = CustomerSubscription::STATUS_ACTIVE;
                if ($transactionRef) {
                    $subscription->payment_reference = $transactionRef;
                }
                $subscription->save();

                // Log initial credit balance transaction
                $this->logInitialCredits($subscription);
            }

            // Update payment status
            $payment = SubscriptionPayment::where('customer_subscription_id', $subscription->id)
                ->pending()
                ->first();

            if ($payment) {
                $payment->markCompleted($transactionRef, $gatewayResponse);
            }
        });
    }

    /**
     * Cancel subscription
     */
    public function cancelSubscription(CustomerSubscription $subscription): void
    {
        $subscription->cancel();
    }

    /**
     * Renew subscription
     */
    public function renewSubscription(CustomerSubscription $subscription): CustomerSubscription
    {
        $plan = $subscription->subscription;
        $customer = $subscription->customer;

        // Create new subscription starting from current end date
        $startDate = $subscription->end_date->addDay();
        $endDate = $this->calculateEndDate($plan, $startDate);

        return CustomerSubscription::create([
            'customer_id' => $customer->id,
            'subscription_id' => $plan->id,
            'credit_balance' => $plan->credit_amount ? $plan->credit_amount : ($plan->price ?? 0),
            'total_credits_used' => 0,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status' => CustomerSubscription::STATUS_PENDING,
            'auto_renew' => $subscription->auto_renew,
            'amount_paid' => $plan->price,
        ]);
    }

    /**
     * Process expired subscriptions
     */
    public function processExpiredSubscriptions(): int
    {
        $expired = CustomerSubscription::expired()->get();
        $count = 0;

        foreach ($expired as $subscription) {
            // Mark as expired
            $subscription->markExpired();
            $count++;
        }

        return $count;
    }

    /**
     * Get subscriptions expiring soon
     */
    public function getExpiringSoon(int $days = 7)
    {
        return CustomerSubscription::expiringSoon($days)
            ->with(['customer.user', 'subscription'])
            ->get();
    }

    /**
     * Toggle auto-renewal
     */
    public function toggleAutoRenew(CustomerSubscription $subscription): bool
    {
        $subscription->auto_renew = !$subscription->auto_renew;
        $subscription->save();
        return $subscription->auto_renew;
    }

    /**
     * Extend subscription
     */
    public function extendSubscription(CustomerSubscription $subscription, int $days): void
    {
        $subscription->end_date = $subscription->end_date->addDays($days);
        $subscription->save();
    }

    /**
     * Calculate new end date for renewal based on plan validity
     * Starts from current end date (or now if already expired)
     */
    public function calculateRenewalEndDate(Subscription $plan, $currentEndDate): Carbon
    {
        // If subscription already expired, start from now
        $startDate = $currentEndDate instanceof Carbon
            ? ($currentEndDate->isPast() ? now() : $currentEndDate)
            : now();

        return $this->calculateEndDate($plan, $startDate);
    }

    /**
     * Calculate end date based on plan validity
     */
    protected function calculateEndDate(Subscription $plan, ?Carbon $startDate = null): Carbon
    {
        $start = $startDate ?? now();

        return match ($plan->validity_type->value) {
            'days' => $start->copy()->addDays($plan->validity),
            'months' => $start->copy()->addMonths($plan->validity),
            'years' => $start->copy()->addYears($plan->validity),
            default => $start->copy()->addDays($plan->validity),
        };
    }

    /**
     * Log initial credits when subscription is activated
     */
    protected function logInitialCredits(CustomerSubscription $subscription): void
    {
        $plan = $subscription->subscription;
        $creditAmount = $subscription->credit_balance ?? 0;

        if ($creditAmount > 0) {
            \App\Models\CreditTransaction::create([
                'customer_id' => $subscription->customer_id,
                'customer_subscription_id' => $subscription->id,
                'credit_type' => 'balance',
                'amount' => $creditAmount,
                'transaction_type' => 'credit',
                'reference_type' => 'subscription_purchase',
                'reference_id' => $subscription->id,
                'balance_before' => 0,
                'balance_after' => $creditAmount,
                'notes' => "رصيد ابتدائي من اشتراك: {$plan->name}",
            ]);
        }
    }
}
