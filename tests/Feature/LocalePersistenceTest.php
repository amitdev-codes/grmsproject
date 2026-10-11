<?php

use App\Http\Controllers\DashboardController;
use App\Http\Middleware\HandleInertiaRequests;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Setting\Models\ApplicationTranslation;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

function localeTestInertiaHeaders(): array
{
    return [
        'X-Inertia' => 'true',
        'X-Inertia-Version' => app(HandleInertiaRequests::class)->version(request()) ?? '',
    ];
}

beforeEach(function () {
    $dashboard = Mockery::mock(DashboardController::class)->makePartial();
    $dashboard->shouldReceive('data')->andReturn([]);
    app()->instance(DashboardController::class, $dashboard);

    $role = Role::firstOrCreate(
        ['name' => 'Super Admin', 'guard_name' => 'web'],
        ['code' => 'SUPER_ADMIN', 'status' => true],
    );
    $this->actingAs(User::factory()->create()->assignRole($role));
});

it('keeps the selected Sesotho locale on subsequent Inertia page visits', function () {
    $this->from(route('settings.translations.index'))
        ->get(route('locale.update', ['locale' => 'st']))
        ->assertRedirect(route('settings.translations.index'))
        ->assertCookie('locale', 'st');

    expect(auth()->user()->fresh()->locale)->toBe('st');

    $this->get(route('settings.translations.index'), localeTestInertiaHeaders())
        ->assertOk()
        ->assertJsonPath('props.locale', 'st');

    $this->get(route('users.index'), localeTestInertiaHeaders())
        ->assertOk()
        ->assertJsonPath('props.locale', 'st');
});

it('shares edited database translations with the users page in the selected locale', function () {
    $translation = ApplicationTranslation::query()->create([
        'translation_key' => 'menu.users',
        'english_text' => 'Users',
        'sesotho_text' => 'Basebedisi ba ka',
    ]);

    $this->get(route('locale.update', ['locale' => 'st']))
        ->assertRedirect();

    $initialPage = $this->get(route('users.index'), localeTestInertiaHeaders())
        ->assertOk();
    expect($initialPage->json('props.dynamicTranslations')['menu.users']['st'])
        ->toBe('Basebedisi ba ka');

    $this->put(route('settings.translations.update', $translation), [
        'translation_key' => $translation->translation_key,
        'english_text' => $translation->english_text,
        'sesotho_text' => 'Basebedisi ba hlophisitsoeng',
    ])->assertRedirect(route('settings.translations.index'));

    $updatedPage = $this->get(route('users.index'), localeTestInertiaHeaders())
        ->assertOk()
        ->assertJsonPath('props.locale', 'st');
    expect($updatedPage->json('props.dynamicTranslations')['menu.users']['st'])
        ->toBe('Basebedisi ba hlophisitsoeng');
});
