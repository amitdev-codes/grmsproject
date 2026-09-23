<?php

namespace Modules\Setting\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Setting\Http\Requests\StoreSmsSettingRequest;
use Modules\Setting\Http\Requests\UpdateSmsSettingRequest;
use Modules\Setting\Models\SmsSetting;

class SmsSettingController extends Controller
{
    public function index(Request $request): Response
    {
        $search = trim((string) $request->input('search', ''));
        $settings = SmsSetting::query()
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search): void {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('provider', 'like', "%{$search}%")
                    ->orWhere('sender_id', 'like', "%{$search}%");
            }))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Setting::Settings/SmsSettings/Index', [
            'data' => $settings->items(),
            'meta' => [
                'current_page' => $settings->currentPage(),
                'last_page' => $settings->lastPage(),
                'per_page' => $settings->perPage(),
                'total' => $settings->total(),
                'from' => $settings->firstItem(),
                'to' => $settings->lastItem(),
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Setting::Settings/SmsSettings/Form', ['setting' => null]);
    }

    public function store(StoreSmsSettingRequest $request): RedirectResponse
    {
        SmsSetting::create($request->validated());

        return to_route('settings.sms.index')->with('success', 'SMS setting created.');
    }

    public function edit(SmsSetting $smsSetting): Response
    {
        return Inertia::render('Setting::Settings/SmsSettings/Form', ['setting' => $smsSetting]);
    }

    public function update(UpdateSmsSettingRequest $request, SmsSetting $smsSetting): RedirectResponse
    {
        $data = $request->validated();
        foreach (['api_key', 'api_secret'] as $secret) {
            if (blank($data[$secret] ?? null)) {
                unset($data[$secret]);
            }
        }
        $smsSetting->update($data);

        return to_route('settings.sms.index')->with('success', 'SMS setting updated.');
    }

    public function destroy(SmsSetting $smsSetting): RedirectResponse
    {
        $smsSetting->delete();

        return back()->with('success', 'SMS setting deleted.');
    }
}
