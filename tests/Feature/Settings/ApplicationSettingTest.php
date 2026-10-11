<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Modules\Setting\Database\Seeders\ApplicationSettingSeeder;
use Modules\Setting\Models\ApplicationSetting;

uses(RefreshDatabase::class);

it('seeds the singleton application setting with the configured application identity', function () {
    ApplicationSetting::query()->create([
        'project_name' => 'GRMS',
        'project_slug' => 'grms',
        'logo_path' => 'application-settings/logo.png',
        'favicon_path' => 'application-settings/favicon.png',
    ]);

    app(ApplicationSettingSeeder::class)->run();

    $settings = ApplicationSetting::query()->sole();
    $projectName = (string) config('app.display_name', 'Grievance Redressal Management System');

    expect($settings->project_name)->toBe($projectName)
        ->and($settings->project_slug)->toBe('grms-lesotho')
        ->and($settings->logo_path)->toBe('application-settings/logo.png')
        ->and($settings->favicon_path)->toBe('application-settings/favicon.png');
});

it('provides public URLs for saved branding files', function () {
    Storage::fake('public');
    $path = 'application-settings/branding.png';
    Storage::disk('public')->put($path, 'image');

    $settings = new ApplicationSetting([
        'project_name' => 'GRMS',
        'project_slug' => 'grms',
        'logo_path' => $path,
        'favicon_path' => $path,
        'og_image_path' => $path,
    ]);

    $url = Storage::disk('public')->url($path);

    expect($settings->logo_url)->toBe($url)
        ->and($settings->favicon_url)->toBe($url)
        ->and($settings->og_image_url)->toBe($url);
});
