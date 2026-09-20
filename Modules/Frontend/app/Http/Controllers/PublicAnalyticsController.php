<?php

namespace Modules\Frontend\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\Frontend\Services\PublicAnalyticsService;

class PublicAnalyticsController extends Controller
{
    public function __construct(protected PublicAnalyticsService $service) {}

    public function summary(): JsonResponse
    {
        return response()->json($this->service->summary());
    }

    public function landingData(): array
    {
        return $this->service->landingData();
    }
}
