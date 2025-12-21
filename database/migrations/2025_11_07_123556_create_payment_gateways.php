<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Media;

class CreatePaymentGateways extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment_gateways', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('title');
            $table->foreignIdFor(Media::class)->nullable()->constrained()->nullOnDelete();
            $table->string('mode')->default('test')->comment('test or live');
            $table->string('alias')->nullable()->comment('controller namespace');
            $table->json('config')->nullable();
            $table->boolean('is_active')->default(false);
            $table->unsignedBigInteger('shop_id')->nullable();
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
        Schema::dropIfExists('payment_gateways');
    }
}
