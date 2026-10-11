<?php

use App\Http\Controllers\DashboardController;
use App\Http\Middleware\HandleInertiaRequests;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Modules\Setting\Services\ApplicationSettingService;
use Modules\Setting\Services\ApplicationTranslationService;

uses(RefreshDatabase::class);

it('filters notifications by read state and allows the owner to update that state', function () {
    $this->withoutMiddleware(HandleInertiaRequests::class);
    $user = User::factory()->create();
    $unread = $user->notifications()->create([
        'id' => (string) Str::uuid(),
        'type' => 'test.notification',
        'data' => ['title' => 'Unread notification'],
    ]);
    $read = $user->notifications()->create([
        'id' => (string) Str::uuid(),
        'type' => 'test.notification',
        'data' => ['title' => 'Read notification'],
        'read_at' => now(),
    ]);

    $this->actingAs($user)
        ->get('/notifications?status=unread')
        ->assertOk()
        ->assertSee('Unread notification')
        ->assertDontSee('Read notification');

    $this->post(route('notification.read', $unread->id))->assertRedirect();
    expect($unread->fresh()->read_at)->not->toBeNull();

    $this->post(route('notification.unread', $read->id))->assertRedirect();
    expect($read->fresh()->read_at)->toBeNull();
});

it('does not let a user change another users notification state', function () {
    $this->withoutMiddleware(HandleInertiaRequests::class);
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $notification = $owner->notifications()->create([
        'id' => (string) Str::uuid(),
        'type' => 'test.notification',
        'data' => ['title' => 'Private notification'],
    ]);

    $this->actingAs($otherUser)
        ->post(route('notification.read', $notification->id))
        ->assertNotFound();

    expect($notification->fresh()->read_at)->toBeNull();
});

it('shows unread notifications before newer read notifications in the bell menu', function () {
    $user = User::factory()->create();
    $unreadAssignment = $user->notifications()->create([
        'id' => (string) Str::uuid(),
        'type' => 'grievance.assigned',
        'data' => ['title' => 'Investigation assigned'],
        'created_at' => now()->subDays(2),
    ]);

    foreach (range(1, 7) as $index) {
        $user->notifications()->create([
            'id' => (string) Str::uuid(),
            'type' => 'test.notification',
            'data' => ['title' => "Read notification {$index}"],
            'read_at' => now()->subMinutes($index),
            'created_at' => now()->subMinutes($index),
        ]);
    }

    $this->mock(DashboardController::class, fn ($mock) => $mock
        ->shouldReceive('data')
        ->andReturn([]));
    $this->mock(ApplicationSettingService::class, fn ($mock) => $mock
        ->shouldReceive('publicData')
        ->andReturn([]));
    $this->mock(ApplicationTranslationService::class, fn ($mock) => $mock
        ->shouldReceive('all')
        ->andReturn([]));

    $request = Request::create('/');
    $request->setUserResolver(fn () => $user);
    $notifications = app(HandleInertiaRequests::class)->share($request)['notifications'];

    expect($notifications['count'])->toBe(1)
        ->and($notifications['latest'][0]['id'])->toBe($unreadAssignment->id);
});
