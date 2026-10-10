<?php

namespace Modules\Setting\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Inertia\Inertia;
use Inertia\Response;

class OptimizeAppController extends Controller
{
    private const COMMANDS = [
        'optimize' => [
            'label' => 'Optimize application',
            'description' => 'Cache configuration, routes, events, and Blade views.',
        ],
        'optimize:clear' => [
            'label' => 'Clear all optimization caches',
            'description' => 'Remove cached configuration, routes, events, and views.',
        ],
        'cache:clear' => [
            'label' => 'Clear application cache',
            'description' => 'Clear the configured application cache store.',
        ],
        'config:cache' => [
            'label' => 'Cache configuration',
            'description' => 'Rebuild the configuration cache.',
        ],
        'route:cache' => [
            'label' => 'Cache routes',
            'description' => 'Rebuild the route cache.',
        ],
        'view:cache' => [
            'label' => 'Cache Blade views',
            'description' => 'Compile Blade templates for production.',
        ],
    ];

    public function index(Request $request): Response
    {
        return Inertia::render('Setting::Settings/OptimizeApp', [
            'commands' => self::COMMANDS,
            'result' => $request->session()->get('artisan_result'),
        ]);
    }

    public function run(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'command' => ['required', 'string', 'in:'.implode(',', array_keys(self::COMMANDS))],
        ]);

        $exitCode = Artisan::call($validated['command']);
        $output = trim(Artisan::output());
        $result = [
            'command' => $validated['command'],
            'output' => $output,
            'successful' => $exitCode === 0,
        ];

        return back()->with('artisan_result', $result);
    }
}
