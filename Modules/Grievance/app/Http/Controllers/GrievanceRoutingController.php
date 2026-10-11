<?php

namespace Modules\Grievance\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\Grievance\Http\Requests\AllocateDivisionRequest;
use Modules\Grievance\Http\Requests\AllocateSectionRequest;
use Modules\Grievance\Http\Requests\AssignOfficerRequest;
use Modules\Grievance\Http\Requests\CloseGrievanceRequest;
use Modules\Grievance\Http\Requests\RejectAllocationRequest;
use Modules\Grievance\Http\Requests\RejectGrievanceRequest;
use Modules\Grievance\Http\Requests\ResolveGrievanceRequest;
use Modules\Grievance\Http\Requests\StartInvestigationRequest;
use Modules\Grievance\Models\Grievance;
use Modules\Grievance\Services\GrievanceRoutingService;
use Modules\Grievance\Services\GrievanceWorkflowService;

class GrievanceRoutingController extends Controller
{
    public function __construct(
        protected GrievanceRoutingService $routing,
        protected GrievanceWorkflowService $workflow,
    ) {}

    public function triageQueue(Request $request): RedirectResponse
    {
        return redirect()->route('grievances.pending');
    }

    public function divisionQueue(Request $request): RedirectResponse
    {
        return redirect()->route('grievances.pending');
    }

    public function sectionQueue(Request $request): RedirectResponse
    {
        return redirect()->route('grievances.pending');
    }

    public function allocateDivision(AllocateDivisionRequest $request, Grievance $grievance): RedirectResponse
    {
        $this->workflow->act($grievance, $request->user(), [
            'decision' => 'approve',
            'division_id' => $request->validated('division_id'),
            'reason' => $request->validated('remarks'),
        ]);

        return redirect()->route('grievances.pending')->with('success', 'Grievance allocated to division.');
    }

    public function allocateSection(AllocateSectionRequest $request, Grievance $grievance): RedirectResponse
    {
        $this->workflow->act($grievance, $request->user(), [
            'decision' => 'approve',
            'section_id' => $request->validated('section_id'),
            'reason' => $request->validated('remarks'),
        ]);

        return redirect()->route('grievances.pending')->with('success', 'Grievance allocated to section.');
    }

    public function rejectAllocation(RejectAllocationRequest $request, Grievance $grievance): RedirectResponse
    {
        $this->workflow->act($grievance, $request->user(), [
            'decision' => 'reject',
            'reason' => $request->validated('reason'),
        ]);

        return back()->with('success', 'Allocation rejected and sent back for reallocation.');
    }

    public function assignOfficer(AssignOfficerRequest $request, Grievance $grievance): RedirectResponse
    {
        $this->workflow->act($grievance, $request->user(), [
            'decision' => 'approve',
            'officer_id' => $request->validated('officer_id'),
        ]);

        return redirect()->route('grievances.pending')->with('success', 'Investigating officer assigned.');
    }

    public function reject(RejectGrievanceRequest $request, Grievance $grievance): RedirectResponse
    {
        $this->workflow->act($grievance, $request->user(), [
            'decision' => 'reject',
            'reason' => $request->validated('reason'),
        ]);

        return back()->with('success', 'Grievance rejected with a recorded reason.');
    }

    public function resolve(ResolveGrievanceRequest $request, Grievance $grievance): RedirectResponse
    {
        $validated = $request->validated();
        $this->workflow->act(
            $grievance,
            $request->user(),
            [
                'decision' => 'approve',
                'resolution_html' => $validated['resolution_html'],
                'reason' => $validated['reason'] ?? null,
            ],
            $request->file('documents', []),
        );

        return back()->with('success', 'Resolution draft submitted for final approval.');
    }

    public function startInvestigation(StartInvestigationRequest $request, Grievance $grievance): RedirectResponse
    {
        $this->workflow->startInvestigation($grievance, $request->user(), $request->validated('remarks'));

        return redirect()->route('grievances.pending')->with('success', 'Investigation started.');
    }

    public function close(CloseGrievanceRequest $request, Grievance $grievance): RedirectResponse
    {
        $this->routing->close($grievance, $request->user(), $request->validated('reason'));

        return back()->with('success', 'Grievance closed with a recorded reason.');
    }
}
