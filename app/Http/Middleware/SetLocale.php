<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $supported = array_keys(config('locales.supported'));

        $locale = $request->user()?->locale
            ?? $request->session()->get('locale')
            ?? config('locales.default');

        if (! in_array($locale, $supported, true)) {
            $locale = config('locales.default');
        }

        App::setLocale($locale);

        return $next($request);
    }
}
