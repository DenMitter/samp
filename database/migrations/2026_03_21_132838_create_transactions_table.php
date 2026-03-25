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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('offer_id')->nullable()->constrained('offers')->nullOnDelete();
            $table->foreignId('buyer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('seller_id')->constrained('users')->cascadeOnDelete();
            $table->string('reference')->unique();
            $table->string('currency', 3)->default('USD');
            $table->decimal('amount', 12, 2);
            $table->unsignedInteger('inspection_period_days')->default(3);
            $table->string('status')->default('pending');
            $table->string('payment_status')->default('unpaid');
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('released_at')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
