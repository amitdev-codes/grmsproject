<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Modules\Grievance\Models\Grievance;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('grievance_status_histories')
            ->orderBy('id')
            ->get()
            ->each(function (object $history): void {
                $alreadyLogged = DB::table('activity_log')
                    ->where('subject_type', Grievance::class)
                    ->where('subject_id', $history->grievance_id)
                    ->where('event', 'status_changed')
                    ->whereJsonContains('properties->status_history_id', $history->id)
                    ->exists();

                if ($alreadyLogged) {
                    return;
                }

                DB::table('activity_log')->insert([
                    'log_name' => 'grievance',
                    'description' => "Grievance status changed from {$history->from_status} to {$history->to_status}.",
                    'subject_type' => Grievance::class,
                    'subject_id' => $history->grievance_id,
                    'event' => 'status_changed',
                    'causer_type' => $history->actor_id ? 'App\\Models\\User' : null,
                    'causer_id' => $history->actor_id,
                    'attribute_changes' => null,
                    'properties' => json_encode([
                        'backfilled' => true,
                        'status_history_id' => $history->id,
                        'from_status' => $history->from_status,
                        'to_status' => $history->to_status,
                        'actor_role' => $history->actor_role,
                        'reason' => $history->reason,
                    ], JSON_THROW_ON_ERROR),
                    'created_at' => $history->created_at,
                    'updated_at' => $history->created_at,
                ]);
            });
    }

    public function down(): void
    {
        DB::table('activity_log')
            ->where('log_name', 'grievance')
            ->where('event', 'status_changed')
            ->whereJsonContains('properties->backfilled', true)
            ->delete();
    }
};
