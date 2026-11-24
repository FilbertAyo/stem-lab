<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Additive only. Does not drop tables, columns, or existing rows.
     */
    public function up(): void
    {
        Schema::table('competition_prototype_submissions', function (Blueprint $table): void {
            if (! Schema::hasColumn('competition_prototype_submissions', 'video_path')) {
                $table->string('video_path')->nullable()->after('prototype_next_steps');
            }
        });

        Schema::table('competition_prototype_modifications', function (Blueprint $table): void {
            if (! Schema::hasColumn('competition_prototype_modifications', 'video_path')) {
                $table->string('video_path')->nullable()->after('prototype_next_steps');
            }
            if (! Schema::hasColumn('competition_prototype_modifications', 'prototype_media_notes')) {
                $table->text('prototype_media_notes')->nullable()->after('video_path');
            }
        });

        if (! Schema::hasTable('competition_prototype_images')) {
            Schema::create('competition_prototype_images', function (Blueprint $table): void {
                $table->id();
                $table->string('imageable_type');
                $table->unsignedBigInteger('imageable_id');
                $table->string('file_path');
                $table->string('original_filename')->nullable();
                $table->unsignedSmallInteger('sort_order')->default(0);
                $table->timestamps();

                $table->index(['imageable_type', 'imageable_id'], 'cpi_imageable_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('competition_prototype_images');

        Schema::table('competition_prototype_modifications', function (Blueprint $table): void {
            if (Schema::hasColumn('competition_prototype_modifications', 'prototype_media_notes')) {
                $table->dropColumn('prototype_media_notes');
            }
            if (Schema::hasColumn('competition_prototype_modifications', 'video_path')) {
                $table->dropColumn('video_path');
            }
        });

        Schema::table('competition_prototype_submissions', function (Blueprint $table): void {
            if (Schema::hasColumn('competition_prototype_submissions', 'video_path')) {
                $table->dropColumn('video_path');
            }
        });
    }
};
