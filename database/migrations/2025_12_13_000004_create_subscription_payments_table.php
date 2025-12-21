<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->foreignId('subscription_id')->constrained()->onDelete('cascade');
            $table->foreignId('customer_subscription_id')->nullable()->constrained()->onDelete('set null');
            
            $table->decimal('amount', 10, 2);
            $table->string('currency', 10)->default('SAR');
            
            $table->enum('payment_gateway', ['paytabs', 'cash', 'bank_transfer', 'wallet'])->default('paytabs');
            $table->enum('payment_status', ['pending', 'completed', 'failed', 'refunded'])->default('pending');
            
            $table->string('transaction_ref')->nullable();
            $table->json('gateway_response')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['customer_id', 'payment_status']);
            $table->index('transaction_ref');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_payments');
    }
};
