<?php

namespace App\Console\Commands;

use App\Models\Club;
use App\Models\Model3d;
use App\Models\School;
use App\Models\Subject;
use App\Models\User;
use App\Models\VrAssessmentAttempt;
use App\Models\VrExperiment;
use App\Models\VrQuestionBank;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class BackfillPublicIds extends Command
{
    protected $signature = 'public-ids:backfill
                            {resource : One of model3ds, vr-experiments, question-banks, assessment-attempts, subjects, clubs, users, schools}
                            {--dry-run : Report how many rows need a public_id without writing}
                            {--verify : Only report remaining nulls and duplicate counts}';

    protected $description = 'Backfill six-character lowercase public_id values for a selected resource';

    /**
     * @var array<string, array{model: class-string<Model>, table: string, label: string}>
     */
    protected array $resources = [
        'model3ds' => [
            'model' => Model3d::class,
            'table' => 'model3ds',
            'label' => '3D models',
        ],
        'vr-experiments' => [
            'model' => VrExperiment::class,
            'table' => 'vr_experiments',
            'label' => 'VR experiments',
        ],
        'question-banks' => [
            'model' => VrQuestionBank::class,
            'table' => 'vr_question_banks',
            'label' => 'VR question banks',
        ],
        'assessment-attempts' => [
            'model' => VrAssessmentAttempt::class,
            'table' => 'vr_assessment_attempts',
            'label' => 'VR assessment attempts/results',
        ],
        'subjects' => [
            'model' => Subject::class,
            'table' => 'subjects',
            'label' => 'subjects',
        ],
        'clubs' => [
            'model' => Club::class,
            'table' => 'clubs',
            'label' => 'clubs',
        ],
        'users' => [
            'model' => User::class,
            'table' => 'users',
            'label' => 'users',
        ],
        'schools' => [
            'model' => School::class,
            'table' => 'schools',
            'label' => 'schools',
        ],
    ];

    public function handle(): int
    {
        $resource = (string) $this->argument('resource');

        if (! isset($this->resources[$resource])) {
            $this->error('Unknown resource "'.$resource.'". Allowed: '.implode(', ', array_keys($this->resources)));

            return self::FAILURE;
        }

        $config = $this->resources[$resource];
        /** @var class-string<Model> $modelClass */
        $modelClass = $config['model'];

        if ($this->option('verify')) {
            return $this->verify($config);
        }

        $missing = $modelClass::query()->whereNull('public_id')->count();

        if ($this->option('dry-run')) {
            $this->info("Dry run: {$missing} {$config['label']} row(s) are missing public_id.");

            return self::SUCCESS;
        }

        if ($missing === 0) {
            $this->info("All {$config['label']} rows already have a public_id.");

            return $this->verify($config);
        }

        $assigned = 0;

        $modelClass::query()
            ->whereNull('public_id')
            ->orderBy('id')
            ->chunkById(100, function ($models) use (&$assigned): void {
                foreach ($models as $model) {
                    if ($model->assignPublicIdIfMissing()) {
                        $assigned++;
                    }
                }
            });

        $this->info("Assigned public_id to {$assigned} {$config['label']} row(s).");

        return $this->verify($config);
    }

    /**
     * @param  array{model: class-string<Model>, table: string, label: string}  $config
     */
    protected function verify(array $config): int
    {
        /** @var class-string<Model> $modelClass */
        $modelClass = $config['model'];
        $table = $config['table'];
        $label = $config['label'];

        $total = $modelClass::query()->count();
        $missing = $modelClass::query()->whereNull('public_id')->count();
        $duplicates = (int) DB::table($table)
            ->select('public_id')
            ->whereNotNull('public_id')
            ->groupBy('public_id')
            ->havingRaw('COUNT(*) > 1')
            ->get()
            ->count();

        $this->table(
            ['Metric', 'Count'],
            [
                ["Total {$label}", $total],
                ['Missing public_id', $missing],
                ['Duplicate public_id groups', $duplicates],
            ]
        );

        if ($missing > 0 || $duplicates > 0) {
            $this->warn('Verification found issues that need attention.');

            return self::FAILURE;
        }

        $this->info("Verification passed: every {$label} row has a unique public_id.");

        return self::SUCCESS;
    }
}
