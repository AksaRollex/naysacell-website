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
        Schema::create('transaction', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_code');
            $table->string('transaction_date');
            $table->time('transaction_time');
            $table->string('transaction_message');
            $table->string('transaction_number');
            $table->enum('transaction_status', ['pending', 'success', 'failed', 'process', 'cancelled']);
            $table->enum('order_status', ['pending', 'processing', 'success', 'cancelled']);
            $table->string('transaction_product');
            $table->integer('transaction_total');
            $table->unsignedBigInteger('transaction_user_id');
            $table->foreign('transaction_user_id')->references('id')->on('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction');
    }
};
