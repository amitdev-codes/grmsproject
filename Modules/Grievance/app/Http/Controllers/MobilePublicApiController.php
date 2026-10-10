<?php

namespace Modules\Grievance\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Modules\Frontend\Services\PublicAnalyticsService;
use Modules\Grievance\DataTransferObjects\GrievanceIntakeData;
use Modules\Grievance\Http\Requests\StorePublicGrievanceRequest;
use Modules\Grievance\Http\Requests\TrackGrievanceRequest;
use Modules\Grievance\Http\Resources\DistrictResource;
use Modules\Grievance\Http\Resources\DivisionResource;
use Modules\Grievance\Http\Resources\GrievanceCategoryResource;
use Modules\Grievance\Http\Resources\GrievanceTrackingResource;
use Modules\Grievance\Jobs\SubmitGrievanceJob;
use Modules\Grievance\Models\GrievanceCategory;
use Modules\Grievance\Services\GrievanceIntakeSecurityService;
use Modules\Grievance\Services\GrievanceRegistrationService;
use Modules\Master\Models\District;
use Modules\Master\Models\Division;
use OpenApi\Attributes as OA;

class MobilePublicApiController extends Controller
{
    public function __construct(
        protected PublicAnalyticsService $analytics,
        protected GrievanceRegistrationService $grievances,
        protected GrievanceIntakeSecurityService $security,
    ) {}

