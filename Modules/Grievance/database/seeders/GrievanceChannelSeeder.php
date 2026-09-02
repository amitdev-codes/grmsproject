<?php

namespace Modules\Grievance\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Grievance\Models\GrievanceChannel;

class GrievanceChannelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        collect([
            ['code' => 'web', 'name' => 'RD Website'],
            ['code' => 'mobile_app', 'name' => 'Mobile App'],
            ['code' => 'sms', 'name' => 'SMS'],
            ['code' => 'ussd', 'name' => 'USSD'],
            ['code' => 'whatsapp', 'name' => 'WhatsApp'],
            ['code' => 'helpdesk', 'name' => 'Helpdesk Officer'],
            ['code' => 'box', 'name' => 'Grievance Box'],
            ['code' => 'grc', 'name' => 'Grievance Redress Committee'],
            ['code' => 'chief', 'name' => 'Chief / Community Councillor'],
            ['code' => 'social_media', 'name' => 'Social Media'],
        ])->each(fn ($c) => GrievanceChannel::updateOrCreate(['code' => $c['code']], $c));
    }
}
