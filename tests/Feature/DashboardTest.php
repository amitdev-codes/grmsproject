<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertOk();
});

test('reports route renders the report dashboard page', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('dashboard.reports'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('dashboard/reports'));
});

test('dashboard reports can generate an AI summary from aggregate statistics only', function () {
    config([
        'grievance.ai.enabled' => true,
        'grievance.ai.provider' => 'openai',
        'services.openai.key' => 'test-key',
        'services.openai.model' => 'gpt-test',
    ]);
    Http::fake([
        'api.openai.com/*' => Http::response([
            'choices' => [
                ['message' => ['content' => "- Workload is stable.\n- Resolution rate is 0%."]],
            ],
        ]),
    ]);
    $user = User::factory()->create();

    $this->actingAs($user)
        ->from(route('dashboard'))
        ->post(route('dashboard.ai-summary'))
        ->assertRedirect(route('dashboard'))
        ->assertSessionHas('ai_summary', "- Workload is stable.\n- Resolution rate is 0%.");

    Http::assertSent(function (HttpRequest $request) {
        $prompt = $request->data()['messages'][0]['content'];

        return str_contains($prompt, 'Aggregate dashboard data:')
            && ! str_contains($prompt, 'complainant_phone')
            && ! str_contains($prompt, 'description');
    });
});

test('dashboard reports explain when AI summaries are disabled', function () {
    config(['grievance.ai.enabled' => false]);
    Http::fake();
    $user = User::factory()->create();

    $this->actingAs($user)
        ->from(route('dashboard'))
        ->post(route('dashboard.ai-summary'))
        ->assertRedirect(route('dashboard'))
        ->assertSessionHas('ai_error', 'AI dashboard summaries are not enabled.');

    Http::assertNothingSent();
});
