<?php

namespace Modules\Setting\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Setting\Models\EmailSetting;

class EmailSettingSeeder extends Seeder
{
    public function run(): void
    {
        EmailSetting::query()->updateOrCreate(
            ['name' => 'Mailtrap local'],
            [
                'mailer' => 'smtp',
                'host' => env('MAILTRAP_HOST', 'sandbox.smtp.mailtrap.io'),
                'port' => (int) env('MAILTRAP_PORT', 2525),
                'encryption' => env('MAILTRAP_ENCRYPTION', 'tls'),
                'username' => env('MAILTRAP_USERNAME', env('MAIL_USERNAME')),
                'password' => env('MAILTRAP_PASSWORD', env('MAIL_PASSWORD')),
                'from_address' => env('MAIL_FROM_ADDRESS', 'hello@example.com'),
                'from_name' => env('MAIL_FROM_NAME', env('APP_NAME', 'GRMS')),
                'is_active' => (bool) env('MAILTRAP_ACTIVE', true),
            ],
        );
    }
}
