<?php

namespace App\Http\Middleware;

use App\Http\Controllers\DashboardController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Inertia\Middleware;
use Modules\Grievance\Models\Grievance;
use Modules\Grievance\Notifications\GrievanceAllocated;
use Modules\Grievance\Notifications\GrievanceAssigned;
use Modules\Grievance\Notifications\GrievanceReAllocationRequested;
use Modules\Setting\Models\ApplicationSetting;

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

        return [
            ...parent::share($request),
            'locale' => $locale,
            'locales' => config('locales.supported'),
            'translations' => $this->loadTranslations($locale),
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
                        'avatar' => $user->avatar,
                        'locale' => $user->locale,
                        'role_names' => $user->getRoleNames()->implode(', '),
                        'division_id' => $user->division_id,
                        'section_id' => $user->section_id,
                        'permissions' => $user->getAllPermissions()->pluck('name')->toArray(),
                    ]
                    : null,
            ],
            'notifications' => [
                'count' => $user
                    ? $user->unreadNotifications()->whereIn('type', [
                        GrievanceAllocated::class,
                        GrievanceAssigned::class,
                        GrievanceReAllocationRequested::class,
                    ])->count()
                    : 0,
            ],
            'pendingGrievances' => $this->pendingGrievances($user),
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'import_failures' => fn () => $request->session()->get('import_failures'),
            ],
        ];
    }

    protected function applicationSettings(): array
    {
        $settings = ApplicationSetting::current();

        return [
            'project_name' => $settings->project_name,
            'short_name' => $settings->short_name,
            'tagline' => $settings->tagline,
            'description' => $settings->description,
            'theme' => $settings->theme ?: 'default',
            'logo_url' => $settings->logo_path ? asset('storage/'.$settings->logo_path) : null,
            'favicon_url' => $settings->favicon_path ? asset('storage/'.$settings->favicon_path) : null,
            'email' => $settings->email,
            'phone' => $settings->phone,
            'address_line' => $settings->address_line,
            'support_hours' => $settings->support_hours,
            'footer_text' => $settings->footer_text,
        ];
    }

    /**
     * Return the queue the signed-in user can act on next.
     *
     * @return array{count: int, href: string, label: string, visible: bool}
     */
    protected function pendingGrievances(?object $user): array
    {
        if (! $user) {
            return ['count' => 0, 'href' => '/grievances?pending=1', 'label' => 'Pending Grievances', 'visible' => false];
        }

        $query = Grievance::query();
        $href = '/grievances?pending=1';

        if ($user->hasRole('Director')) {
            $query->whereIn('status', ['submitted', 'reallocation_required'])->whereNull('division_id');
            $href = '/grievances?pending=1';
        } elseif ($user->hasRole('Division Director') && $user->division_id) {
            $query->where('status', 'allocated_division')->where('division_id', $user->division_id);
            $href = '/grievances/division-queue';
        } elseif ($user->hasRole('Section Manager') && $user->section_id) {
            $query->where('status', 'allocated_section')->where('section_id', $user->section_id);
        } elseif ($user->hasAnyRole(['Helpdesk Officer', 'Content Editor'])) {
            $query->whereIn('status', ['assigned_officer', 'assigned', 'in_progress', 'escalated'])
                ->where('assigned_officer_id', $user->id);
        } else {
            return ['count' => 0, 'href' => $href, 'label' => 'Pending Grievances', 'visible' => false];
        }

        return ['count' => $query->count(), 'href' => $href, 'label' => 'Pending Grievances', 'visible' => true];
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
