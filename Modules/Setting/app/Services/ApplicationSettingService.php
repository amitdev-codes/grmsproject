<?php

namespace Modules\Setting\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Modules\Grievance\Models\GrievanceEscalation;
use Modules\Setting\Models\ApplicationSetting;
use Modules\Setting\Repository\ApplicationSettingRepository;

class ApplicationSettingService
{
    public function __construct(protected ApplicationSettingRepository $repository) {}

    public function forCreate(): array
    {
        return [
            'applicationSetting' => null,
        ];
    }
    public function current(): Model
    {
        return $this->repository->current();
    }
    public function store(array $data): ApplicationSetting
    {
        return $this->repository->create($data);
    }

    public function update(array $data, array $files = []): Model
    {
        $settings = $this->repository->current();

        foreach (['logo', 'favicon', 'og_image'] as $key) {
            if (! empty($files[$key]) && $files[$key] instanceof UploadedFile) {
                $data["{$key}_path"] = $this->storeAsset($files[$key], $settings, $key);
            }
        }

        return $this->repository->update($settings, $data);
    }

    protected function storeAsset(UploadedFile $file, ApplicationSetting $settings, string $key): string
    {
        $column = "{$key}_path";

        // Replace, don't accumulate — a settings asset has exactly one
        // current file, unlike grievance evidence which is a growing list.
        if ($settings->{$column}) {
            Storage::disk('public')->delete($settings->{$column});
        }

        return $file->store('application-settings', 'public');
    }
    public function publicData(): array
    {
        $settings = $this->current();
        $siteTotal = (int) DB::table('page_view_counters')
            ->where('key', 'site_total')
            ->value('count') ?: 0;

        return [
            'project_name' => $settings->project_name,
            'project_slug' => $settings->project_slug,
            'short_name' => $settings->short_name,
            'tagline' => $settings->tagline,
            'description' => $settings->description,
            'theme' => $settings->theme ?: 'default',
            'logo_url' => $settings->logo_path ? asset('storage/'.$settings->logo_path) : null,
            'logo_path' => $settings->logo_path,
            'favicon_url' => $settings->favicon_path ? asset('storage/'.$settings->favicon_path) : null,
            'favicon_path' => $settings->favicon_path,
            'email' => $settings->email,
            'phone' => $settings->phone,
            'whatsapp' => $settings->whatsapp,
            'address_line' => $settings->address_line,
            'latitude' => $settings->latitude,
            'longitude' => $settings->longitude,
            'support_hours' => $settings->support_hours,
            'footer_text' => $settings->footer_text,
            'social_links' => $settings->social_links ?? [],
            'seo_title' => $settings->seo_title,
            'seo_description' => $settings->seo_description,
            'seo_keywords' => $settings->seo_keywords,
            'seo_meta' => $settings->seo_meta ?? [],
            'default_locale' => $settings->default_locale,
            'privacy_policy_url' => $settings->privacy_policy_url,
            'terms_url' => $settings->terms_url,
            'site_total' => $siteTotal,
        ];
    }

    public function destroy(ApplicationSetting $applicationSetting): bool
    {
        return $this->repository->delete($applicationSetting);
    }
}
