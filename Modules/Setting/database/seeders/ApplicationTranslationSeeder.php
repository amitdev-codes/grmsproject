<?php

namespace Modules\Setting\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ApplicationTranslationSeeder extends Seeder
{
    public function run(): void
    {
        $english = $this->loadLocaleDictionary('en');
        $sesotho = $this->loadLocaleDictionary('st');
        $rows = [];

        foreach ($this->flatten($english) as $key => $text) {
            if (! is_string($text) || trim($text) === '' || strlen($key) > 191) {
                continue;
            }

            $sesothoText = data_get($sesotho, $key);
            $rows[] = [
                'translation_key' => $key,
                'english_text' => $text,
                'sesotho_text' => is_string($sesothoText) ? $sesothoText : null,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            if (count($rows) >= 500) {
                DB::table('application_translations')->insertOrIgnore($rows);
                $rows = [];
            }
        }

        if ($rows !== []) {
            DB::table('application_translations')->insertOrIgnore($rows);
        }

        DB::table('application_translations')->insertOrIgnore([
            [
                'translation_key' => 'menu.language-translations',
                'english_text' => 'Language translations',
                'sesotho_text' => 'Phetolelo ea lipuo',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $uiTranslations = [
            'menu.home' => ['Home', 'Lehae'],
            'menu.back' => ['Back', 'Morao'],
            'menu.actions' => ['Actions', 'Liketso'],
            'menu.select_all' => ['Select all', 'Khetha tsohle'],
            'menu.select_row' => ['Select row', 'Khetha mola'],
            'menu.ascending' => ['Ascending', 'Nyolohang'],
            'menu.descending' => ['Descending', 'Theohang'],
            'menu.hide_column' => ['Hide column', 'Pata kholomo'],
            'menu.reset' => ['Reset', 'Seta bocha'],
            'menu.active' => ['Active', 'E sebetsa'],
            'menu.inactive' => ['Inactive', 'Ha e sebetse'],
            'menu.new_user' => ['New User', 'Mosebelisi e mocha'],
            'menu.new_role' => ['New Role', 'Karolo e ncha'],
            'menu.new_permission' => ['New Permission', 'Tumello e ncha'],
            'menu.list_resource' => ['List :resource', 'Lenane la :resource'],
            'menu.export' => ['Export', 'Romella kantle'],
            'menu.import' => ['Import', 'Kenya'],
            'menu.view_all' => ['View all', 'Sheba tsohle'],
            'menu.rows_per_page' => ['Rows per page', 'Mela ka leqephe'],
            'menu.showing' => ['Showing', 'E bontša'],
            'menu.of' => ['of', 'ho'],
            'menu.show' => ['Show', 'Bontša'],
            'menu.entries' => ['entries', 'likeno'],
            'menu.toggle_columns' => ['Toggle columns', 'Fetola likholomo'],
            'menu.excel' => ['Excel (.xlsx)', 'Excel (.xlsx)'],
            'menu.csv' => ['CSV', 'CSV'],
            'menu.pdf' => ['PDF', 'PDF'],
            'menu.print' => ['Print', 'Hatisa'],
            'menu.import_records' => ['Import records', 'Kenya lirekoto'],
            'menu.import_file_description' => [
                'Upload an .xlsx or .csv file matching the expected columns.',
                'Kenya faele ea .xlsx kapa .csv e lumellanang le likholomo tse hlokahalang.',
            ],
            'menu.importing' => ['Importing…', 'E kenya…'],
            'menu.username' => ['Username', 'Lebitso la mosebelisi'],
            'menu.email' => ['Email', 'Imeile'],
            'menu.phone' => ['Phone', 'Mohala'],
            'menu.role' => ['Role', 'Karolo'],
            'menu.status' => ['Status', 'Boemo'],
            'menu.created' => ['Created', 'E entsoe'],
            'menu.code' => ['Code', 'Khoutu'],
            'menu.name' => ['Name', 'Lebitso'],
            'menu.name_st' => ['Name (ST)', 'Lebitso (Sesotho)'],
        ];

        foreach ($uiTranslations as $key => [$englishText, $sesothoText]) {
            if ($key === 'menu.view_all') {
                DB::table('application_translations')
                    ->where('translation_key', $key)
                    ->where('english_text', 'View')
                    ->update(['english_text' => $englishText, 'updated_at' => now()]);
            }

            DB::table('application_translations')
                ->where('translation_key', $key)
                ->where(fn ($query) => $query->whereNull('sesotho_text')->orWhere('sesotho_text', ''))
                ->update(['sesotho_text' => $sesothoText, 'updated_at' => now()]);

            DB::table('application_translations')->insertOrIgnore([
                'translation_key' => $key,
                'english_text' => $englishText,
                'sesotho_text' => $sesothoText,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        Cache::forget('application-translations.all');
    }

    private function loadLocaleDictionary(string $locale): array
    {
        $dictionary = [];
        $jsonPath = base_path("lang/{$locale}.json");

        if (File::exists($jsonPath)) {
            $dictionary = json_decode(File::get($jsonPath), true) ?: [];
        }

        $languagePath = lang_path($locale);
        if (File::isDirectory($languagePath)) {
            foreach (File::files($languagePath) as $file) {
                if ($file->getExtension() === 'php') {
                    $dictionary[$file->getFilenameWithoutExtension()] = require $file->getPathname();
                }
            }
        }

        foreach (File::directories(base_path('Modules')) as $modulePath) {
            $moduleName = strtolower(basename($modulePath));
            $moduleLanguagePath = is_dir("{$modulePath}/lang/{$locale}")
                ? "{$modulePath}/lang/{$locale}"
                : "{$modulePath}/Resources/lang/{$locale}";

            if (! File::isDirectory($moduleLanguagePath)) {
                continue;
            }

            foreach (File::files($moduleLanguagePath) as $file) {
                if ($file->getExtension() === 'php') {
                    $dictionary[$moduleName][$file->getFilenameWithoutExtension()] = require $file->getPathname();
                }
            }
        }

        return $dictionary;
    }

    /**
     * @return array<string, mixed>
     */
    private function flatten(array $values, string $prefix = ''): array
    {
        $result = [];

        foreach ($values as $key => $value) {
            $translationKey = $prefix === '' ? (string) $key : "{$prefix}.{$key}";

            if (is_array($value)) {
                $result += $this->flatten($value, $translationKey);
            } else {
                $result[$translationKey] = $value;
            }
        }

        return $result;
    }
}
