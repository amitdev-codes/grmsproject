<?php

namespace Modules\Frontend\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\Frontend\Http\Requests\StorePublicGrievanceRequest;
use Modules\Grievance\Http\Resources\GrievanceTrackingResource;
use Modules\Grievance\Models\VoiceGrievance;
use Modules\Grievance\Services\GrievanceService;

class PublicGrievanceController extends Controller
{
    public function __construct(protected GrievanceService $grievanceService) {}

    public function store(StorePublicGrievanceRequest $request): JsonResponse
    {
        $grievance = $this->grievanceService->fileFromPublic($request->validated(), $request->file('attachments', []));

        return response()->json([
            'data' => [
                'reference_number' => $grievance->reference_number,
                'sla_due_at' => $grievance->sla_due_at,
            ],
        ], 201);
    }

    /**
     * @throws \Throwable
     */
    public function track(Request $request): JsonResponse
    {
        $request->validate([
            'ref' => ['required', 'string'],
            'contact' => ['nullable', 'string'],
        ]);

        $grievance = $this->grievanceService->findForTracking(
            $request->string('ref')->toString(),
            $request->string('contact')->toString(),
        );

        if (! $grievance) {
            return response()->json(['message' => 'Not found'], 404);
        }

        return response()->json(new GrievanceTrackingResource($grievance));
    }

    public function message(Request $request, string $reference): JsonResponse
    {
        $request->validate(['body' => ['required', 'string', 'max:2000']]);

        $message = $this->grievanceService->addCitizenMessage($reference, $request->string('body')->toString());

        if (! $message) {
            return response()->json(['message' => 'Not found'], 404);
        }

        return response()->json($message, 201);
    }

    public function rating(Request $request, string $reference): JsonResponse
    {
        $request->validate(['satisfaction_rating' => ['required', 'integer', 'between:1,5']]);

        $this->grievanceService->rate($reference, $request->integer('satisfaction_rating'));

        return response()->json(['message' => 'Thank you']);
    }

    public function storeVoice(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'audio' => ['required', 'file', 'max:20480', 'mimes:mp3,wav,m4a,aac,ogg,oga,webm,mp4'],
            'duration_seconds' => ['nullable', 'integer', 'min:1', 'max:600'],
            'language_selected' => ['required', 'in:en,st,zu,xh,other'],
            'contact_phone' => ['nullable', 'string', 'max:30'],
        ]);

        $audio = $request->file('audio');
        $audioPath = $audio->store('voice-grievances', 'local');
        if (! $audioPath) {
            throw new \RuntimeException('Unable to save the voice grievance audio file.');
        }

        try {
            $voiceGrievance = DB::transaction(function () use ($validated, $audio, $audioPath): VoiceGrievance {
                $voiceGrievance = VoiceGrievance::query()->create([
                    'reference_no' => 'VGR-'.Str::ulid(),
                    'audio_path' => $audioPath,
                    'audio_mime' => $audio->getMimeType() ?: 'application/octet-stream',
                    'duration_seconds' => $validated['duration_seconds'] ?? null,
                    'language_selected' => $validated['language_selected'],
                    'contact_phone' => $validated['contact_phone'] ?? null,
                    'status' => 'received',
                ]);

                return $voiceGrievance;
            });
        } catch (\Throwable $exception) {
            Storage::disk('local')->delete($audioPath);

            throw $exception;
        }

        return to_route('voice-grievance.received', ['reference' => $voiceGrievance->reference_no]);
    }
}
