<?php

namespace Database\Seeders;

use App\Models\Equipment;
use App\Models\Platform;
use App\Models\Project;
use App\Models\ProjectCategory;
use App\Models\ProjectTag;
use App\Models\SchoolClass;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProjectSeeder extends Seeder
{
    public function run(): void
    {
        $directory = database_path('seeders/data/projects');
        $files = glob($directory.'/*.json') ?: [];

        if ($files === []) {
            $this->command?->error("No project JSON files found in {$directory}");

            return;
        }

        sort($files);

        $created = 0;
        $updated = 0;

        foreach ($files as $path) {
            $projects = json_decode(file_get_contents($path), true);

            if (! is_array($projects)) {
                $this->command?->warn('Skipping invalid JSON: '.basename($path));

                continue;
            }

            $this->command?->info('Seeding from '.basename($path).' ('.count($projects).' projects)');

            foreach ($projects as $entry) {
                $slug = $entry['slug'] ?? Str::slug($entry['name'] ?? '');

                if ($slug === '') {
                    $this->command?->warn('Skipping project with no name/slug.');

                    continue;
                }

                DB::transaction(function () use ($entry, $slug, &$created, &$updated) {
                    $categoryId = null;
                    if (! empty($entry['category_slug'])) {
                        $categoryId = ProjectCategory::where('slug', $entry['category_slug'])->value('id');
                    }

                    $schoolClassId = null;
                    if (! empty($entry['school_class'])) {
                        $schoolClassId = SchoolClass::where('name', $entry['school_class'])->value('id');
                    }

                    $project = Project::firstOrNew(['slug' => $slug]);
                    $isNew = ! $project->exists;

                    $project->fill([
                        'project_category_id' => $categoryId,
                        'school_class_id' => $schoolClassId,
                        'name' => $entry['name'],
                        'difficulty' => $entry['difficulty'] ?? 'beginner',
                        'estimated_time' => $entry['estimated_time'] ?? null,
                        'youtube_url' => $entry['youtube_url'] ?? null,
                        'external_link' => $entry['external_link'] ?? null,
                        'description' => $entry['description'] ?? null,
                        'guide' => $entry['guide'] ?? null,
                        'is_active' => $entry['is_active'] ?? true,
                    ]);

                    $project->save();

                    $this->syncPlatforms($project, $entry['platform_slugs'] ?? []);
                    $this->syncTags($project, $entry['tags'] ?? []);
                    $this->syncEquipment($project, $entry['equipment'] ?? []);
                    $this->syncCodes($project, $entry['codes'] ?? []);
                    $this->syncResources($project, $entry['resources'] ?? []);

                    $isNew ? $created++ : $updated++;
                });
            }
        }

        $this->command?->info("ProjectSeeder finished: {$created} created, {$updated} updated.");
    }

    /**
     * @param  array<int, string>  $slugs
     */
    private function syncPlatforms(Project $project, array $slugs): void
    {
        if ($slugs === []) {
            $project->platforms()->sync([]);

            return;
        }

        $ids = Platform::whereIn('slug', $slugs)->pluck('id');
        $project->platforms()->sync($ids);
    }

    /**
     * @param  array<int, string>  $tags
     */
    private function syncTags(Project $project, array $tags): void
    {
        $ids = [];

        foreach ($tags as $name) {
            $name = trim((string) $name);
            if ($name === '') {
                continue;
            }

            $slug = Str::slug($name);
            $tag = ProjectTag::firstOrCreate(
                ['name' => $name],
                ['slug' => $slug]
            );

            if (! $tag->slug) {
                $tag->update(['slug' => $slug]);
            }

            $ids[] = $tag->id;
        }

        $project->tags()->sync($ids);
    }

    /**
     * @param  array<int, array{name: string, quantity?: string, notes?: string}>  $rows
     */
    private function syncEquipment(Project $project, array $rows): void
    {
        $sync = [];

        foreach ($rows as $row) {
            $name = trim((string) ($row['name'] ?? ''));
            if ($name === '') {
                continue;
            }

            $equipment = Equipment::firstOrCreate(['name' => $name]);

            $sync[$equipment->id] = [
                'quantity' => isset($row['quantity']) ? trim((string) $row['quantity']) : null,
                'notes' => isset($row['notes']) ? trim((string) $row['notes']) : null,
            ];
        }

        $project->equipment()->sync($sync);
    }

    /**
     * @param  array<int, array{title: string, language?: string, code: string, description?: string}>  $codes
     */
    private function syncCodes(Project $project, array $codes): void
    {
        $project->codes()->delete();

        $order = 0;

        foreach ($codes as $block) {
            $title = trim((string) ($block['title'] ?? ''));
            $body = trim((string) ($block['code'] ?? ''));

            if ($title === '' && $body === '') {
                continue;
            }

            $order++;

            $project->codes()->create([
                'title' => $title ?: 'Code snippet',
                'language' => trim((string) ($block['language'] ?? '')) ?: 'Plain',
                'code' => $body ?: ' ',
                'description' => trim((string) ($block['description'] ?? '')) ?: null,
                'sort_order' => $order,
            ]);
        }
    }

    /**
     * @param  array<int, array{title: string, type?: string, url?: string}>  $resources
     */
    private function syncResources(Project $project, array $resources): void
    {
        $project->resources()->delete();

        $order = 0;

        foreach ($resources as $resource) {
            $title = trim((string) ($resource['title'] ?? ''));
            if ($title === '') {
                continue;
            }

            $order++;

            $project->resources()->create([
                'title' => $title,
                'type' => $resource['type'] ?? 'link',
                'url' => trim((string) ($resource['url'] ?? '')) ?: null,
                'sort_order' => $order,
            ]);
        }
    }
}
