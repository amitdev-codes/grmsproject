<?php

namespace Modules\Setting\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FaqSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faqs = [
            ['q' => 'What counts as a grievance under the LITTL Project?', 'a' => 'Any complaint or concern about road works, land and resettlement, contractor conduct, or project impact — from a road user, community member, or worker.'],
            ['q' => 'Can I report anonymously, especially for sensitive matters?', 'a' => 'Yes. Sensitive reports, including GBV/SEA concerns, can be filed anonymously through a restricted channel seen only by designated caseworkers.'],
            ['q' => 'How are land and resettlement complaints handled?', 'a' => "They route directly to the safeguards officer and link to the project's land acquisition and resettlement register."],
            ['q' => 'Does the system work without a smartphone or internet access?', 'a' => 'Yes. Complaints can be filed by SMS, toll-free hotline, or in person at a site office or suggestion box.'],
            ['q' => 'How does the World Bank review grievance data?', 'a' => 'Consolidated, exportable reports are available for implementation support missions and safeguards supervision.'],
        ];
        foreach ($faqs as $i => $f) {
            DB::table('faqs')->updateOrInsert(
                ['question_en' => $f['q']],
                [
                    'question_en' => $f['q'],
                    'answer_en' => $f['a'],
                    'sort_order' => $i,
                    'is_published' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
        // Site visit counter (backs the footer's "Site visits" figure)
        // -----------------------------------------------------------
        DB::table('page_view_counters')->updateOrInsert(
            ['key' => 'site_total'],
            ['key' => 'site_total', 'count' => 0, 'created_at' => now(), 'updated_at' => now()]
        );

        // -----------------------------------------------------------
        // Settings — SLA thresholds (per the escalation matrix)
        // -----------------------------------------------------------
        DB::table('settings')->updateOrInsert(
            ['key' => 'sla_thresholds'],
            [
                'key' => 'sla_thresholds',
                'value' => json_encode([
                    'acknowledgement_hours' => 48,
                    'escalation_level_1_days' => 5,
                    'escalation_level_2_days' => 7,
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

    }
}
