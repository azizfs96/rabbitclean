<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->foreignId('subscription_id')->constrained()->onDelete('cascade');
            
            // Credits Balance
            $table->integer('laundry_credits_remaining')->default(0);
            $table->integer('clothing_credits_remaining')->default(0);
            $table->integer('delivery_credits_remaining')->default(0);
            $table->integer('towel_credits_remaining')->default(0);
            $table->integer('special_credits_remaining')->default(0);
            
            // Dates
            $table->date('start_date');
            $table->date('end_date');
            
            // Status
            $table->enum('status', ['active', 'expired', 'cancelled', 'pending'])->default('pending');
            $table->boolean('auto_renew')->default(false);
            
            // Payment Info
            $table->decimal('amount_paid', 10, 2)->nullable();
            $table->string('payment_reference')->nullable();
            
            $table->timestamps();
            
            // Index for quick lookups
            $table->index(['customer_id', 'status']);
            $table->index('end_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_subscriptions');
    }
};
