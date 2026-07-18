<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->index(
                ['project_category_id', 'is_active'],
                'projects_category_active_idx',
            );
            $table->index(
                ['difficulty', 'is_active'],
                'projects_difficulty_active_idx',
            );
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->dropIndex('projects_category_publish_active_idx');
            $table->dropIndex('projects_difficulty_publish_active_idx');
            $table->dropColumn(['is_published', 'published_at']);
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->boolean('is_published')->default(false)->after('embedded_code');
            $table->timestamp('published_at')->nullable()->after('is_active');
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->index(
                ['project_category_id', 'is_published', 'is_active'],
                'projects_category_publish_active_idx',
            );
            $table->index(
                ['difficulty', 'is_published', 'is_active'],
                'projects_difficulty_publish_active_idx',
            );
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->dropIndex('projects_category_active_idx');
            $table->dropIndex('projects_difficulty_active_idx');
        });
    }
};
