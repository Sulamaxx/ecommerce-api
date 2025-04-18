<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('user_guide_pdf')->nullable();
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->decimal('discount', 10, 2);
            $table->string('category'); // beard, hair, accessories
            $table->string('currency')->default(value: "LKR");
            $table->boolean('is_new')->default(true);
            $table->integer('rating')->default(5);
            $table->integer('stock');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