    #[OA\Get(
        path: '/mobile/captcha',
        summary: 'Get a mobile grievance-lodging captcha challenge',
        tags: ['Mobile App'],
        responses: [
            new OA\Response(response: 200, description: 'Cloudflare site key or a short-lived local math challenge.'),
        ],
    )]
    public function captcha(): JsonResponse
    {
        $settings = $this->security->settings();

        if ($settings->captcha_provider === 'cloudflare_turnstile') {
            return response()->json([
                'provider' => 'cloudflare_turnstile',
                'site_key' => $settings->cloudflare_site_key,
            ]);
        }

        return response()->json($this->security->issueLocalChallenge());
    }

    #[OA\Get(
        path: '/mobile/landing',
        summary: 'Get mobile landing page data',
        tags: ['Mobile App'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Public landing page statistics and grievance reference data.',
                content: new OA\JsonContent(
                    properties: [new OA\Property(property: 'data', type: 'object')],
                    type: 'object',
                ),
            ),
        ],
    )]
    public function landing(Request $request): JsonResponse
    {
        return response()->json([
            'data' => [
                'application_name' => config('app.name'),
                'reports' => $this->analytics->landingData(),
                'categories' => GrievanceCategoryResource::collection(
                    GrievanceCategory::active()->orderBy('sort_order')->get(),
                )->resolve($request),
                'districts' => DistrictResource::collection(
                    District::query()->orderBy('name')->get(),
                )->resolve($request),
                'divisions' => DivisionResource::collection(
                    Division::query()->orderBy('name')->get(),
                )->resolve($request),
            ],
        ]);
    }

    #[OA\Get(
        path: '/mobile/reports/summary',
        summary: 'Get public grievance summary report',
        tags: ['Mobile App'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Public aggregate counts, status summary, and monthly totals.',
                content: new OA\JsonContent(
                    properties: [new OA\Property(property: 'data', type: 'object')],
                    type: 'object',
                ),
            ),
        ],
    )]
    public function reportSummary(): JsonResponse
    {
        return response()->json([
            'data' => [
                'year' => now()->year,
                ...$this->analytics->landingData(),
            ],
        ]);
    }

    #[OA\Post(
        path: '/mobile/grievances',
        summary: 'Lodge a grievance from the mobile app',
        requestBody: new OA\RequestBody(
            required: true,
            content: [
                new OA\MediaType(
                    mediaType: 'multipart/form-data',
                    schema: new OA\Schema(
                        required: ['description', 'is_anonymous'],
                        properties: [
                            new OA\Property(property: 'description', type: 'string', minLength: 20, maxLength: 3000),
                            new OA\Property(property: 'category_id', type: 'integer', nullable: true),
                            new OA\Property(property: 'district_id', type: 'integer', nullable: true),
                            new OA\Property(property: 'division_id', type: 'integer', nullable: true),
                            new OA\Property(property: 'is_anonymous', type: 'boolean'),
                            new OA\Property(property: 'contact_name', type: 'string', nullable: true),
                            new OA\Property(property: 'contact_phone', type: 'string', nullable: true),
                            new OA\Property(property: 'contact_email', type: 'string', format: 'email', nullable: true),
                            new OA\Property(property: 'location_description', type: 'string', nullable: true),
                            new OA\Property(property: 'latitude', type: 'number', format: 'float', nullable: true),
                            new OA\Property(property: 'longitude', type: 'number', format: 'float', nullable: true),
                            new OA\Property(property: 'captcha_token', description: 'Cloudflare Turnstile response token when that provider is selected.', type: 'string', nullable: true),
                            new OA\Property(property: 'captcha_challenge_id', description: 'Local challenge ID from GET /mobile/captcha.', type: 'string', nullable: true),
                            new OA\Property(property: 'captcha_answer', description: 'Answer to the local challenge from GET /mobile/captcha.', type: 'string', nullable: true),
                            new OA\Property(
                                property: 'attachments[]',
                                description: 'Attach up to five supported evidence files, up to 20 MB each.',
                                type: 'array',
                                items: new OA\Items(type: 'string', format: 'binary'),
                                nullable: true,
                            ),
                        ],
                        type: 'object',
                    ),
                ),
                new OA\MediaType(
                    mediaType: 'application/json',
                    schema: new OA\Schema(
                        required: ['description', 'is_anonymous'],
                        properties: [
                            new OA\Property(property: 'description', type: 'string', minLength: 20, maxLength: 3000),
                            new OA\Property(property: 'category_id', type: 'integer', nullable: true),
                            new OA\Property(property: 'district_id', type: 'integer', nullable: true),
                            new OA\Property(property: 'division_id', type: 'integer', nullable: true),
                            new OA\Property(property: 'is_anonymous', type: 'boolean'),
                            new OA\Property(property: 'contact_name', type: 'string', nullable: true),
                            new OA\Property(property: 'contact_phone', type: 'string', nullable: true),
                            new OA\Property(property: 'contact_email', type: 'string', format: 'email', nullable: true),
                            new OA\Property(property: 'location_description', type: 'string', nullable: true),
                            new OA\Property(property: 'latitude', type: 'number', format: 'float', nullable: true),
                            new OA\Property(property: 'longitude', type: 'number', format: 'float', nullable: true),
                            new OA\Property(property: 'captcha_token', description: 'Cloudflare Turnstile response token when that provider is selected.', type: 'string', nullable: true),
                            new OA\Property(property: 'captcha_challenge_id', description: 'Local challenge ID from GET /mobile/captcha.', type: 'string', nullable: true),
                            new OA\Property(property: 'captcha_answer', description: 'Answer to the local challenge from GET /mobile/captcha.', type: 'string', nullable: true),
                        ],
                        type: 'object',
                    ),
                ),
            ],
        ),
        tags: ['Mobile App'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Grievance registered. Keep the reference number to track it.',
                content: new OA\JsonContent(
                    properties: [new OA\Property(property: 'data', type: 'object')],
                    type: 'object',
                ),
            ),
            new OA\Response(response: 422, description: 'The submitted grievance data is invalid.'),
            new OA\Response(response: 409, description: 'A matching grievance was already lodged within the duplicate-check window.'),
            new OA\Response(response: 429, description: 'The public submission rate limit was exceeded.'),
        ],
    )]
    public function lodge(StorePublicGrievanceRequest $request): JsonResponse
    {
        if (! $this->security->verifyMobileCaptcha(
            $request->validated('captcha_token'),
            $request->input('captcha_challenge_id'),
            $request->input('captcha_answer'),
            (string) $request->ip(),
        )) {
            throw ValidationException::withMessages([
                'captcha_token' => 'Captcha verification failed. Please try again.',
            ]);
        }

        $data = GrievanceIntakeData::fromMobileApi(
            $request->safe()->except(['attachments', 'captcha_token', 'captcha_challenge_id', 'captcha_answer']),
            $request->file('attachments', []),
            $request->ip(),
        );

        if ($this->grievances->findRecentPublicDuplicate($data)) {
            return response()->json([
                'message' => 'A matching grievance was recently submitted. Please track your existing grievance instead.',
            ], 409);
        }

        $grievance = $this->grievances->submitQuick($data);
        SubmitGrievanceJob::dispatchSync($grievance->id);

        return response()->json([
            'data' => [
                'reference_number' => $grievance->reference_no,
                'status' => $grievance->fresh()->status,
                'sla_due_at' => $grievance->sla_due_at?->toIso8601String(),
                'tracking_endpoint' => url('/api/v1/mobile/grievances/track'),
            ],
        ], 201);
    }

    #[OA\Post(
        path: '/mobile/grievances/track',
        summary: 'Track a grievance',
        description: 'Send tracking details in the request body so contact information is not exposed in URL logs. A non-anonymous grievance requires the complainant phone number or email used when lodging it. An anonymous grievance can be tracked by reference number.',
        tags: ['Mobile App'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['ref'],
                properties: [
                    new OA\Property(property: 'ref', type: 'string'),
                    new OA\Property(
                        property: 'contact',
                        description: 'Phone number or email used when registering a named grievance.',
                        type: 'string',
                        nullable: true,
                    ),
                ],
                type: 'object',
            ),
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Current grievance status and status history.',
                content: new OA\JsonContent(
                    properties: [new OA\Property(property: 'data', type: 'object')],
                    type: 'object',
                ),
            ),
            new OA\Response(response: 404, description: 'Grievance not found or contact verification failed.'),
        ],
    )]
    public function track(TrackGrievanceRequest $request): JsonResponse
    {
        $grievance = $this->grievances->track(
            $request->validated('ref'),
            $request->validated('contact'),
        );

        if (! $grievance) {
            return response()->json(['message' => 'Grievance not found.'], 404);
        }

        return response()->json([
            'data' => (new GrievanceTrackingResource($grievance))->resolve($request),
        ]);
    }
}
