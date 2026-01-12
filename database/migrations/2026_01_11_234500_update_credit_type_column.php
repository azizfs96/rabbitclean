<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Update credit_type column to accept 'balance' value for simplified credit system
     */
    public function up(): void
    {
        // Check if using enum - modify to accept 'balance'
        // Since we can't easily modify enum in MySQL, we'll change to varchar
        Schema::table('credit_transactions', function (Blueprint $table) {
            $table->string('credit_type', 20)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to original if needed
    }
};
