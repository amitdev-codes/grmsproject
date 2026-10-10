<?php

use App\Models\User;
use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

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
