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
        Schema::create('service_areas', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // اسم الحي
            $table->boolean('is_served')->default(true); // حي مخدوم بالكامل
            $table->boolean('allow_with_extra_fee')->default(false); // مسموح مع رسوم إضافية؟
            $table->decimal('extra_delivery_fee', 8, 2)->default(0); // قيمة الرسوم الإضافية
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_areas');
    }
};

