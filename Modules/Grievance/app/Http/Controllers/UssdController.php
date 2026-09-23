<?php

namespace Modules\Grievance\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Modules\Grievance\Services\GrievanceRegistrationService;

class UssdController extends Controller
{
    public function __construct(protected GrievanceRegistrationService $service) {}

    public function handle(Request $request): Response
    {
        abort_unless(config('grievance.ussd.enabled'), 404);

        $this->validateProvider($request);

        $validated = $request->validate([
            'sessionId' => ['required', 'string', 'max:120'],
            'phoneNumber' => ['required', 'string', 'max:30'],
            'text' => ['nullable', 'string', 'max:2000'],
            'serviceCode' => ['nullable', 'string', 'max:50'],
        ]);

        $text = (string) ($validated['text'] ?? '');
        $response = $this->service->handleUssd(
            $validated['sessionId'],
            $validated['phoneNumber'],
            $text,
        );

        Log::info('USSD grievance request handled', [
            'session_id' => $validated['sessionId'],
            'phone_number' => $validated['phoneNumber'],
            'step' => substr_count($text, '*'),
            'completed' => str_starts_with($response, 'END'),
        ]);

        return response($response, 200)->header('Content-Type', 'text/plain; charset=UTF-8');
    }

    protected function validateProvider(Request $request): void
    {
        $configuredServiceCode = config('grievance.ussd.service_code');
        $providedServiceCode = (string) $request->input('serviceCode', '');

        abort_if(
            $configuredServiceCode !== null
            && ($providedServiceCode === ''
                || ! hash_equals((string) $configuredServiceCode, $providedServiceCode)),
            403,
        );

        $secret = config('grievance.ussd.shared_secret');
        if (! $secret) {
            return;
        }

        $providedSecret = (string) $request->header('X-USSD-Secret', '');
        abort_unless($providedSecret !== '' && hash_equals((string) $secret, $providedSecret), 403);
    }
}
