<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Modules\Grievance\Services\GrievanceIntakeSecurityService;
use Modules\Setting\Models\GrievanceIntakeSecuritySetting;

uses(RefreshDatabase::class);

it('issues single-use, expiring local mobile captcha challenges', function () {
    $security = app(GrievanceIntakeSecurityService::class);
    $challenge = $security->issueLocalChallenge();

    expect($challenge['provider'])->toBe('local')
        ->and($challenge['expires_in_seconds'])->toBe(300)
        ->and(preg_match('/What is (\d+) \+ (\d+)\?/', $challenge['question'], $matches))->toBe(1);

    $answer = (string) ((int) $matches[1] + (int) $matches[2]);

    expect($security->verifyMobileCaptcha(
        null,
        $challenge['challenge_id'],
        $answer,
        '127.0.0.1',
    ))->toBeTrue()
        ->and($security->verifyMobileCaptcha(
            null,
            $challenge['challenge_id'],
            $answer,
            '127.0.0.1',
        ))->toBeFalse();
});

it('blocks blacklisted IPs and rate limits public submissions', function () {
    $setting = GrievanceIntakeSecuritySetting::query()->create([
        'lodging_requests_per_minute' => 1,
        'duplicate_window_hours' => 24,
        'whitelisted_ip_addresses' => [],
        'blacklisted_ip_addresses' => ['192.0.2.10'],
        'captcha_provider' => 'local',
    ]);

    $this->withServerVariables(['REMOTE_ADDR' => '192.0.2.10'])
        ->postJson('/api/v1/mobile/grievances')
        ->assertForbidden();

    $setting->update(['blacklisted_ip_addresses' => []]);
    RateLimiter::clear('public-grievance-lodging:192.0.2.11');

    $this->withServerVariables(['REMOTE_ADDR' => '192.0.2.11'])
        ->postJson('/api/v1/mobile/grievances')
        ->assertUnprocessable();

    $this->withServerVariables(['REMOTE_ADDR' => '192.0.2.11'])
        ->postJson('/api/v1/mobile/grievances')
        ->assertStatus(429);
});

it('renders the public grievance system guide', function () {
    $this->get('/api/grievance-docs')
        ->assertOk()
        ->assertSee('Configured review workflow')
        ->assertSee('/api/documentation');
});
