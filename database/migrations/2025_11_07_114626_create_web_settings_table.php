<?php

use App\Models\WebSetting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWebSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create((new WebSetting())->getTable(), function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('title')->nullable();
            $table->string('logo')->nullable();
            $table->string('fav_icon')->nullable();
            $table->string('city')->nullable();
            $table->string('road')->nullable();
            $table->string('area')->nullable();
            $table->string('mobile')->nullable();
            $table->string('address')->nullable();
            $table->foreignId('signature_id')->nullable();
            $table->string('currency')->nullable();
            $table->unsignedInteger('otp_expiry_minutes')->default(5);
            $table->unsignedTinyInteger('otp_max_attempts')->default(5);
            $table->unsignedInteger('otp_resend_delay_seconds')->default(60);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists((new WebSetting())->getTable());
    }
}
