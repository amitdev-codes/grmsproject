<?php

namespace Modules\Setting\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Setting\Models\SmsSetting;

class SmsSettingSeeder extends Seeder
{
    public function run(): void
    {
        SmsSetting::query()->updateOrCreate(
            ['name' => 'SMS provider placeholder'],
            [
                'provider' => 'custom',
                'base_url' => null,
                'api_key' => null,
                'api_secret' => null,
                'sender_id' => 'GRMS',
                'default_country_code' => '+266',
                'is_active' => false,
            ],
        );
    }
}
