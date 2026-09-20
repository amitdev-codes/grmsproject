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

class GrievanceRoutingController extends Controller
{
    public function __construct(protected GrievanceRoutingService $routing) {}

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
        $this->routing->allocateToDivision($grievance, $request->validated('division_id'), $request->user(), $request->validated('remarks'));

        return redirect()->route('grievances.pending')->with('success', 'Grievance allocated to division.');
    }

    public function allocateSection(AllocateSectionRequest $request, Grievance $grievance): RedirectResponse
    {
        $this->routing->allocateToSection($grievance, $request->validated('section_id'), $request->user(), $request->validated('remarks'));

        return redirect()->route('grievances.pending')->with('success', 'Grievance allocated to section.');
    }

    public function rejectAllocation(RejectAllocationRequest $request, Grievance $grievance): RedirectResponse
    {
        $this->routing->rejectAllocation($grievance, $request->user(), $request->validated('reason'));

        return back()->with('success', 'Allocation rejected and sent back for reallocation.');
    }

    public function assignOfficer(AssignOfficerRequest $request, Grievance $grievance): RedirectResponse
    {
        $this->routing->assignOfficer($grievance, $request->validated('officer_id'), $request->user());

        return redirect()->route('grievances.pending')->with('success', 'Investigating officer assigned.');
    }

    public function reject(RejectGrievanceRequest $request, Grievance $grievance): RedirectResponse
    {
        $this->routing->reject($grievance, $request->user(), $request->validated('reason'));

        return back()->with('success', 'Grievance rejected with a recorded reason.');
    }

    public function resolve(ResolveGrievanceRequest $request, Grievance $grievance): RedirectResponse
    {
        $this->routing->resolve($grievance, $request->user(), $request->validated('reason'));

        return back()->with('success', 'Grievance resolved with a recorded reason.');
    }

    public function startInvestigation(StartInvestigationRequest $request, Grievance $grievance): RedirectResponse
    {
        $this->routing->startInvestigation($grievance, $request->user(), $request->validated('remarks'));

        return redirect()->route('grievances.pending')->with('success', 'Investigation started.');
    }

    public function close(CloseGrievanceRequest $request, Grievance $grievance): RedirectResponse
    {
        $this->routing->close($grievance, $request->user(), $request->validated('reason'));

        return back()->with('success', 'Grievance closed with a recorded reason.');
    }
}
