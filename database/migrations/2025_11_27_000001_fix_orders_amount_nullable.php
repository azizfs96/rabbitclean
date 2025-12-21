<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class FixOrdersAmountNullable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Use raw SQL to ensure the columns are nullable
        DB::statement('ALTER TABLE `orders` MODIFY `amount` DOUBLE UNSIGNED NULL');
        DB::statement('ALTER TABLE `orders` MODIFY `total_amount` DOUBLE UNSIGNED NULL');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('ALTER TABLE `orders` MODIFY `amount` DOUBLE UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE `orders` MODIFY `total_amount` DOUBLE UNSIGNED NOT NULL');
    }
}
