<?php

namespace Modules\Log\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ActivityLogController extends LogController
{
    public function index(Request $request): Response
    {
        $this->authorizeLogs($request);

        [$search, $page, $perPage] = $this->listParams($request);
        $result = $this->activityLogs($search, $page, $perPage);

        return Inertia::render('Log::ActivityLogs/Index', [
            'data' => $result['items'],
            'meta' => $result['meta'],
        ]);
    }
}
