<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('snippe_webhook_events', function (Blueprint $table) {
            $table->id();
            $table->string('event_id')->unique();
            $table->string('event_type', 64);
            $table->string('payment_reference')->nullable()->index();
            $table->string('session_reference')->nullable()->index();
            $table->foreignId('subscription_order_id')->nullable()->constrained()->nullOnDelete();
            $table->string('processing_status', 32)->default('processed');
            $table->json('payload');
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('snippe_webhook_events');
    }
};
