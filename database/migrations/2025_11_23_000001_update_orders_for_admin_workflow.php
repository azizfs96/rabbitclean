<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateOrdersForAdminWorkflow extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            // Make amount and total_amount nullable since admin will fill them later
            $table->float('amount')->nullable()->change();
            $table->float('total_amount')->nullable()->change();
            
            // Add service_id to track which service was selected (check if not exists)
            if (!Schema::hasColumn('orders', 'service_id')) {
                $table->foreignId('service_id')->nullable()->after('customer_id');
            }
            
            // Add admin_completed flag to track if admin has finished adding products
            if (!Schema::hasColumn('orders', 'admin_completed')) {
                $table->boolean('admin_completed')->default(false)->after('is_show');
            }
            
            // Add sent_to_customer flag to track if order was sent back to customer
            if (!Schema::hasColumn('orders', 'sent_to_customer')) {
                $table->boolean('sent_to_customer')->default(false)->after('admin_completed');
            }
            
            // Add admin notes field
            if (!Schema::hasColumn('orders', 'admin_notes')) {
                $table->text('admin_notes')->nullable()->after('instruction');
            }
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
            $table->float('amount')->nullable(false)->change();
            $table->float('total_amount')->nullable(false)->change();
            $table->dropColumn(['service_id', 'admin_completed', 'sent_to_customer', 'admin_notes']);
        });
    }
}
