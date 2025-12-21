<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->string('name')->after('id');
            $table->string('name_ar')->nullable()->after('name');
            $table->text('description')->nullable()->after('name_ar');
            $table->text('description_ar')->nullable()->after('description');
            $table->json('features')->nullable()->after('description_ar');
            $table->integer('laundry_credits')->default(0)->after('price');
            $table->integer('special_credits')->default(0)->after('towel');
            $table->boolean('is_active')->default(true)->after('special_credits');
            $table->boolean('is_featured')->default(false)->after('is_active');
            $table->integer('sort_order')->default(0)->after('is_featured');
            $table->string('color')->nullable()->after('sort_order');
        });

        // Rename columns for clarity
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->renameColumn('clothe', 'clothing_credits');
            $table->renameColumn('towel', 'towel_credits');
            $table->renameColumn('delivery', 'delivery_credits');
        });
    }

    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->renameColumn('clothing_credits', 'clothe');
            $table->renameColumn('towel_credits', 'towel');
            $table->renameColumn('delivery_credits', 'delivery');
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn([
                'name', 'name_ar', 'description', 'description_ar',
                'features', 'laundry_credits', 'special_credits',
                'is_active', 'is_featured', 'sort_order', 'color'
            ]);
        });
    }
};
