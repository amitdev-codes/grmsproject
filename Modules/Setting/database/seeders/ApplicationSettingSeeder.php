<?php

namespace Modules\Setting\Database\Seeders;

use Illuminate\Database\Seeder;

use Modules\Setting\Models\ApplicationSetting;

class ApplicationSettingSeeder extends Seeder
{
    public function run(): void
    {
        ApplicationSetting::query()->updateOrCreate(
            ['project_slug' => 'grms-lesotho'],
            [
                // Identity
                'project_name' => 'Grievance Redress Management System',
                'short_name' => 'GRMS',
                'tagline' => 'Register, track, and resolve grievances transparently.',
                'description' => 'A national platform for citizens to register grievances against government services and track their resolution through to closure.',

                // Branding
                'logo_path' => null,
                'favicon_path' => null,
                'primary_color' => '#1D4ED8',
                'secondary_color' => '#64748B',

                // Location
                'address_line' => 'Ministry Complex, Maseru',
                'district_id' => null,
                'latitude' => -29.3167,
                'longitude' => 27.4833,

                // Contact
                'email' => 'support@grms.gov.ls',
                'phone' => '+266 2231 0000',
                'whatsapp' => '+266 5000 0000',
                'social_links' => [
                    'facebook' => 'https://facebook.com/grmslesotho',
                    'linkedin' => 'https://linkedin.com/company/grmslesotho',
                    'youtube' => null,
                    'twitter' => null,
                    'instagram' => null,
                ],

                // SEO
                'seo_title' => 'GRMS — Grievance Redress Management System',
                'seo_description' => 'Register and track grievances against government services online.',
                'seo_keywords' => 'grievance, complaint, redress, lesotho, government',
                'og_image_path' => null,
                'seo_meta' => [
                    'robots' => 'index, follow',
                    'twitter_card' => 'summary_large_image',
                    'canonical_url' => null,
                ],

                // GRMS config
                'sla_level1_hours' => 48,
                'sla_level2_days' => 5,
                'sla_level3_days' => 7,
                'default_locale' => 'en',
                'support_hours' => 'Mon–Fri, 8:00am–4:30pm',
                'anonymous_submissions_enabled' => true,
                'maintenance_mode' => false,

                // Legal / footer
                'footer_text' => '© ' . date('Y') . ' Government of Lesotho. All rights reserved.',
                'privacy_policy_url' => null,
                'terms_url' => null,

                // Escape hatch
                'extra' => [],
            ],
        );
    }
}
