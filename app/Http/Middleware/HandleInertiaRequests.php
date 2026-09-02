<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $locale = App::getLocale();
        return [
            ...parent::share($request),
            'locale' => $locale,
            'locales' => config('locales.supported'),
            'translations' => $this->loadTranslations($locale),
            'name' => config('app.name'),
            'auth' => [
                'user' => $request->user()
                    ? [
                        'id' => $request->user()->id,
                        'name' => $request->user()->name,
                        'email' => $request->user()->email,
                        'avatar' => $request->user()->avatar,
                        'locale' => $request->user()->locale,
                        'role_names' => $request->user()->getRoleNames()->implode(', '),
                        // or 'roles' => $request->user()->getRoleNames()->values(),
                    ]
                    : null,
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'import_failures' => fn () => $request->session()->get('import_failures'),
            ],
        ];
    }
    /**
     * @return array<string,string>
     */
    protected function loadTranslations(string $locale): array
    {
        $cacheKey = "translations.{$locale}";

        if (app()->environment('production')) {
            return Cache::rememberForever($cacheKey, fn () => $this->buildTranslations($locale));
        }

        return $this->buildTranslations($locale);
    }

    protected function buildTranslations(string $locale): array
    {
        return array_merge(
            $this->loadJsonTranslations($locale),
            $this->loadPhpTranslations($locale),
            $this->loadModuleTranslations($locale),
        );
    }

    protected function loadJsonTranslations(string $locale): array
    {
        $path = base_path("lang/{$locale}.json");

        return file_exists($path)
            ? (json_decode(file_get_contents($path), true) ?? [])
            : [];
    }

    protected function loadPhpTranslations(string $locale): array
    {
        $translations = [];
        $path = lang_path($locale);

        if (! is_dir($path)) {
            return [];
        }

        foreach (glob("{$path}/*.php") as $file) {
            $translations[basename($file, '.php')] = require $file;
        }

        return $translations;
    }

    protected function loadModuleTranslations(string $locale): array
    {
        $translations = [];
        $modulesPath = base_path('Modules');

        if (! is_dir($modulesPath)) {
            return [];
        }

        foreach (glob("{$modulesPath}/*", GLOB_ONLYDIR) as $moduleDir) {
            $moduleKey = strtolower(basename($moduleDir));

            // nwidart v10+: Modules/{Name}/lang/{locale}
            // older nwidart: Modules/{Name}/Resources/lang/{locale}
            $langDir = is_dir("{$moduleDir}/lang/{$locale}")
                ? "{$moduleDir}/lang/{$locale}"
                : "{$moduleDir}/Resources/lang/{$locale}";

            if (! is_dir($langDir)) {
                continue;
            }

            foreach (glob("{$langDir}/*.php") as $file) {
                $translations[$moduleKey][basename($file, '.php')] = require $file;
            }
        }

        return $translations;
    }
}
