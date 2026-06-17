<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_packages', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('tier_key', 32);
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('price_heading')->nullable();
            $table->string('price_subheading')->nullable();
            $table->string('price_compare')->nullable();
            $table->string('badge_label')->nullable();
            $table->string('cta_label');
            $table->string('cta_action', 64);
            $table->json('features')->nullable();
            $table->string('linked_title')->nullable();
            $table->text('linked_description')->nullable();
            $table->json('linked_features')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
            $table->index('tier_key');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_packages');
    }
};
