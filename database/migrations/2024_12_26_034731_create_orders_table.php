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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->string('product_name');
            $table->string('product_price');
            $table->string('customer_no');
            $table->integer('quantity');
            $table->foreign('product_id')->references('id')->on('product_prepaid')->onDelete('cascade');
            $table->unsignedBigInteger('user_id');
            $table->string('customer_name');
            $table->enum('order_status', ['pending', 'processing', 'success', 'cancelled']);
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
