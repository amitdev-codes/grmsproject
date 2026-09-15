<?php

namespace Modules\Grievance\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Modules\Grievance\Models\GrievanceCategory;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        collect([
            [
                'code' => 'LAND',
                'name_en' => 'Land Acquisition and Resettlement',
                'name_st' => 'Ho nka naha le Phalliso',
                'icon' => 'map',          // optional
                'is_sensitive' => true,
            ],
            [
                'code' => 'ENCR',
                'name_en' => 'Encroachments',
                'name_st' => 'Ho kena-kenana / Ho tlola meeli',
                'icon' => 'alert-triangle',
                'is_sensitive' => false,
            ],
            [
                'code' => 'COMP',
                'name_en' => 'Compensation',
                'name_st' => 'Matšeliso',
                'icon' => 'currency-dollar',
                'is_sensitive' => true,
            ],
            [
                'code' => 'ENV',
                'name_en' => 'Environmental Concerns',
                'name_st' => 'Mathata a Tikoloho',
                'icon' => 'leaf',
                'is_sensitive' => false,
            ],
            [
                'code' => 'SAFE',
                'name_en' => 'Road Safety Concerns',
                'name_st' => 'Mathata a Polokeho tseleng',
                'icon' => 'shield',
                'is_sensitive' => false,
            ],
            [
                'code' => 'DAMAGE',
                'name_en' => 'Damage to Properties',
                'name_st' => 'Tšenyo ea Thepa',
                'icon' => 'home',
                'is_sensitive' => false,
            ],
            [
                'code' => 'OHS',
                'name_en' => 'Occupational Health and Safety',
                'name_st' => 'Bophelo le Polokeho Mosebetsing',
                'icon' => 'hard-hat',
                'is_sensitive' => false,
            ],
            [
                'code' => 'LABOUR',
                'name_en' => 'Labour Recruitment Issues',
                'name_st' => 'Mathata a ho hira basebetsi',
                'icon' => 'users',
                'is_sensitive' => false,
            ],
            [
                'code' => 'OTHER',
                'name_en' => 'Other',
                'name_st' => 'Tse ling',
                'icon' => 'more-horizontal',
                'is_sensitive' => false,
            ],
            [
                'code' => 'POTHOLE',
                'name_en' => 'Potholes and Road Surface Damage',
                'name_st' => 'Likoti le Tšenyo ea Bokaholimo ba Tsela',
                'icon' => 'construction',
                'is_sensitive' => false,
            ],
            [
                'code' => 'SIGNAGE',
                'name_en' => 'Damaged or Missing Road Signage',
                'name_st' => 'Matšoao a Tsela a Senyehileng kapa a Sieo',
                'icon' => 'signpost',
                'is_sensitive' => false,
            ],
            [
                'code' => 'EROSION',
                'name_en' => 'Erosion and Drainage',
                'name_st' => 'Khoholeho ea Mobu le Metsi a Phallang',
                'icon' => 'waves',
                'is_sensitive' => false,
            ],
            [
                'code' => 'CONSDELAY',
                'name_en' => 'Construction Delay',
                'name_st' => 'Tieho ea Kaho',
                'icon' => 'clock',
                'is_sensitive' => false,
            ],
            [
                'code' => 'ILLEGALUSE',
                'name_en' => 'Illegal Road Use',
                'name_st' => 'Tšebeliso e Seng Molaong ea Tsela',
                'icon' => 'ban',
                'is_sensitive' => false,
            ],
            [
                'code' => 'CONTRACTOR',
                'name_en' => 'Contractor or Service Provider Complaint',
                'name_st' => 'Tletlebo ka Rakonteraka kapa Mofani oa Tšebeletso',
                'icon' => 'briefcase',
                'is_sensitive' => false,
            ],
            [
                'code' => 'CORRUPTION',
                'name_en' => 'Corruption or Malpractice Report',
                'name_st' => 'Tlaleho ea Bobolu kapa Boitšoaro bo sa Lokang',
                'icon' => 'shield-alert',
                'is_sensitive' => true,
            ],
            [
                'code' => 'FEEDBACK',
                'name_en' => 'Feedback, Suggestion or Recommendation',
                'name_st' => 'Maikutlo, Tlhahiso kapa Keletso',
                'icon' => 'message-circle',
                'is_sensitive' => false,
            ],
            [
                'code' => 'COMPLIMENT',
                'name_en' => 'Compliment or Success Story',
                'name_st' => 'Thoriso kapa Pale ea Katleho',
                'icon' => 'heart',
                'is_sensitive' => false,
            ],
            [
                'code' => 'SOCRESP',
                'name_en' => 'Roads Directorate Social Responsibility',
                'name_st' => 'Boikarabelo ba Sechaba ba Roads Directorate',
                'icon' => 'hand-heart',
                'is_sensitive' => false,
            ],
        ])->values()->each(function ($c, $i) {
            GrievanceCategory::updateOrCreate(
                ['code' => $c['code']],
                [
                    'name_en' => $c['name_en'],
                    'name_st' => $c['name_st'],
                    'slug' => Str::slug($c['code']),   // or Str::slug($c['name_en'])
                    'icon' => $c['icon'] ?? null,
                    'is_sensitive' => $c['is_sensitive'] ?? false,
                    'sort_order' => $i,
                    'is_active' => true,
                ]
            );
        });
    }
}
