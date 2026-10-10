<?php

use App\Http\Middleware\HandleInertiaRequests;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Setting\Models\ApplicationTranslation;
use Modules\Setting\Services\ApplicationTranslationService;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withoutMiddleware(HandleInertiaRequests::class);
    $role = Role::firstOrCreate(
        ['name' => 'Super Admin', 'guard_name' => 'web'],
        ['code' => 'SUPER_ADMIN', 'status' => true],
    );
    $this->actingAs(User::factory()->create()->assignRole($role));
});

it('allows admins to manage the English and Sesotho translation grid', function () {
    $this->post(route('settings.translations.store'), [
        'translation_key' => 'menu.sample',
        'english_text' => 'Sample menu',
        'sesotho_text' => 'Mohlala',
    ])->assertRedirect(route('settings.translations.index'));

    $translation = ApplicationTranslation::query()->where('translation_key', 'menu.sample')->firstOrFail();
    expect(app(ApplicationTranslationService::class)->all()['menu.sample'])->toBe([
        'en' => 'Sample menu',
        'st' => 'Mohlala',
    ]);

    $this->put(route('settings.translations.update', $translation), [
        'translation_key' => 'menu.sample',
        'english_text' => 'Updated sample',
        'sesotho_text' => 'E ntlafalitsoe',
    ])->assertRedirect(route('settings.translations.index'));

    expect(app(ApplicationTranslationService::class)->all()['menu.sample']['st'])->toBe('E ntlafalitsoe');
});

it('filters the translation list by untranslated strings', function () {
    ApplicationTranslation::query()->create([
        'translation_key' => 'menu.untranslated',
        'english_text' => 'Needs translation',
        'sesotho_text' => null,
    ]);
    ApplicationTranslation::query()->create([
        'translation_key' => 'menu.translated',
        'english_text' => 'Already translated',
        'sesotho_text' => 'E fetoletsoe',
    ]);

    $this->get(route('settings.translations.index', ['filters' => ['sesotho_status' => ['missing']]]), ['X-Inertia' => 'true'])
        ->assertOk()
        ->assertJsonPath('props.meta.total', 1)
        ->assertJsonPath('props.data.0.translation_key', 'menu.untranslated')
        ->assertJsonMissing(['translation_key' => 'menu.translated']);

    $this->get(route('settings.translations.index', ['filters' => ['sesotho_status' => ['translated']]]), ['X-Inertia' => 'true'])
        ->assertOk()
        ->assertJsonPath('props.meta.total', 1)
        ->assertJsonPath('props.data.0.translation_key', 'menu.translated');
});
