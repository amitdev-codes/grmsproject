<?php

use App\Http\Middleware\HandleInertiaRequests;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Permission\Middleware\PermissionMiddleware;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withoutMiddleware([
        HandleInertiaRequests::class,
        PermissionMiddleware::class,
    ]);
    $this->actingAs(User::factory()->create());

    $now = now();
    $divisionId = DB::table('divisions')->insertGetId([
        'ulid' => (string) Str::ulid(),
        'code' => 'RPT',
        'name' => 'Report Division',
        'created_at' => $now,
        'updated_at' => $now,
    ]);
    $categoryId = DB::table('grievance_categories')->insertGetId([
        'ulid' => (string) Str::ulid(),
        'code' => 'RPT',
        'name_en' => 'Report Category',
        'name_st' => 'Report Category',
        'slug' => 'report-category',
        'created_at' => $now,
        'updated_at' => $now,
    ]);
    $channelId = DB::table('grievance_channels')->insertGetId([
        'ulid' => (string) Str::ulid(),
        'code' => 'report-test',
        'name' => 'Report Test',
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    foreach ([
        ['reference_no' => 'RPT-001', 'status' => 'submitted'],
        ['reference_no' => 'RPT-002', 'status' => 'resolved'],
    ] as $index => $case) {
        DB::table('grievances')->insert([
            'ulid' => (string) Str::ulid(),
            'reference_no' => $case['reference_no'],
            'grievance_category_id' => $categoryId,
            'channel_id' => $channelId,
            'division_id' => $divisionId,
            'description' => 'Report test grievance',
            'status' => $case['status'],
            'created_at' => $now->copy()->subMonths($index),
            'updated_at' => $now,
        ]);
    }
});

it('shows summary totals and grouped reports', function () {
    $response = $this->get('/reports/summary', ['X-Inertia' => 'true']);

    $response->assertOk()
        ->assertJsonPath('component', 'Report::Reports/Summary')
        ->assertJsonPath('props.rows.0.group', now()->year)
        ->assertJsonPath('props.rows.0.total', 2)
        ->assertJsonPath('props.rows.0.pending', 1)
        ->assertJsonPath('props.rows.0.resolved', 1);

    $this->get('/reports/summary?group_by=category', ['X-Inertia' => 'true'])
        ->assertOk()
        ->assertJsonPath('props.rows.0.group', 'Report Category')
        ->assertJsonPath('props.rows.0.total', 2);
});

it('shows annex category totals and only finalized grievances in detailed report', function () {
    $this->get('/reports/annex', ['X-Inertia' => 'true'])
        ->assertOk()
        ->assertJsonPath('component', 'Report::Reports/Annex')
        ->assertJsonPath('props.byCategory.0.total', 2)
        ->assertJsonPath('props.byCategory.0.pending', 1)
        ->assertJsonPath('props.byCategory.0.resolved', 1);

    $this->get('/reports/detailed', ['X-Inertia' => 'true'])
        ->assertOk()
        ->assertJsonPath('component', 'Report::Reports/Detailed')
        ->assertJsonPath('props.grievances.total', 1)
        ->assertJsonPath('props.grievances.data.0.reference_no', 'RPT-002');

    $this->get('/reports/detailed?status=resolved', ['X-Inertia' => 'true'])
        ->assertOk()
        ->assertJsonPath('props.grievances.total', 1);
});
