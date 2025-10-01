<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('pending_payhere_orders', function (Blueprint $table) {
            $table->id();
            $table->string('temp_order_id')->unique();
            $table->unsignedBigInteger('user_id');
            $table->json('order_data');
            $table->timestamps();
            
            $table->index('temp_order_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('pending_payhere_orders');
    }
};