<?php

namespace App\Services;

use App\Models\CreditTransaction;
use App\Models\Customer;
use App\Models\CustomerSubscription;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CreditService
{
    /**
     * Get customer's credit balance
     */
    public function getBalance(Customer $customer): array
    {
        $subscription = CustomerSubscription::where('customer_id', $customer->id)
            ->active()
            ->first();

        if (!$subscription) {
            return [
                'has_subscription' => false,
                'laundry' => 0,
                'clothing' => 0,
                'delivery' => 0,
                'towel' => 0,
                'special' => 0,
                'total' => 0,
                'subscription_end_date' => null,
                'days_remaining' => 0,
            ];
        }

        return [
            'has_subscription' => true,
            'subscription_id' => $subscription->id,
            'subscription_name' => $subscription->subscription->name,
            'laundry' => $subscription->laundry_credits_remaining,
            'clothing' => $subscription->clothing_credits_remaining,
            'delivery' => $subscription->delivery_credits_remaining,
            'towel' => $subscription->towel_credits_remaining,
            'special' => $subscription->special_credits_remaining,
            'total' => $subscription->getTotalCreditsRemaining(),
            'subscription_end_date' => $subscription->end_date->toDateString(),
            'days_remaining' => $subscription->daysRemaining(),
            'auto_renew' => $subscription->auto_renew,
        ];
    }

    /**
     * Add credits to customer
     */
    public function addCredits(
        Customer $customer,
        string $creditType,
        int $amount,
        string $referenceType,
        ?int $referenceId = null,
        ?string $notes = null
    ): CreditTransaction {
        $subscription = CustomerSubscription::where('customer_id', $customer->id)
            ->active()
            ->first();

        $balanceBefore = $subscription ? $subscription->getCredits($creditType) : 0;
        $balanceAfter = $balanceBefore + $amount;

        // Update subscription credits
        if ($subscription) {
            $subscription->addCredits($creditType, $amount);
        }

        // Create transaction record
        return CreditTransaction::create([
            'customer_id' => $customer->id,
            'customer_subscription_id' => $subscription?->id,
            'credit_type' => $creditType,
            'amount' => $amount,
            'transaction_type' => CreditTransaction::TYPE_CREDIT,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'balance_before' => $balanceBefore,
            'balance_after' => $balanceAfter,
            'notes' => $notes,
            'created_by' => Auth::id(),
        ]);
    }

    /**
     * Deduct credits from customer
     */
    public function deductCredits(
        Customer $customer,
        string $creditType,
        int $amount,
        string $referenceType,
        ?int $referenceId = null,
        ?string $notes = null
    ): ?CreditTransaction {
        $subscription = CustomerSubscription::where('customer_id', $customer->id)
            ->active()
            ->first();

        if (!$subscription || !$subscription->hasCredits($creditType, $amount)) {
            return null;
        }

        $balanceBefore = $subscription->getCredits($creditType);
        $balanceAfter = $balanceBefore - $amount;

        // Update subscription credits
        $subscription->deductCredits($creditType, $amount);

        // Create transaction record
        return CreditTransaction::create([
            'customer_id' => $customer->id,
            'customer_subscription_id' => $subscription->id,
            'credit_type' => $creditType,
            'amount' => $amount,
            'transaction_type' => CreditTransaction::TYPE_DEBIT,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'balance_before' => $balanceBefore,
            'balance_after' => $balanceAfter,
            'notes' => $notes,
            'created_by' => Auth::id(),
        ]);
    }

    /**
     * Admin adjustment of credits
     */
    public function adjustCredits(
        Customer $customer,
        string $creditType,
        int $amount,
        string $adjustmentType,
        ?string $notes = null
    ): ?CreditTransaction {
        if ($adjustmentType === 'add') {
            return $this->addCredits($customer, $creditType, $amount, 'admin_adjustment', null, $notes);
        } else {
            return $this->deductCredits($customer, $creditType, $amount, 'admin_adjustment', null, $notes);
        }
    }

    /**
     * Get transaction history for customer
     */
    public function getTransactionHistory(Customer $customer, ?string $creditType = null, int $limit = 50)
    {
        $query = CreditTransaction::where('customer_id', $customer->id)
            ->with('customerSubscription.subscription')
            ->orderBy('created_at', 'desc');

        if ($creditType) {
            $query->where('credit_type', $creditType);
        }

        return $query->limit($limit)->get();
    }

    /**
     * Calculate credits needed for an order
     */
    public function calculateOrderCredits(Order $order): array
    {
        $credits = [
            'laundry' => 0,
            'clothing' => 0,
            'delivery' => 0,
            'towel' => 0,
            'special' => 0,
        ];

        // Calculate based on order products
        foreach ($order->products as $product) {
            $quantity = $product->pivot->quantity ?? 1;
            
            // Determine credit type based on product/variant
            // This logic can be customized based on your product categorization
            $credits['clothing'] += $quantity;
        }

        // Add delivery credit if delivery is requested
        if ($order->delivery_type === 'delivery') {
            $credits['delivery'] = 1;
        }

        return $credits;
    }

    /**
     * Apply credits to an order
     */
    public function applyCreditsToOrder(Customer $customer, Order $order): array
    {
        $subscription = CustomerSubscription::where('customer_id', $customer->id)
            ->active()
            ->first();

        if (!$subscription) {
            return [
                'applied' => false,
                'message' => 'No active subscription',
                'credits_used' => [],
                'remaining_amount' => $order->amount,
            ];
        }

        $requiredCredits = $this->calculateOrderCredits($order);
        $creditsUsed = [];
        $totalSaved = 0;

        DB::transaction(function () use ($customer, $order, $subscription, $requiredCredits, &$creditsUsed, &$totalSaved) {
            foreach ($requiredCredits as $type => $amount) {
                if ($amount > 0 && $subscription->hasCredits($type, $amount)) {
                    $this->deductCredits(
                        $customer,
                        $type,
                        $amount,
                        'order',
                        $order->id,
                        "Credits used for order #{$order->id}"
                    );
                    $creditsUsed[$type] = $amount;
                }
            }
        });

        return [
            'applied' => !empty($creditsUsed),
            'message' => !empty($creditsUsed) ? 'Credits applied successfully' : 'Insufficient credits',
            'credits_used' => $creditsUsed,
            'subscription_id' => $subscription->id,
        ];
    }

    /**
     * Expire remaining credits when subscription expires
     */
    public function expireCredits(CustomerSubscription $subscription): void
    {
        $creditTypes = ['laundry', 'clothing', 'delivery', 'towel', 'special'];
        $customer = $subscription->customer;

        foreach ($creditTypes as $type) {
            $remaining = $subscription->getCredits($type);
            
            if ($remaining > 0) {
                CreditTransaction::create([
                    'customer_id' => $customer->id,
                    'customer_subscription_id' => $subscription->id,
                    'credit_type' => $type,
                    'amount' => $remaining,
                    'transaction_type' => CreditTransaction::TYPE_DEBIT,
                    'reference_type' => 'expiry',
                    'reference_id' => $subscription->id,
                    'balance_before' => $remaining,
                    'balance_after' => 0,
                    'notes' => 'Credits expired with subscription',
                ]);
            }
        }
    }

    /**
     * Refund credits for cancelled order
     */
    public function refundCredits(Customer $customer, Order $order): void
    {
        // Find debit transactions for this order
        $transactions = CreditTransaction::where('customer_id', $customer->id)
            ->where('reference_type', 'order')
            ->where('reference_id', $order->id)
            ->where('transaction_type', CreditTransaction::TYPE_DEBIT)
            ->get();

        foreach ($transactions as $transaction) {
            $this->addCredits(
                $customer,
                $transaction->credit_type,
                $transaction->amount,
                'refund',
                $order->id,
                "Refund for cancelled order #{$order->id}"
            );
        }
    }
}
