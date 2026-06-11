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
            $table->string('order_number')->unique(); // ORD-001
            $table->bigInteger('service_id');
            $table->string('service_name', 500);
            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('state');
            $table->decimal('amount', 10, 2);
            $table->string('status')->default('Completed');
            $table->string('card_brand')->nullable(); // Visa, Mastercard
            $table->string('card_last4')->nullable();  // **** 0001
            $table->string('document_status')->default('Ready');
            $table->string('stripe_transaction_id')->nullable(); // from Stripe ID
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
