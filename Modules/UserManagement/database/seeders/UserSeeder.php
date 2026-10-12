<?php

namespace Modules\UserManagement\Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Modules\Master\Models\District;
use Modules\Master\Models\Division;
use Modules\Master\Models\Section;
use Spatie\Permission\PermissionRegistrar;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $district = fn (string $code) => District::where('code', $code)->value('id');
        $division = fn (string $code) => Division::where('code', $code)->value('id');
        $section = fn (string $name) => Section::where('name', $name)->value('id');

        $fixedUsers = [
            [
                'role' => 'Super Admin',
                'name' => 'Super Admin',
                'username' => 'superadmin',
                'email' => 'superadmin@grms.com',
                'phone' => '+266 58000001',
                'staff' => true,
                'mfa' => true,
            ],
            [
                'role' => 'IT Admin',
                'name' => 'Admin',
                'username' => 'admin',
                'email' => 'admin@grms.com',
                'phone' => '+266 58000002',
                'staff' => true,
                'mfa' => true,
            ],
            [
                'role' => 'Developer',
                'name' => 'Developer',
                'username' => 'developer',
                'email' => 'developer@grms.com',
                'phone' => '+266 58000007',
                'staff' => true,
                'mfa' => false,
            ],
            [
                'role' => 'Director',
                'name' => 'Director',
                'username' => 'director',
                'email' => 'director@grms.com',
                'phone' => '+266 58000003',
                'staff' => true,
                'mfa' => true,
            ],
            [
                'role' => 'Division Director',
                'name' => 'Division Director',
                'username' => 'division.director',
                'email' => 'division.director@grms.com',
                'phone' => '+266 58000004',
                'staff' => true,
                'mfa' => false,
                'division' => 'NR',
                'section' => 'Berea Section',
                'district' => 'BE',
            ],
            [
                'role' => 'Section Manager',
                'name' => 'Section Manager',
                'username' => 'section.manager',
                'email' => 'section.manager@grms.com',
                'phone' => '+266 58000005',
                'staff' => true,
                'mfa' => false,
                'division' => 'NR',
                'section' => 'Berea Section',
                'district' => 'BE',
            ],
            [
                'role' => 'Helpdesk Officer',
                'name' => 'Helpdesk Officer',
                'username' => 'helpdesk.officer',
                'email' => 'helpdesk@grms.com',
                'phone' => '+266 58000006',
                'staff' => true,
                'mfa' => false,
                'division' => 'NR',
                'section' => 'Berea Section',
                'district' => 'BE',
            ],
            [
                'role' => 'RD Officer',
                'name' => 'RD Officer',
                'username' => 'rd.officer',
                'email' => 'rd.officer@grms.com',
                'phone' => '+266 58000008',
                'staff' => true,
                'mfa' => false,
            ],
            [
                'role' => 'Contractor CLO',
                'name' => 'Contractor CLO',
                'username' => 'contractor.clo',
                'email' => 'contractor.clo@grms.com',
                'phone' => '+266 58000009',
                'staff' => true,
                'mfa' => false,
                'division' => 'NR',
                'section' => 'Berea Section',
                'district' => 'BE',
            ],
            [
                'role' => 'GRC Member',
                'name' => 'GRC Member',
                'username' => 'grc.member',
                'email' => 'grc.member@grms.com',
                'phone' => '+266 58000010',
                'staff' => true,
                'mfa' => false,
            ],
            [
                'role' => 'Responsible Manager',
                'name' => 'Responsible Manager',
                'username' => 'responsible.manager',
                'email' => 'responsible.manager@grms.com',
                'phone' => '+266 58000011',
                'staff' => true,
                'mfa' => false,
                'division' => 'NR',
                'section' => 'Berea Section',
                'district' => 'BE',
            ],
            [
                'role' => 'Investigator',
                'name' => 'Investigator',
                'username' => 'investigator',
                'email' => 'investigator@grms.com',
                'phone' => '+266 58000012',
                'staff' => true,
                'mfa' => false,
                'division' => 'NR',
                'section' => 'Berea Section',
                'district' => 'BE',
            ],
            [
                'role' => 'Engineer',
                'name' => 'Engineer',
                'username' => 'engineer',
                'email' => 'engineer@grms.com',
                'phone' => '+266 58000013',
                'staff' => true,
                'mfa' => false,
                'division' => 'NR',
                'section' => 'Berea Section',
                'district' => 'BE',
            ],
            [
                'role' => 'GRC Chair',
                'name' => 'GRC Chair',
                'username' => 'grc.chair',
                'email' => 'grc.chair@grms.com',
                'phone' => '+266 58000014',
                'staff' => true,
                'mfa' => false,
            ],
            [
                'role' => 'RD Manager',
                'name' => 'RD Manager',
                'username' => 'rd.manager',
                'email' => 'rd.manager@grms.com',
                'phone' => '+266 58000015',
                'staff' => true,
                'mfa' => false,
            ],
            [
                'role' => 'Director General',
                'name' => 'Director General',
                'username' => 'director.general',
                'email' => 'director.general@grms.com',
                'phone' => '+266 58000016',
                'staff' => true,
                'mfa' => true,
            ],
            [
                'role' => 'RD E&S Unit',
                'name' => 'RD E&S Officer',
                'username' => 'rd.environment.social',
                'email' => 'rd.es.unit@grms.com',
                'phone' => '+266 58000017',
                'staff' => true,
                'mfa' => false,
            ],
            [
                'role' => 'External Reviewer',
                'name' => 'External Reviewer',
                'username' => 'external.reviewer',
                'email' => 'external.reviewer@grms.com',
                'phone' => '+266 58000018',
                'staff' => true,
                'mfa' => false,
            ],
            [
                'role' => 'Executive',
                'name' => 'Executive',
                'username' => 'executive',
                'email' => 'executive@grms.com',
                'phone' => '+266 58000019',
                'staff' => true,
                'mfa' => false,
            ],
        ];

        foreach ($fixedUsers as $fixed) {
            $user = User::updateOrCreate(
                ['email' => $fixed['email']],
                [
                    'name' => $fixed['name'],
                    'username' => $fixed['username'],
                    'phone' => $fixed['phone'] ?? $this->lesothoPhone(),
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
                    'district_id' => isset($fixed['district']) ? $district($fixed['district']) : null,
                    'division_id' => isset($fixed['division']) ? $division($fixed['division']) : null,
                    'section_id' => isset($fixed['section']) ? $section($fixed['section']) : null,
                    'mfa_enabled' => $fixed['mfa'],
                    'mfa_secret' => null,
                    'status' => true,
                    'email_verified_at' => now(),
                ]
            );

            $user->syncRoles([$fixed['role']]);
        }

        // 20 users, distributed across the original platform roles
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
            ['division' => 'NR', 'section' => 'Berea Section', 'district' => 'BE'],
            ['division' => 'NR', 'section' => 'Leribe Section', 'district' => 'LE'],
            ['division' => 'CR', 'section' => 'Maseru Section', 'district' => 'MS'],
            ['division' => 'SR', 'section' => 'Mafeteng Section', 'district' => 'MF'],
            ['division' => 'SR', 'section' => "Mohale's Hoek Section", 'district' => 'MH'],
            ['division' => 'SR', 'section' => 'Thaba-Tseka Section', 'district' => 'TT'],
        ];
        $citizenDistricts = ['BE', 'BB', 'LE', 'MF', 'MS', 'MH', 'QN', 'QT', 'MK', 'TT'];

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

        $this->command->info("✅ Created/verified {$created} of {$totalPlanned} planned sample users across 8 platform roles.");
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
