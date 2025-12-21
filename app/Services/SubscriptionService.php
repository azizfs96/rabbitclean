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
                'laundry_credits_remaining' => $plan->laundry_credits,
                'clothing_credits_remaining' => $plan->clothing_credits,
                'delivery_credits_remaining' => $plan->delivery_credits,
                'towel_credits_remaining' => $plan->towel_credits,
                'special_credits_remaining' => $plan->special_credits,
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
     */
    public function activateSubscription(CustomerSubscription $subscription, ?string $transactionRef = null, ?array $gatewayResponse = null): void
    {
        DB::transaction(function () use ($subscription, $transactionRef, $gatewayResponse) {
            // Cancel any existing active subscriptions
            CustomerSubscription::where('customer_id', $subscription->customer_id)
                ->where('id', '!=', $subscription->id)
                ->where('status', CustomerSubscription::STATUS_ACTIVE)
                ->update(['status' => CustomerSubscription::STATUS_CANCELLED]);

            // Activate the subscription
            $subscription->status = CustomerSubscription::STATUS_ACTIVE;
            if ($transactionRef) {
                $subscription->payment_reference = $transactionRef;
            }
            $subscription->save();

            // Update payment status
            $payment = SubscriptionPayment::where('customer_subscription_id', $subscription->id)
                ->pending()
                ->first();

            if ($payment) {
                $payment->markCompleted($transactionRef, $gatewayResponse);
            }

            // Log credit transactions
            $this->logInitialCredits($subscription);
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
            'laundry_credits_remaining' => $plan->laundry_credits,
            'clothing_credits_remaining' => $plan->clothing_credits,
            'delivery_credits_remaining' => $plan->delivery_credits,
            'towel_credits_remaining' => $plan->towel_credits,
            'special_credits_remaining' => $plan->special_credits,
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
            // Log remaining credits as expired
            $this->creditService->expireCredits($subscription);
            
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
     * Calculate end date based on plan validity
     */
    protected function calculateEndDate(Subscription $plan, ?Carbon $startDate = null): Carbon
    {
        $start = $startDate ?? now();
        
        return match($plan->validity_type->value) {
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
        $creditTypes = [
            'laundry' => $plan->laundry_credits,
            'clothing' => $plan->clothing_credits,
            'delivery' => $plan->delivery_credits,
            'towel' => $plan->towel_credits,
            'special' => $plan->special_credits,
        ];

        foreach ($creditTypes as $type => $amount) {
            if ($amount > 0) {
                $this->creditService->addCredits(
                    $subscription->customer,
                    $type,
                    $amount,
                    'subscription',
                    $subscription->id,
                    "Initial credits from {$plan->name} subscription"
                );
            }
        }
    }
}
