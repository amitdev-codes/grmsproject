<?php

namespace Modules\Setting\Services;

use Illuminate\Support\Facades\Cache;
use Modules\Setting\Models\ApplicationTranslation;

class ApplicationTranslationService
{
    /**
     * @return array<string, array{en: string, st: string}>
     */
    public function all(): array
    {
        return Cache::rememberForever('application-translations.all', fn () => ApplicationTranslation::query()
            ->get(['translation_key', 'english_text', 'sesotho_text'])
            ->mapWithKeys(fn (ApplicationTranslation $translation) => [
                $translation->translation_key => [
                    'en' => $translation->english_text,
                    'st' => $translation->sesotho_text ?? '',
                ],
            ])
            ->all());
    }

    public function clearCache(): void
    {
        Cache::forget('application-translations.all');
    }
}
