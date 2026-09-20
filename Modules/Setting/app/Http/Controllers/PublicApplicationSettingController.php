<?php

namespace Modules\Setting\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\Setting\Services\ApplicationSettingService;

class PublicApplicationSettingController extends Controller
{
    public function __construct(protected ApplicationSettingService $service) {}

    public function show(): JsonResponse
    {
        return response()->json([
            'applicationSettings' => $this->service->publicData(),
        ]);
    }
}
