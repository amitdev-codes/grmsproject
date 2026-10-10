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
