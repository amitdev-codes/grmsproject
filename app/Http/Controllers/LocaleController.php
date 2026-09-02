<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
    public function update(Request $request, string $locale): RedirectResponse
    {
        abort_unless(array_key_exists($locale, config('locales.supported')), 404);

        $request->session()->put('locale', $locale);

        // Persist for logged-in users so it survives across devices/sessions.
        if ($user = $request->user()) {
            $user->update(['locale' => $locale]);
        }

        return back();
    }
}
