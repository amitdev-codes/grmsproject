<?php

use App\Http\Controllers\DashboardController;
use App\Http\Middleware\HandleInertiaRequests;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Grievance\Models\Grievance;
use Modules\Grievance\Models\GrievanceCategory;
use Modules\Setting\Services\ApplicationSettingService;
use Modules\Setting\Services\ApplicationTranslationService;
use Spatie\Permission\Models\Role;

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

it('marks an owned notification as read when opening its action and redirects internally', function () {
    $this->withoutMiddleware(HandleInertiaRequests::class);
    $user = User::factory()->create();
    $notification = $user->notifications()->create([
        'id' => (string) Str::uuid(),
        'type' => 'test.notification',
        'data' => [
            'title' => 'Open notification',
            'action_url' => 'http://localhost/grievances/1/edit?from=notification',
        ],
    ]);

    $this->actingAs($user)
        ->get(route('notification.open', $notification->id))
        ->assertRedirect('/grievances/1/edit?from=notification');

    expect($notification->fresh()->read_at)->not->toBeNull();
});

it('does not allow opening another users notification', function () {
    $this->withoutMiddleware(HandleInertiaRequests::class);
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $notification = $owner->notifications()->create([
        'id' => (string) Str::uuid(),
        'type' => 'test.notification',
        'data' => [
            'title' => 'Private notification',
            'action_url' => 'http://localhost/grievances/1/edit',
        ],
    ]);

    $this->actingAs($otherUser)
        ->get(route('notification.open', $notification->id))
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
    $otherUser = User::factory()->create();
    $otherUser->notifications()->create([
        'id' => (string) Str::uuid(),
        'type' => 'test.notification',
        'data' => ['title' => 'Another users notification'],
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
        ->and($notifications['latest'][0]['id'])->toBe($unreadAssignment->id)
        ->and(collect($notifications['latest'])->pluck('title'))->not->toContain('Another users notification');
});

it('counts the directors pending final-approval cases in the pending menu badge', function () {
    $directorRole = Role::query()->firstOrCreate(
        ['name' => 'Director', 'guard_name' => 'web'],
        ['name_st' => 'Director', 'code' => 'DIRECTOR', 'status' => true],
    );
    $director = User::factory()->create();
    $director->assignRole($directorRole);

    $categoryId = GrievanceCategory::query()->value('id');
    if (! $categoryId) {
        $categoryId = DB::table('grievance_categories')->insertGetId([
            'ulid' => (string) Str::ulid(),
            'code' => 'PENDING-BADGE',
            'name_en' => 'Pending badge',
            'name_st' => 'Pending badge',
            'slug' => 'pending-badge',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
    $channelId = DB::table('grievance_channels')->value('id');
    if (! $channelId) {
        $channelId = DB::table('grievance_channels')->insertGetId([
            'ulid' => (string) Str::ulid(),
            'code' => 'pending-badge',
            'name' => 'Pending badge',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
    Grievance::query()->create([
        'reference_no' => 'GRM-PENDING-BADGE',
        'grievance_category_id' => $categoryId,
        'channel_id' => $channelId,
        'description' => 'Waiting for final approval.',
        'status' => 'pending_approval',
        'registered_by' => $director->id,
    ]);

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
    $request->setUserResolver(fn () => $director);
    $shared = app(HandleInertiaRequests::class)->share($request);

    expect($shared['pendingGrievances']['count'])->toBe(1)
        ->and($shared['pendingGrievances']['href'])->toBe('/grievances?pending=1');
});
