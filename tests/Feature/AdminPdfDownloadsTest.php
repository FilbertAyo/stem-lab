<?php

use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

require_once __DIR__.'/../Helpers/CompetitionTestHelper.php';

function pdfDownloadAdminUrl(string $path): string
{
    $host = parse_url(config('app.admin_url'), PHP_URL_HOST) ?: 'admin.test';

    return 'http://'.$host.$path;
}

function assertPdfDownload($response): void
{
    $response->assertOk();
    expect($response->headers->get('content-type'))->toContain('application/pdf');
    expect($response->headers->get('content-disposition'))->toContain('.pdf');
}

test('admin can download classes and subjects pdf lists', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    SchoolClass::query()->create([
        'name' => 'Form One '.Str::random(4),
        'description' => 'Test class',
        'is_active' => true,
    ]);

    Subject::query()->create([
        'subject_name' => 'Biology '.Str::random(4),
        'description' => 'Test subject',
        'display_order' => 1,
        'is_active' => true,
    ]);

    assertPdfDownload(
        $this->actingAs($admin)
            ->withServerVariables(['HTTP_HOST' => adminRequestHost()])
            ->get(pdfDownloadAdminUrl('/classes/export'))
    );

    assertPdfDownload(
        $this->actingAs($admin)
            ->withServerVariables(['HTTP_HOST' => adminRequestHost()])
            ->get(pdfDownloadAdminUrl('/subjects/export'))
    );
});

test('admin can download filtered models vr experiments and projects pdf lists', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    assertPdfDownload(
        $this->actingAs($admin)
            ->withServerVariables(['HTTP_HOST' => adminRequestHost()])
            ->get(pdfDownloadAdminUrl('/models/3d/export?search=none&status=active'))
    );

    assertPdfDownload(
        $this->actingAs($admin)
            ->withServerVariables(['HTTP_HOST' => adminRequestHost()])
            ->get(pdfDownloadAdminUrl('/vr-experiments/export?search=none'))
    );

    assertPdfDownload(
        $this->actingAs($admin)
            ->withServerVariables(['HTTP_HOST' => adminRequestHost()])
            ->get(pdfDownloadAdminUrl('/projects/export?status=active'))
    );
});

test('download templates start with sn and omit ownership columns', function () {
    $html = view('admin.downloads.classes-list', [
        'classes' => collect(),
        'title' => 'Classes',
        'generatedAt' => now(),
        'filterSummary' => null,
    ])->render();

    expect($html)->toContain('S/N')
        ->and($html)->not->toContain('Created by')
        ->and($html)->not->toContain('Updated by')
        ->and($html)->not->toContain('Last Updated')
        ->and($html)->not->toContain('created_at')
        ->and($html)->not->toContain('updated_at');

    $modelsHtml = view('admin.downloads.models-3d-list', [
        'models' => collect(),
        'title' => '3D Models',
        'generatedAt' => now(),
        'filterSummary' => 'Search: demo',
    ])->render();

    expect($modelsHtml)->toContain('S/N')
        ->and($modelsHtml)->not->toContain('Created by')
        ->and($modelsHtml)->not->toContain('Updated at');
});
