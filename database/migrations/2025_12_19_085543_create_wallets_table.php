<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('wallets', function (Blueprint $table) {
            $table->id();

            // User ID
            $table->unsignedBigInteger('user_id')->nullable();

            // New Custom Fields
            $table->integer('project_count')->default(0); // Default 0 rakha hai
            $table->integer('tree_count')->default(0);    // Default 0 rakha hai

            // Razorpay Payment Keys
            $table->string('razorpay_payment_id')->unique(); // Unique rakhna safe hai
            $table->string('razorpay_order_id');
            $table->string('razorpay_signature')->nullable(); // Future verification ke liye

            // Transaction Details
            $table->decimal('amount', 10, 2);
            $table->string('status')->default('pending'); // Default pending, success verify hone par karenge

            // Created_at and Updated_at (Automatic)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallets');
    }
};
