<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Populate credit_balance for existing customer subscriptions based on their plan's price
     */
    public function up(): void
    {
        // Update subscriptions table to set credit_amount = price if not set
        // (Admin can later update to add bonus)
        DB::table('subscriptions')
            ->whereNull('credit_amount')
            ->orWhere('credit_amount', 0)
            ->update(['credit_amount' => DB::raw('price')]);

        // Update customer_subscriptions to set credit_balance = subscription's credit_amount
        // This gives existing subscribers their full credit balance
        $customerSubscriptions = DB::table('customer_subscriptions')
            ->where('status', 'active')
            ->where(function ($query) {
                $query->whereNull('credit_balance')
                      ->orWhere('credit_balance', 0);
            })
            ->get();

        foreach ($customerSubscriptions as $cs) {
            $subscription = DB::table('subscriptions')->find($cs->subscription_id);
            
            if ($subscription) {
                // Set credit_balance to the subscription's credit_amount (or price if not set)
                $creditAmount = $subscription->credit_amount ?? $subscription->price ?? 0;
                
                DB::table('customer_subscriptions')
                    ->where('id', $cs->id)
                    ->update([
                        'credit_balance' => $creditAmount,
                        'total_credits_used' => 0,
                    ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Cannot reverse - data is populated
    }
};
