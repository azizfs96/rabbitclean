<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Simplify credit system from 5 types to single monetary balance with bonus
     */
    public function up(): void
    {
        // Add credit_amount to subscriptions (what customer gets - with bonus)
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->decimal('credit_amount', 10, 2)->default(0)->after('special_credits')
                ->comment('Credit amount customer receives (with bonus)');
        });

        // Add simplified credit fields to customer_subscriptions
        Schema::table('customer_subscriptions', function (Blueprint $table) {
            $table->decimal('credit_balance', 10, 2)->default(0)->after('special_credits_remaining')
                ->comment('Remaining credit balance');
            $table->decimal('total_credits_used', 10, 2)->default(0)->after('credit_balance')
                ->comment('Total credits used from this subscription');
        });

        // Add subscription credit tracking to orders
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('subscription_credit_used', 10, 2)->nullable()->after('paid_via_subscription')
                ->comment('Amount deducted from subscription credits');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn('credit_amount');
        });

        Schema::table('customer_subscriptions', function (Blueprint $table) {
            $table->dropColumn(['credit_balance', 'total_credits_used']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('subscription_credit_used');
        });
    }
};
