<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('customer_subscription_id')->nullable()->after('customer_id');
            $table->json('credits_used')->nullable()->after('payment_type');
            $table->boolean('paid_via_subscription')->default(false)->after('credits_used');
            
            $table->foreign('customer_subscription_id')
                  ->references('id')
                  ->on('customer_subscriptions')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['customer_subscription_id']);
            $table->dropColumn(['customer_subscription_id', 'credits_used', 'paid_via_subscription']);
        });
    }
};
