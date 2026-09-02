<?php

namespace Modules\Grievance\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Rules\ValidCaptcha;
use Illuminate\Http\JsonResponse;
use Modules\Grievance\Http\Requests\RateGrievanceRequest;
use Modules\Grievance\Http\Requests\StoreGrievanceMessageRequest;
use Modules\Grievance\Http\Requests\StorePublicGrievanceRequest;
use Modules\Grievance\Http\Requests\TrackGrievanceRequest;
use Modules\Grievance\Http\Resources\GrievanceTrackingResource;
use Modules\Grievance\Models\Grievance;
use Modules\Grievance\Services\GrievanceRegistrationService;

class PublicGrievanceController extends Controller
{
    public function __construct(protected GrievanceRegistrationService $service) {}

    public function store(StorePublicGrievanceRequest $request): JsonResponse
    {
        $request->validate(['captcha_token' => [new ValidCaptcha]]);

        $grievance = $this->service->registerPublic(
            $request->safe()->except(['attachments', 'captcha_token']),
            $request->file('attachments', []),
        );

        return response()->json([
            'data' => [
                'reference_number' => $grievance->reference_no,
                'sla_due_at' => $grievance->sla_due_at?->toIso8601String(),
            ],
        ], 201);
    }

    public function track(TrackGrievanceRequest $request): GrievanceTrackingResource|JsonResponse
    {
        $grievance = $this->service->track($request->validated('ref'), $request->validated('contact'));

        if (! $grievance) {
            return response()->json(['message' => 'Not found'], 404);
        }

        return new GrievanceTrackingResource($grievance);
    }

    public function storeMessage(StoreGrievanceMessageRequest $request, Grievance $grievance): JsonResponse
    {
        $message = $this->service->addCitizenMessage($grievance, $request->validated('contact'), $request->validated('body'));

        if (! $message) {
            return response()->json(['message' => 'Unable to add message.'], 422);
        }

        return response()->json([
            'id' => $message->id,
            'sender' => $message->sender,
            'body' => $message->body,
            'created_at' => $message->created_at->toIso8601String(),
        ], 201);
    }

    public function rate(RateGrievanceRequest $request, Grievance $grievance): JsonResponse
    {
        $updated = $this->service->rate($grievance, $request->validated('contact'), $request->validated('satisfaction_rating'));

        if (! $updated) {
            return response()->json(['message' => 'Unable to submit rating.'], 422);
        }

        return response()->json(['satisfaction_rating' => $updated->satisfaction_rating]);
    }
}
