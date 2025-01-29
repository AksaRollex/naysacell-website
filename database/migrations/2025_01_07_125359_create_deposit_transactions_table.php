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
        Schema::create('deposit_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id'); 
            $table->string('user_name');
            $table->decimal('amount', 10, 2); 
            $table->enum('status', ['pending', 'success', 'failed']);
            $table->string('deposit_code');
            $table->string('user_number');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade'); 
            $table->timestamps(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deposit_transactions');
    }
};
