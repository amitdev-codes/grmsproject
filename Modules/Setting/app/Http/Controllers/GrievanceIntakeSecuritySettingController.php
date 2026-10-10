<?php

namespace Modules\Setting\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Setting\Http\Requests\UpdateGrievanceIntakeSecuritySettingRequest;
use Modules\Setting\Models\GrievanceIntakeSecuritySetting;

class GrievanceIntakeSecuritySettingController extends Controller
{
    public function edit(): Response
    {
        $settings = GrievanceIntakeSecuritySetting::current();

        return Inertia::render('Setting::Settings/GrievanceIntakeSecuritySettings', [
            'settings' => [
                'lodging_requests_per_minute' => $settings->lodging_requests_per_minute,
                'duplicate_window_hours' => $settings->duplicate_window_hours,
                'whitelisted_ip_addresses' => $settings->whitelisted_ip_addresses ?? [],
                'blacklisted_ip_addresses' => $settings->blacklisted_ip_addresses ?? [],
                'captcha_provider' => $settings->captcha_provider,
                'cloudflare_site_key' => $settings->cloudflare_site_key,
                'has_cloudflare_secret_key' => filled($settings->cloudflare_secret_key),
            ],
        ]);
    }

    public function update(UpdateGrievanceIntakeSecuritySettingRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $settings = GrievanceIntakeSecuritySetting::current();

        if (blank($data['cloudflare_secret_key'])) {
            unset($data['cloudflare_secret_key']);
        }

        $settings->update($data);

        return back()->with('success', 'Grievance security settings updated.');
    }
}
