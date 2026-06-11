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
        Schema::create('service_fields', function (Blueprint $table) {
            $table->id();

            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_step_id')->constrained()->cascadeOnDelete();

            $table->integer('column')->default(1);

            $table->string('label');
            $table->string('document_key')->index();

            $table->enum('type', [
                'text','number','email','date',
                'radio','textarea','select',
                'rich_text','effective_date'
            ]);

            $table->string('placeholder')->nullable();
            $table->boolean('required')->default(false);

            $table->json('options')->nullable();
            $table->unsignedInteger('order')->default(0);

            $table->timestamps();

            $table->unique(['service_id', 'document_key']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_fields');
    }
};
