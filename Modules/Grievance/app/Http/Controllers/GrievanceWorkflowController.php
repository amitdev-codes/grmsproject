<?php

namespace Modules\Grievance\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Modules\Grievance\Http\Requests\SubmitGrievanceWorkflowActionRequest;
use Modules\Grievance\Models\Grievance;
use Modules\Grievance\Models\GrievanceWorkflowDocument;
use Modules\Grievance\Services\GrievanceWorkflowService;
use Symfony\Component\HttpFoundation\StreamedResponse;

class GrievanceWorkflowController extends Controller
{
    public function act(
        SubmitGrievanceWorkflowActionRequest $request,
        Grievance $grievance,
        GrievanceWorkflowService $workflow,
    ): RedirectResponse {
        $workflow->act(
            $grievance,
            $request->user(),
            $request->validated(),
            $request->file('documents', []),
        );

        return back()->with('success', 'Workflow decision recorded.');
    }

    public function download(GrievanceWorkflowDocument $document): StreamedResponse
    {
        abort_unless(Storage::disk($document->disk)->exists($document->path), 404);

        return Storage::disk($document->disk)->response($document->path, $document->original_name, [
            'Content-Type' => $document->mime_type,
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store',
        ]);
    }
}
