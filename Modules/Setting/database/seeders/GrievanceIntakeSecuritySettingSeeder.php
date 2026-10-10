<?php

namespace Modules\Setting\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Setting\Models\GrievanceIntakeSecuritySetting;

class GrievanceIntakeSecuritySettingSeeder extends Seeder
{
    public function run(): void
    {
        GrievanceIntakeSecuritySetting::query()->firstOrCreate(
            ['id' => 1],
            [
                'lodging_requests_per_minute' => 5,
                'duplicate_window_hours' => 24,
                'whitelisted_ip_addresses' => [],
                'blacklisted_ip_addresses' => [],
                'captcha_provider' => 'local',
            ],
        );
    }
}
