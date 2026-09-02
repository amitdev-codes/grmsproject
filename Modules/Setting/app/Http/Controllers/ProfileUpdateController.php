<?php

namespace Modules\Setting\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Master\Models\District;
use Modules\Master\Models\Division;
use Modules\Master\Models\Section;
use Modules\Setting\Http\Requests\ProfileUpdateRequest;

class ProfileUpdateController extends Controller
{
    public function edit(Request $request): Response
    {
        $user = $request->user()->load('media');

        return Inertia::render('Setting::Settings/Profile', [
            'mustVerifyEmail' => $user instanceof MustVerifyEmail,
            'status' => session('status'),
            'districts' => District::select('id', 'name')->get(),
            'divisions' => Division::select('id', 'name')->get(),
            'sections' => Section::select('id', 'name', 'division_id')->get(),
        ]);
    }

    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->safe()->only(['username', 'email']);

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        if ($request->filled('password')) {
            $user->password = Hash::make($request->validated('password'));
        }

        $user->save();

        if ($request->boolean('remove_avatar')) {
            $user->clearMediaCollection('avatar');
        } elseif ($request->hasFile('avatar')) {
            $user->clearMediaCollection('avatar');
            $user->addMedia($request->file('avatar'))->toMediaCollection('avatar');
        }

        return to_route('profile.edit');
    }
}
