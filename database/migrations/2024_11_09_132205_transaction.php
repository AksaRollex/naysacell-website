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
            $table->string('transaction_number');
            $table->string('transaction_message');
            $table->string('transaction_status');
            $table->integer('transaction_total');
            $table->unsignedBigInteger('transaction_user_id');
            $table->enum('payment_status', ['pending', 'success', 'failed']);
            $table->timestamp('payment_date')->nullable();
            // $table->string('transaction_type');
            // $table->string('transaction_sku');
            // $table->string('transaction_provider');
            $table->timestamps();
            $table->foreign('transaction_user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
