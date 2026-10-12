<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Frontend\Services\SiteVisitCounter;

uses(RefreshDatabase::class);

it('counts a visitor IP only once across public page reloads', function () {
    DB::table('page_view_counters')->insert([
        'key' => 'site_total',
        'count' => 31,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $counter = app(SiteVisitCounter::class);
    $counter->record(Request::create('/', 'GET', [], [], [], ['REMOTE_ADDR' => '192.0.2.10']));
    $counter->record(Request::create('/faq', 'GET', [], [], [], ['REMOTE_ADDR' => '192.0.2.10']));

    expect(DB::table('page_view_counters')->where('key', 'site_total')->value('count'))->toBe(32)
        ->and(DB::table('site_visit_uniques')->count())->toBe(1);

    $counter->record(Request::create('/', 'GET', [], [], [], ['REMOTE_ADDR' => '192.0.2.11']));

    expect(DB::table('page_view_counters')->where('key', 'site_total')->value('count'))->toBe(33)
        ->and(DB::table('site_visit_uniques')->count())->toBe(2)
        ->and(DB::table('site_visit_uniques')->value('ip_hash'))->not->toBe('192.0.2.10');
});
