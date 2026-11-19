<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_orders', function (Blueprint $table) {
            $table->id();
            $table->ulid('ulid')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subscription_package_id')->constrained()->cascadeOnDelete();
            $table->string('package_slug', 64);
            $table->string('status', 32)->default('pending');
            $table->unsignedInteger('amount_tzs');
            $table->string('currency', 3)->default('TZS');
            $table->unsignedSmallInteger('period_days');
            $table->string('snippe_session_reference')->nullable()->unique();
            $table->string('snippe_payment_reference')->nullable()->unique();
            $table->string('checkout_url', 500)->nullable();
            $table->string('idempotency_key', 30)->unique();
            $table->json('metadata')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('fulfilled_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_orders');
    }
};
