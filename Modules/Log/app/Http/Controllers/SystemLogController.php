<?php

namespace Modules\Log\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SystemLogController extends LogController
{
    public function index(Request $request): Response
    {
        $this->authorizeLogs($request);

        [$search, $page, $perPage] = $this->listParams($request);
        $date = $this->validatedDate((string) $request->input('date', now()->toDateString()));
        $result = $this->systemLogs($date, $search, $page, $perPage);

        return Inertia::render('Log::SystemLogs/Index', [
            'data' => $result['items'],
            'meta' => $result['meta'],
            'selectedDate' => $date,
            'availableDates' => $this->availableSystemLogDates(),
        ]);
    }
}
