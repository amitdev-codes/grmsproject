<?php

namespace Modules\Frontend\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SiteVisitCounter
{
    public function record(Request $request): void
    {
        $ip = $request->ip();

        if (! $ip) {
            return;
        }

        $now = now();
        DB::table('page_view_counters')->insertOrIgnore([
            'key' => 'site_total',
            'count' => 0,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $visitorRecorded = DB::table('site_visit_uniques')->insertOrIgnore([
            'ip_hash' => hash_hmac('sha256', $ip, (string) config('app.key')),
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        if ($visitorRecorded === 1) {
            DB::table('page_view_counters')
                ->where('key', 'site_total')
                ->increment('count');
        }
    }
}
