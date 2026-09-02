<?php

namespace Modules\Frontend\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Frontend\Http\Requests\StorePublicGrievanceRequest;
use Modules\Grievance\Http\Resources\GrievanceTrackingResource;
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
}
