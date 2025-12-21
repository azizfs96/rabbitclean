<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddReviewStatusToOrders extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->enum('review_status', ['pending_review', 'approved_for_payment', 'not_required'])
                  ->default('not_required')
                  ->after('payment_status');
            $table->decimal('admin_adjusted_amount', 10, 2)->nullable()->after('total_amount');
            $table->text('admin_notes')->nullable()->after('admin_adjusted_amount');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['review_status', 'admin_adjusted_amount', 'admin_notes']);
        });
    }
}
