<?php

namespace Modules\Setting\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Master\Models\District;
use Modules\Setting\Http\Requests\UpdateApplicationSettingRequest;
use Modules\Setting\Services\ApplicationSettingService;


class ApplicationSettingController extends Controller
{
    public function __construct(protected ApplicationSettingService $service) {}

    public function edit(): Response
    {
        return Inertia::render('Setting::Settings/ApplicationSettingForm', [
            'settings' => $this->service->current(),
            'districts' => District::select('id', 'name')->get(),
        ]);
    }

    public function update(UpdateApplicationSettingRequest $request): RedirectResponse
    {
        $this->service->update(
            $request->safe()->except(['logo', 'favicon', 'og_image']),
            $request->only(['logo', 'favicon', 'og_image']),
        );

        return back()->with('success', 'Application settings updated.');
    }
}
