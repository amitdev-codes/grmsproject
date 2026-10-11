<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Grievance\Datatable\ResolutionDataTable;

uses(RefreshDatabase::class);

it('loads resolution relationships and applies the state filter', function () {
    $proposer = User::factory()->create(['name' => 'Resolution Proposer']);
    $approver = User::factory()->create(['name' => 'Resolution Approver']);
    $now = now();

    $categoryId = DB::table('grievance_categories')->insertGetId([
        'ulid' => (string) Str::ulid(),
        'code' => 'RES',
        'name_en' => 'Resolution Test',
        'name_st' => 'Resolution Test',
        'slug' => 'resolution-test',
        'created_at' => $now,
        'updated_at' => $now,
    ]);
    $channelId = DB::table('grievance_channels')->insertGetId([
        'ulid' => (string) Str::ulid(),
        'code' => 'resolution-test',
        'name' => 'Resolution Test',
        'created_at' => $now,
        'updated_at' => $now,
    ]);
    $grievanceId = DB::table('grievances')->insertGetId([
        'ulid' => (string) Str::ulid(),
        'reference_no' => 'RES-001',
        'grievance_category_id' => $categoryId,
        'channel_id' => $channelId,
        'description' => 'Resolution test grievance',
        'status' => 'resolved',
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    DB::table('resolutions')->insert([
        'ulid' => (string) Str::ulid(),
        'grievance_id' => $grievanceId,
        'proposed_by' => $proposer->id,
        'approved_by' => $approver->id,
        'resolution_text' => 'Approved resolution',
        'approved_at' => $now,
        'created_at' => $now,
        'updated_at' => $now,
    ]);
    DB::table('resolutions')->insert([
        'ulid' => (string) Str::ulid(),
        'grievance_id' => $grievanceId,
        'proposed_by' => $proposer->id,
        'resolution_text' => 'Proposed resolution',
        'created_at' => $now->copy()->subMinute(),
        'updated_at' => $now,
    ]);

    $result = app(ResolutionDataTable::class)->toArray(
        Request::create('/resolutions', 'GET', ['filters' => ['state' => 'approved']]),
    );

    expect($result['meta']['total'])->toBe(1)
        ->and($result['data'][0]->grievance->reference_no)->toBe('RES-001')
        ->and($result['data'][0]->proposedByUser->name)->toBe('Resolution Proposer')
        ->and($result['data'][0]->approvedByUser->name)->toBe('Resolution Approver')
        ->and($result['data'][0]->toArray()['approved_by_user']['name'])->toBe('Resolution Approver');
});
