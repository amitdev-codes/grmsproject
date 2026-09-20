<?php

namespace Modules\Log\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AuditLogController extends LogController
{
    public function index(Request $request): Response
    {
        $this->authorizeLogs($request);

        [$search, $page, $perPage] = $this->listParams($request);
        $result = $this->auditLogs($search, $page, $perPage);

        return Inertia::render('Log::AuditLogs/Index', [
            'data' => $result['items'],
            'meta' => $result['meta'],
        ]);
    }
}
