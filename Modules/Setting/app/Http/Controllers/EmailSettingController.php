<?php

namespace Modules\Setting\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Setting\Http\Requests\StoreEmailSettingRequest;
use Modules\Setting\Http\Requests\UpdateEmailSettingRequest;
use Modules\Setting\Models\EmailSetting;

class EmailSettingController extends Controller
{
    public function index(Request $request): Response
    {
        $search = trim((string) $request->input('search', ''));
        $settings = EmailSetting::query()
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search): void {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('host', 'like', "%{$search}%")
                    ->orWhere('from_address', 'like', "%{$search}%");
            }))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Setting::Settings/EmailSettings/Index', [
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
        return Inertia::render('Setting::Settings/EmailSettings/Form', ['setting' => null]);
    }

    public function store(StoreEmailSettingRequest $request): RedirectResponse
    {
        EmailSetting::create($request->validated());

        return to_route('settings.email.index')->with('success', 'Email setting created.');
    }

    public function edit(EmailSetting $emailSetting): Response
    {
        return Inertia::render('Setting::Settings/EmailSettings/Form', ['setting' => $emailSetting]);
    }

    public function update(UpdateEmailSettingRequest $request, EmailSetting $emailSetting): RedirectResponse
    {
        $data = $request->validated();
        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }
        $emailSetting->update($data);

        return to_route('settings.email.index')->with('success', 'Email setting updated.');
    }

    public function destroy(EmailSetting $emailSetting): RedirectResponse
    {
        $emailSetting->delete();

        return back()->with('success', 'Email setting deleted.');
    }
}
