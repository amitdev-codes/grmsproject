<?php

namespace Modules\Grievance\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Grievance\DataTransferObjects\GrievanceIntakeData;
use Modules\Grievance\Http\Requests\ReviewVoiceGrievanceRequest;
use Modules\Grievance\Models\GrievanceCategory;
use Modules\Grievance\Models\VoiceGrievance;
use Modules\Grievance\Services\GrievanceRegistrationService;
use Modules\Master\Models\District;
use Symfony\Component\HttpFoundation\StreamedResponse;

class VoiceGrievanceController extends Controller
{
    public function index(): Response
    {
        $voiceGrievances = VoiceGrievance::query()
            ->with(['assignedOfficer:id,name', 'grievance:id,reference_no'])
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Grievance::VoiceGrievances/Index', [
            'voiceGrievances' => $voiceGrievances,
        ]);
    }

    public function show(VoiceGrievance $voiceGrievance): Response
    {
        $voiceGrievance->load(['assignedOfficer:id,name', 'grievance:id,reference_no']);

        return Inertia::render('Grievance::VoiceGrievances/Show', [
            'voiceGrievance' => $voiceGrievance,
            'audioUrl' => route('voice-grievances.audio', $voiceGrievance),
            'categories' => GrievanceCategory::active()
                ->orderBy('name_en')
                ->get(['id', 'name_en']),
            'districts' => District::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function audio(VoiceGrievance $voiceGrievance): StreamedResponse
    {
        abort_unless(Storage::disk('local')->exists($voiceGrievance->audio_path), 404);

        return Storage::disk('local')->response($voiceGrievance->audio_path, null, [
            'Content-Type' => $voiceGrievance->audio_mime,
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store',
        ]);
    }

    public function review(ReviewVoiceGrievanceRequest $request, VoiceGrievance $voiceGrievance): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($request, $voiceGrievance, $data): void {
            $voiceGrievance = VoiceGrievance::query()->lockForUpdate()->findOrFail($voiceGrievance->id);
            $this->ensureNotFiled($voiceGrievance);
            $this->ensureAvailableToOfficer($request, $voiceGrievance);

            $voiceGrievance->update([
                'transcript_original' => $data['transcript_original'],
                'transcript_english' => $data['transcript_english'],
                'language_detected' => $data['language_detected'] ?? null,
                'assigned_officer_id' => $voiceGrievance->assigned_officer_id ?? $request->user()->id,
                'status' => 'reviewed',
                'failure_reason' => null,
            ]);
        });

        return back()->with('success', 'Voice grievance transcription saved.');
    }

    public function file(
        ReviewVoiceGrievanceRequest $request,
        VoiceGrievance $voiceGrievance,
        GrievanceRegistrationService $registrationService,
    ): RedirectResponse {
        $data = $request->validated();

        $grievance = DB::transaction(function () use ($request, $voiceGrievance, $data, $registrationService) {
            $voiceGrievance = VoiceGrievance::query()->lockForUpdate()->findOrFail($voiceGrievance->id);
            $this->ensureNotFiled($voiceGrievance);
            $this->ensureAvailableToOfficer($request, $voiceGrievance);

            $grievance = $registrationService->submit(new GrievanceIntakeData(
                channel: 'helpdesk',
                description: $data['transcript_english'],
                categoryId: (int) $data['category_id'],
                districtId: isset($data['district_id']) ? (int) $data['district_id'] : null,
                contactPhone: $voiceGrievance->contact_phone,
                isAnonymous: blank($voiceGrievance->contact_phone),
                registeredBy: $request->user()->id,
                preferredLanguage: $voiceGrievance->language_detected ?? $voiceGrievance->language_selected ?? 'en',
                metadata: [
                    'voice_grievance_id' => (string) $voiceGrievance->id,
                    'voice_grievance_reference' => $voiceGrievance->reference_no,
                    'transcript_original' => $data['transcript_original'],
                ],
            ));

            $voiceGrievance->update([
                'transcript_original' => $data['transcript_original'],
                'transcript_english' => $data['transcript_english'],
                'language_detected' => $data['language_detected'] ?? null,
                'assigned_officer_id' => $voiceGrievance->assigned_officer_id ?? $request->user()->id,
                'grievance_id' => $grievance->id,
                'status' => 'filed',
                'failure_reason' => null,
            ]);

            return $grievance;
        });

        return to_route('grievances.edit', $grievance)->with('success', "Grievance filed: {$grievance->reference_no}");
    }

    private function ensureNotFiled(VoiceGrievance $voiceGrievance): void
    {
        abort_if($voiceGrievance->grievance_id !== null, 409, 'This voice grievance has already been filed.');
    }

    private function ensureAvailableToOfficer(Request $request, VoiceGrievance $voiceGrievance): void
    {
        abort_if(
            $voiceGrievance->assigned_officer_id !== null
            && $voiceGrievance->assigned_officer_id !== $request->user()->id
            && ! $request->user()->hasRole('Super Admin'),
            403,
        );
    }
}
