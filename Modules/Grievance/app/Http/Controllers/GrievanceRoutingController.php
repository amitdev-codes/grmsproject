<?php

namespace Modules\Grievance\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Grievance\Http\Requests\AllocateDivisionRequest;
use Modules\Grievance\Http\Requests\AllocateSectionRequest;
use Modules\Grievance\Http\Requests\AssignOfficerRequest;
use Modules\Grievance\Http\Requests\RejectAllocationRequest;
use Modules\Grievance\Models\Grievance;
use Modules\Grievance\Services\GrievanceRoutingService;

class GrievanceRoutingController extends Controller
{
    public function __construct(protected GrievanceRoutingService $routing) {}

    public function triageQueue(Request $request): Response
    {
        $this->authorize('viewTriageQueue', Grievance::class); // add a simple gate/policy method, or check role directly
        return Inertia::render('Grievances/TriageQueue', [
            'grievances' => $this->routing->queueForResponsibleManager(),
        ]);
    }

    public function divisionQueue(Request $request): Response
    {
        return Inertia::render('Grievances/DivisionQueue', [
            'grievances' => $this->routing->queueForDivision($request->user()->division_id),
        ]);
    }

    public function sectionQueue(Request $request): Response
    {
        return Inertia::render('Grievances/SectionQueue', [
            'grievances' => $this->routing->queueForSection($request->user()->section_id),
        ]);
    }

    public function allocateDivision(AllocateDivisionRequest $request, Grievance $grievance): RedirectResponse
    {
        $this->routing->allocateToDivision($grievance, $request->validated('division_id'), $request->user());

        return back()->with('success', 'Grievance allocated to division.');
    }

    public function allocateSection(AllocateSectionRequest $request, Grievance $grievance): RedirectResponse
    {
        $this->routing->allocateToSection($grievance, $request->validated('section_id'), $request->user());

        return back()->with('success', 'Grievance allocated to section.');
    }

    public function rejectAllocation(RejectAllocationRequest $request, Grievance $grievance): RedirectResponse
    {
        $this->routing->rejectAllocation($grievance, $request->user(), $request->validated('reason'));

        return back()->with('success', 'Allocation rejected and sent back for reallocation.');
    }

    public function assignOfficer(AssignOfficerRequest $request, Grievance $grievance): RedirectResponse
    {
        $this->routing->assignOfficer($grievance, $request->validated('officer_id'), $request->user());

        return back()->with('success', 'Officer assigned.');
    }
}
