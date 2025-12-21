<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('credit_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->foreignId('customer_subscription_id')->nullable()->constrained()->onDelete('set null');
            
            // Credit details
            $table->enum('credit_type', ['laundry', 'clothing', 'delivery', 'towel', 'special']);
            $table->integer('amount');
            $table->enum('transaction_type', ['credit', 'debit']);
            
            // Reference
            $table->string('reference_type')->nullable(); // order, subscription, admin_adjustment, topup, expiry
            $table->unsignedBigInteger('reference_id')->nullable();
            
            // Balance tracking
            $table->integer('balance_before')->default(0);
            $table->integer('balance_after')->default(0);
            
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            
            $table->timestamps();
            
            // Indexes
            $table->index(['customer_id', 'credit_type']);
            $table->index(['reference_type', 'reference_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('credit_transactions');
    }
};
