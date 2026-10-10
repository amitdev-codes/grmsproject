<?php

namespace Modules\Grievance\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Modules\Setting\Models\GrievanceIntakeSecuritySetting;

class GrievanceIntakeSecurityService
{
    public function settings(): GrievanceIntakeSecuritySetting
    {
        return GrievanceIntakeSecuritySetting::current();
    }

    public function isBlacklisted(string $ip): bool
    {
        return in_array($ip, $this->settings()->blacklisted_ip_addresses ?? [], true);
    }

    public function isWhitelisted(string $ip): bool
    {
        return in_array($ip, $this->settings()->whitelisted_ip_addresses ?? [], true);
    }

    public function issueLocalChallenge(): array
    {
        $left = random_int(2, 9);
        $right = random_int(1, 9);
        $id = (string) Str::uuid();

        Cache::put(
            "grievance_mobile_captcha:{$id}",
            (string) ($left + $right),
            now()->addMinutes(5),
        );

        return [
            'provider' => 'local',
            'challenge_id' => $id,
            'question' => "What is {$left} + {$right}?",
            'expires_in_seconds' => 300,
        ];
    }

    public function verifyMobileCaptcha(
        ?string $token,
        ?string $challengeId,
        ?string $answer,
        string $ip,
    ): bool {
        $settings = $this->settings();

        if ($settings->captcha_provider === 'cloudflare_turnstile') {
            return $this->verifyTurnstile($token, $ip, $settings);
        }

        if (! $challengeId || ! $answer || strlen($challengeId) > 50 || strlen($answer) > 20) {
            return false;
        }

        $cacheKey = "grievance_mobile_captcha:{$challengeId}";
        $expected = Cache::pull($cacheKey);

        return is_string($expected) && hash_equals($expected, trim($answer));
    }

    public function verifyWebCaptcha(?string $token, string $ip): bool
    {
        $settings = $this->settings();

        if ($settings->captcha_provider === 'cloudflare_turnstile') {
            return $this->verifyTurnstile($token, $ip, $settings);
        }

        $expected = Session::get('grievance_captcha_answer');

        if (! is_string($token) || $token === '' || strlen($token) > 20
            || ! $expected || ! hash_equals((string) $expected, trim($token))) {
            return false;
        }

        Session::forget('grievance_captcha_answer');

        return true;
    }

    public function duplicateFingerprint(
        string $description,
        ?int $categoryId,
        ?string $phone,
        ?string $email,
        bool $anonymous,
        string $ip,
    ): string {
        $identity = $anonymous
            ? 'ip:'.$ip
            : strtolower(trim($email ?? '')).'|'.preg_replace('/\D+/', '', $phone ?? '');

        $payload = implode('|', [
            (string) $categoryId,
            mb_strtolower((string) preg_replace('/\s+/', ' ', trim($description))),
            $identity,
        ]);

        return hash_hmac('sha256', $payload, (string) config('app.key'));
    }

    private function verifyTurnstile(
        ?string $token,
        string $ip,
        GrievanceIntakeSecuritySetting $settings,
    ): bool {
        if (blank($token) || blank($settings->cloudflare_secret_key)) {
            return false;
        }

        $response = Http::asForm()
            ->timeout(5)
            ->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
                'secret' => $settings->cloudflare_secret_key,
                'response' => $token,
                'remoteip' => $ip,
            ])
            ->throw();

        return $response->json('success') === true;
    }
}
