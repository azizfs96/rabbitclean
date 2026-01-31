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
     * Get customer's credit balance (Simplified - single balance)
     */
    public function getBalance(Customer $customer): array
    {
        $subscription = CustomerSubscription::where('customer_id', $customer->id)
            ->active()
            ->first();

        if (!$subscription) {
            return [
                'has_subscription' => false,
                'credit_balance' => 0,
                'total_credits_used' => 0,
                'subscription_end_date' => null,
                'days_remaining' => 0,
                'auto_renew' => false,
            ];
        }

        return [
            'has_subscription' => true,
            'subscription_id' => $subscription->id,
            'subscription_name' => $subscription->subscription?->name,
            'subscription_name_ar' => $subscription->subscription?->name_ar,
            'credit_balance' => (float) ($subscription->credit_balance ?? 0),
            'total_credits_used' => (float) ($subscription->total_credits_used ?? 0),
            'subscription_end_date' => $subscription->end_date?->toDateString(),
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

    // ========== NEW SIMPLIFIED CREDIT SYSTEM ==========

    /**
     * Get simplified credit balance (single balance instead of 5 types)
     */
    public function getSimplifiedBalance(Customer $customer): array
    {
        $subscription = CustomerSubscription::where('customer_id', $customer->id)
            ->active()
            ->first();

        if (!$subscription) {
            return [
                'has_subscription' => false,
                'credit_balance' => 0,
                'total_credits_used' => 0,
                'subscription_end_date' => null,
                'days_remaining' => 0,
            ];
        }

        return [
            'has_subscription' => true,
            'subscription_id' => $subscription->id,
            'subscription_name' => $subscription->subscription?->name,
            'subscription_name_ar' => $subscription->subscription?->name_ar,
            'credit_balance' => (float) $subscription->credit_balance,
            'total_credits_used' => (float) $subscription->total_credits_used,
            'subscription_end_date' => $subscription->end_date?->toDateString(),
            'days_remaining' => $subscription->daysRemaining(),
            'auto_renew' => $subscription->auto_renew,
        ];
    }

    /**
     * Apply simplified credits to order (deduct order total from credit_balance)
     * Supports partial deduction if balance is less than order total
     */
    public function applySimplifiedCreditsToOrder(Customer $customer, Order $order): array
    {
        $subscription = CustomerSubscription::where('customer_id', $customer->id)
            ->active()
            ->first();

        if (!$subscription) {
            return [
                'applied' => false,
                'partial' => false,
                'message' => 'No active subscription',
                'amount_used' => 0,
                'remaining_to_pay' => (float) $order->total_amount,
            ];
        }

        $orderTotal = (float) $order->total_amount;
        $availableBalance = $subscription->getCreditBalance();

        if ($orderTotal <= 0) {
            return [
                'applied' => false,
                'partial' => false,
                'message' => 'Order has no amount to pay',
                'amount_used' => 0,
                'remaining_to_pay' => 0,
            ];
        }

        // No credits available at all
        if ($availableBalance <= 0) {
            return [
                'applied' => false,
                'partial' => false,
                'message' => 'No credit balance available',
                'amount_used' => 0,
                'credit_balance' => 0,
                'remaining_to_pay' => $orderTotal,
            ];
        }

        // Calculate how much to deduct (full or partial)
        $amountToDeduct = min($availableBalance, $orderTotal);
        $remainingToPay = $orderTotal - $amountToDeduct;
        $isPartial = $amountToDeduct < $orderTotal;

        // Deduct credits
        DB::transaction(function () use ($customer, $order, $subscription, $amountToDeduct, $isPartial) {
            $balanceBefore = $subscription->getCreditBalance();
            
            // Deduct from subscription
            $subscription->credit_balance -= $amountToDeduct;
            $subscription->total_credits_used += $amountToDeduct;
            $subscription->save();
            
            // Log the transaction
            CreditTransaction::create([
                'customer_id' => $customer->id,
                'customer_subscription_id' => $subscription->id,
                'credit_type' => 'balance',
                'amount' => $amountToDeduct,
                'transaction_type' => CreditTransaction::TYPE_DEBIT,
                'reference_type' => 'order',
                'reference_id' => $order->id,
                'balance_before' => $balanceBefore,
                'balance_after' => $subscription->getCreditBalance(),
                'notes' => $isPartial 
                    ? "Partial payment for Order #{$order->prefix}{$order->order_code} - Credits used: {$amountToDeduct} SAR"
                    : "Order #{$order->prefix}{$order->order_code} - Credits used: {$amountToDeduct} SAR",
                'created_by' => Auth::id(),
            ]);
        });

        return [
            'applied' => true,
            'partial' => $isPartial,
            'message' => $isPartial 
                ? "Partial payment: {$amountToDeduct} SAR from credits, {$remainingToPay} SAR remaining to pay"
                : "Full payment: {$amountToDeduct} SAR from credits",
            'amount_used' => $amountToDeduct,
            'subscription_id' => $subscription->id,
            'remaining_balance' => $subscription->getCreditBalance(),
            'remaining_to_pay' => $remainingToPay,
        ];
    }

    /**
     * Refund simplified credits for cancelled order
     */
    public function refundSimplifiedCredits(Customer $customer, Order $order): void
    {
        if (!$order->paid_via_subscription || !$order->subscription_credit_used) {
            return;
        }

        $subscription = CustomerSubscription::find($order->customer_subscription_id);
        
        if (!$subscription) {
            return;
        }

        $refundAmount = (float) $order->subscription_credit_used;
        
        if ($refundAmount > 0) {
            $balanceBefore = $subscription->getCreditBalance();
            
            // Add back the credits
            $subscription->addCreditBalance($refundAmount);
            $subscription->total_credits_used -= $refundAmount;
            $subscription->save();
            
            // Log the refund transaction
            CreditTransaction::create([
                'customer_id' => $customer->id,
                'customer_subscription_id' => $subscription->id,
                'credit_type' => 'balance',
                'amount' => $refundAmount,
                'transaction_type' => CreditTransaction::TYPE_CREDIT,
                'reference_type' => 'refund',
                'reference_id' => $order->id,
                'balance_before' => $balanceBefore,
                'balance_after' => $subscription->getCreditBalance(),
                'notes' => "Refund for cancelled order #{$order->prefix}{$order->order_code}",
                'created_by' => Auth::id(),
            ]);
        }
    }
}
