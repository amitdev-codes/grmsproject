<?php

namespace App\Http\Middleware;

use App\Http\Controllers\DashboardController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Inertia\Middleware;
use Modules\Grievance\Services\PendingGrievanceQueueService;
use Modules\Setting\Services\ApplicationSettingService;
use Modules\Setting\Services\ApplicationTranslationService;

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
        $user = $request->user();
        $notificationItems = collect();

        if ($user) {
            $notificationItems = $user->unreadNotifications()->latest()->limit(10)->get();

            if ($notificationItems->count() < 10) {
                $notificationItems = $notificationItems->concat(
                    $user->readNotifications()->latest()->limit(10 - $notificationItems->count())->get(),
                );
            }
        }

        return [
            ...parent::share($request),
            'locale' => $locale,
            'locales' => config('locales.supported'),
            'translations' => $this->loadTranslations($locale),
            'dynamicTranslations' => $user
                ? app(ApplicationTranslationService::class)->all()
                : [],
            'name' => config('app.name'),
            'app_author' => config('app.author', 'Roads Directorate · Government of Lesotho'),
            'applicationSettings' => $this->applicationSettings(),
            'dashboard' => app(DashboardController::class)->data(),
            'auth' => [
                'user' => $user
                    ? [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'avatar_url' => $user->avatar_url,
                        'locale' => $user->locale,
                        'role_names' => $user->getRoleNames()->implode(', '),
                        'division_id' => $user->division_id,
                        'section_id' => $user->section_id,
                        'permissions' => $user->getAllPermissions()->pluck('name')->toArray(),
                    ]
                    : null,
            ],
            'notifications' => [
                'count' => $user ? $user->unreadNotifications()->count() : 0,
                'latest' => $notificationItems->map(fn ($notification) => [
                    'id' => $notification->id,
                    'title' => $notification->data['title'] ?? 'Notification',
                    'message' => $notification->data['message'] ?? '',
                    'action_url' => $notification->data['action_url'] ?? null,
                    'open_url' => route('notification.open', $notification->id),
                    'read_at' => $notification->read_at?->toIso8601String(),
                    'created_at' => $notification->created_at?->diffForHumans(),
                ])->values()->all(),
            ],
            'pendingGrievances' => $this->pendingGrievances($user),
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'import_failures' => fn () => $request->session()->get('import_failures'),
                'ai_summary' => fn () => $request->session()->get('ai_summary'),
                'ai_error' => fn () => $request->session()->get('ai_error'),
            ],
        ];
    }

    protected function applicationSettings(): array
    {
        return app(ApplicationSettingService::class)->publicData();
    }

    /**
     * Return the queue the signed-in user can act on next.
     *
     * @return array{count: int, href: string, label: string, visible: bool}
     */
    protected function pendingGrievances(?User $user): array
    {
        if (! $user) {
            return ['count' => 0, 'href' => '/grievances?pending=1', 'label' => 'Pending Grievances', 'visible' => false];
        }

        $queue = app(PendingGrievanceQueueService::class);
        $query = $queue->queryFor($user);

        return [
            'count' => $query->count(),
            'href' => '/grievances?pending=1',
            'label' => 'Pending Grievances',
            'visible' => $queue->visibleFor($user),
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
