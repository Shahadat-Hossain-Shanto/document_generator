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
        Schema::create('referral_codes', function (Blueprint $table) {
            $table->id();
            $table->string('title'); // Campaign or Agent name (e.g., "Black Friday", "Agent Shafi")
            $table->string('code')->unique(); // Unique Referral Code (e.g., BLACKFRIDAY20)
            $table->enum('type', ['fixed', 'percentage', 'none'])->default('none'); // Discount type ('none' for tracking only)
            $table->decimal('amount', 10, 2)->default(0.00); // 0.00 means no discount, just tracking sales
            $table->integer('used_count')->default(0); // Automatic tracking for total sales
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('referral_codes');
    }
};
