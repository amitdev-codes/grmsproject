<?php

namespace Modules\Setting\Services;

use Illuminate\Database\Eloquent\Model;
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
    public function destroy(ApplicationSetting $applicationSetting): bool
    {
        return $this->repository->delete($applicationSetting);
    }
}
