<?php

namespace Modules\UserManagement\Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;


class UserSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $district = fn (string $code) => \DB::table('districts')->where('code', $code)->value('id');
        $division = fn (string $code) => \DB::table('divisions')->where('code', $code)->value('id');
        $section = fn (string $name) => \DB::table('sections')->where('name', $name)->value('id');

        $fixedUsers = [
            [
                'role' => 'Super Admin',
                'name' => 'Super Admin',
                'username' => 'superadmin',
                'email' => 'superadmin@grms.com',
                'staff' => true,
                'mfa' => true,
            ],
            [
                'role' => 'IT Admin',
                'name' => 'Admin',
                'username' => 'admin',
                'email' => 'admin@grms.com',
                'staff' => true,
                'mfa' => true,
            ],
        ];

        foreach ($fixedUsers as $fixed) {
            $user = User::firstOrCreate(
                ['email' => $fixed['email']],
                [
                    'name' => $fixed['name'],
                    'username' => $fixed['username'],
                    'phone' => $this->lesothoPhone(),
                    'password' => bcrypt('password'), // ⚠ dev-only
                    'bio' => "Works in {$fixed['role']} at the Roads Directorate.",
                    'urls' => json_encode([]),
                    'language' => 'en',
                    'locale' => 'en',
                    'appearance_settings' => json_encode(['theme' => 'system']),
                    'notification_settings' => json_encode([
                        'email' => true,
                        'sms' => false,
                        'in_app' => true,
                    ]),
                    'district_id' => null,
                    'division_id' => null,
                    'section_id' => null,
                    'mfa_enabled' => $fixed['mfa'],
                    'mfa_secret' => null,
                    'status' => true,
                    'email_verified_at' => now(),
                ]
            );

            $user->syncRoles([$fixed['role']]);
        }
        // 20 users, distributed across all 8 roles in a realistic ratio
        // for an org this size: 1 Super Admin, 2 IT Admin, 1 Director,
        // 2 Division Director, 3 Section Manager, 5 Helpdesk Officer,
        // 2 Content Editor, 4 Citizen.
        $plan = [
            ['role' => 'Super Admin', 'count' => 1, 'staff' => true, 'mfa' => true],
            ['role' => 'IT Admin', 'count' => 2, 'staff' => true, 'mfa' => true],
            ['role' => 'Director', 'count' => 1, 'staff' => true, 'mfa' => true],
            ['role' => 'Division Director', 'count' => 2, 'staff' => true, 'mfa' => false],
            ['role' => 'Section Manager', 'count' => 3, 'staff' => true, 'mfa' => false],
            ['role' => 'Helpdesk Officer', 'count' => 5, 'staff' => true, 'mfa' => false],
            ['role' => 'Content Editor', 'count' => 2, 'staff' => true, 'mfa' => false],
            ['role' => 'Citizen', 'count' => 4, 'staff' => false, 'mfa' => false],
        ];

        // Divisions/sections rotate across staff so they're not all piled
        // into one bucket — makes the admin panel's filtered lists testable.
        $orgRotation = [
            ['division' => 'MAINT', 'section' => 'Pothole & Surface Repair', 'district' => 'MSU'],
            ['division' => 'MAINT', 'section' => 'Drainage & Culverts', 'district' => 'BER'],
            ['division' => 'CONST', 'section' => 'Contractor Supervision', 'district' => 'LRB'],
            ['division' => 'SAFE', 'section' => 'Land Acquisition & Compensation', 'district' => 'MFT'],
            ['division' => 'SAFE', 'section' => 'GBV/SEA & Social Safeguards', 'district' => 'QTG'],
            ['division' => 'PLAN', 'section' => 'Project Scheduling', 'district' => 'THT'],
        ];
        $citizenDistricts = ['MSU', 'BER', 'LRB', 'MFT', 'MHK', 'QTG', 'QNK', 'BTB', 'MKH', 'THT'];

        $orgIndex = 0;
        $citizenIndex = 0;
        $created = 0;
        $totalPlanned = array_sum(array_column($plan, 'count'));

        foreach ($plan as $group) {
            for ($i = 1; $i <= $group['count']; $i++) {
                $created++;
                $name = fake()->name();
                $isLastCitizen = $group['role'] === 'Citizen' && $i === $group['count'];

                $divisionCode = $sectionName = $districtCode = null;
                if ($group['staff']) {
                    $org = $orgRotation[$orgIndex % count($orgRotation)];
                    $divisionCode = $org['division'];
                    $sectionName = $org['section'];
                    $districtCode = $org['district'];
                    $orgIndex++;
                } else {
                    $districtCode = $citizenDistricts[$citizenIndex % count($citizenDistricts)];
                    $citizenIndex++;
                }

                // Top-tier roles (Super Admin / IT Admin / Director) get no
                // division/section — their remit isn't scoped to one.
                if (in_array($group['role'], ['Super Admin', 'IT Admin', 'Director'])) {
                    $divisionCode = $sectionName = $districtCode = null;
                }

                $locale = fake()->boolean(30) ? 'st' : 'en'; // ~30% Sesotho-preference users, for testing the toggle

                $user = User::firstOrCreate(
                    ['email' => Str::slug($name, '.').'@'.($group['staff'] ? 'roads.gov.ls' : 'example.com')],
                    [
                        'name' => $name,
                        'username' => fake()->unique()->userName(),
                        'phone' => $this->lesothoPhone(),
                        'password' => bcrypt('password'), // ⚠ dev-only
                        'bio' => $group['staff']
                            ? "Works in {$group['role']} at the Roads Directorate."
                            : (fake()->boolean(60) ? fake()->sentence(10) : null),
                        'urls' => json_encode($this->sampleUrls()),
                        'language' => $locale,
                        'locale' => $locale,
                        'appearance_settings' => json_encode([
                            'theme' => fake()->randomElement(['light', 'dark', 'system']),
                        ]),
                        'notification_settings' => json_encode([
                            'email' => true,
                            'sms' => fake()->boolean(50),
                            'in_app' => true,
                        ]),
                        'district_id' => $districtCode ? $district($districtCode) : null,
                        'division_id' => $divisionCode ? $division($divisionCode) : null,
                        'section_id' => $sectionName ? $section($sectionName) : null,
                        'mfa_enabled' => $group['mfa'],
                        'mfa_secret' => null, // real secret is generated at enrollment time, never seeded
                        'status' => ! $isLastCitizen, // one deliberately inactive user, for testing the deactivated-account flow
                        'email_verified_at' => now(),
                    ]
                );

                $user->syncRoles([$group['role']]);
            }
        }

        $this->command->info("✅ Created/verified {$created} of {$totalPlanned} planned users across 8 roles.");
    }

    private function lesothoPhone(): string
    {
        // Lesotho mobile numbers: +266 5XXXXXXX (Vodacom) or +266 6XXXXXXX (Econet)
        $prefix = fake()->randomElement(['5', '6']);

        return '+266 '.$prefix.fake()->numerify('#######');
    }

    private function sampleUrls(): array
    {
        if (! fake()->boolean(40)) {
            return [];
        }
        $count = fake()->numberBetween(1, 2);
        $urls = [];
        for ($i = 0; $i < $count; $i++) {
            $urls[] = ['label' => fake()->randomElement(['Website', 'LinkedIn']), 'url' => fake()->url()];
        }

        return $urls;
    }
}
