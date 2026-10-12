<?php

use App\Http\Middleware\HandleInertiaRequests;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\Grievance\Database\Seeders\GrievanceWorkflowSeeder;
use Modules\Grievance\Http\Resources\GrievanceResource;
use Modules\Grievance\Models\Grievance;
use Modules\Grievance\Models\GrievanceWorkflowStep;
use Modules\Grievance\Notifications\GrievanceWorkflowActionRequired;
use Modules\Grievance\Services\PendingGrievanceQueueService;
use Modules\UserManagement\Database\Seeders\PermissionSeeder;
use Modules\UserManagement\Database\Seeders\RoleSeeder;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

it('provides every saved workflow step to the live workflow preview', function () {
    $this->withoutMiddleware(PermissionMiddleware::class);
    $this->seed(GrievanceWorkflowSeeder::class);

    $this->actingAs(User::factory()->create())
        ->get(route('grievance-workflow-steps.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('workflowSteps', 9)
            ->where('workflowSteps.0.step_number', 1)
            ->where('workflowSteps.8.step_number', 9)
            ->where('workflowSteps.8.name', 'Performance monitoring and reporting'));
});

it('shows a pending grievance to its configured workflow role and restricts other workflow roles', function () {
    $this->seed(GrievanceWorkflowSeeder::class);
    $rdOfficer = workflowTestUser('RD Officer', ['grievances.view']);
    $otherRole = workflowTestUser('Executive', ['grievances.view']);
    $firstStepId = GrievanceWorkflowStep::query()
        ->where('workflow_key', 'default')
        ->where('step_number', 1)
        ->value('id');
    $grievance = Grievance::query()->create([
        'reference_no' => 'GRM-PENDING-RD-000001',
        'grievance_category_id' => workflowTestCategory(),
        'channel_id' => workflowTestChannel(),
        'description' => 'Pending grievance assigned to the RD Officer workflow step.',
        'status' => 'submitted',
        'current_workflow_step_id' => $firstStepId,
        'registered_by' => $rdOfficer->id,
    ]);

    $this->actingAs($rdOfficer)
        ->get('/grievances?pending=1')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('pendingGrievances.visible', true)
            ->where('pendingGrievances.count', 1)
            ->where('meta.total', 1)
            ->where('data.0.reference_no', $grievance->reference_no));

    $this->actingAs($otherRole)
        ->get('/grievances?pending=1')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('pendingGrievances.visible', true)
            ->where('pendingGrievances.count', 0)
            ->where('meta.total', 0));

    $unassignedUser = workflowTestUser('Unassigned Workflow User', ['grievances.view']);
    expect(app(PendingGrievanceQueueService::class)
        ->queryFor($unassignedUser)
        ->count())->toBe(0);

    $this->actingAs($unassignedUser)
        ->get('/grievances?pending=1')
        ->assertForbidden();
});

it('assigns formal submission to the closest active Helpdesk Officer and enforces ownership', function () {
    $this->withoutMiddleware(HandleInertiaRequests::class);
    Notification::fake();
    $this->seed(GrievanceWorkflowSeeder::class);

    $districtId = DB::table('districts')->insertGetId([
        'ulid' => (string) Str::ulid(),
        'code' => 'HELPDESK-LOCAL',
        'name' => 'Local District',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    $otherDistrictId = DB::table('districts')->insertGetId([
        'ulid' => (string) Str::ulid(),
        'code' => 'HELPDESK-OTHER',
        'name' => 'Other District',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    $divisionId = DB::table('divisions')->insertGetId([
        'ulid' => (string) Str::ulid(),
        'code' => 'HELPDESK-DIV',
        'name' => 'Helpdesk Division',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    $rdOfficer = workflowTestUser('RD Officer', ['grievances.view']);
    $localOfficer = workflowTestUser('Helpdesk Officer', ['grievances.view'], [
        'district_id' => $districtId,
        'division_id' => $divisionId,
    ]);
    $otherOfficer = workflowTestUser('Helpdesk Officer', ['grievances.view'], [
        'district_id' => $otherDistrictId,
        'division_id' => $divisionId,
    ]);
    $grievance = Grievance::query()->create([
        'reference_no' => 'GRM-HELPDESK-LOCAL-000001',
        'grievance_category_id' => workflowTestCategory(),
        'channel_id' => workflowTestChannel(),
        'district_id' => $districtId,
        'division_id' => $divisionId,
        'description' => 'A grievance routed to the local Helpdesk Officer.',
        'status' => 'submitted',
        'current_workflow_step_id' => GrievanceWorkflowStep::query()
            ->where('workflow_key', 'default')
            ->where('step_number', 1)
            ->value('id'),
        'registered_by' => $rdOfficer->id,
    ]);

    $this->actingAs($rdOfficer)->post(route('grievances.workflow-action', $grievance), [
        'decision' => 'reject',
        'reason' => 'Informal resolution was unsuccessful; submit formally.',
    ])->assertRedirect()->assertSessionHasNoErrors();

    $grievance->refresh();
    expect($grievance->currentWorkflowStep->action_type)->toBe('review')
        ->and($grievance->assigned_officer_id)->toBe($localOfficer->id)
        ->and(DB::table('grievance_assignments')
            ->where('grievance_id', $grievance->id)
            ->where('action', 'assigned')
            ->value('to_officer_id'))
        ->toBe($localOfficer->id)
        ->and(app(PendingGrievanceQueueService::class)->queryFor($localOfficer)->whereKey($grievance->id)->exists())->toBeTrue()
        ->and(app(PendingGrievanceQueueService::class)->queryFor($otherOfficer)->whereKey($grievance->id)->exists())->toBeFalse();

    Notification::assertSentTo($localOfficer, GrievanceWorkflowActionRequired::class);
    Notification::assertNotSentTo($otherOfficer, GrievanceWorkflowActionRequired::class);

    $this->actingAs($otherOfficer)->post(route('grievances.workflow-action', $grievance), [
        'decision' => 'approve',
    ])->assertForbidden();

    $this->actingAs($localOfficer)->post(route('grievances.workflow-action', $grievance), [
        'decision' => 'approve',
    ])->assertRedirect()->assertSessionHasNoErrors();

    expect($grievance->fresh()->currentWorkflowStep->action_type)->toBe('screen_allocate');
});

it('keeps an unresolved formal-submission handoff pending when no active Helpdesk Officer exists', function () {
    $this->withoutMiddleware(HandleInertiaRequests::class);
    $this->seed(GrievanceWorkflowSeeder::class);
    $rdOfficer = workflowTestUser('RD Officer', ['grievances.view']);
    $grievance = Grievance::query()->create([
        'reference_no' => 'GRM-HELPDESK-MISSING-000001',
        'grievance_category_id' => workflowTestCategory(),
        'channel_id' => workflowTestChannel(),
        'description' => 'A grievance that cannot be handed off without an active Helpdesk Officer.',
        'status' => 'submitted',
        'current_workflow_step_id' => GrievanceWorkflowStep::query()
            ->where('workflow_key', 'default')
            ->where('step_number', 1)
            ->value('id'),
        'registered_by' => $rdOfficer->id,
    ]);

    $this->actingAs($rdOfficer)->post(route('grievances.workflow-action', $grievance), [
        'decision' => 'reject',
        'reason' => 'Informal resolution was unsuccessful; submit formally.',
    ])->assertRedirect()->assertSessionHasErrors('workflow');

    expect($grievance->fresh()->currentWorkflowStep->action_type)->toBe('informal_resolution')
        ->and($grievance->fresh()->assigned_officer_id)->toBeNull();
});

it('routes each grievance through configured approval levels, rejection returns, draft and final approval', function () {
    $this->withoutMiddleware(HandleInertiaRequests::class);
    Notification::fake();
    Queue::fake();
    Storage::fake('local');
    $this->seed(GrievanceWorkflowSeeder::class);
    seedLegacyWorkflowStepsForExistingFlowTests();

    $divisionId = DB::table('divisions')->insertGetId([
        'ulid' => (string) Str::ulid(),
        'code' => 'TEST-DIV',
        'name' => 'Test Division',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    $sectionId = DB::table('sections')->insertGetId([
        'ulid' => (string) Str::ulid(),
        'code' => 'TEST-SEC',
        'division_id' => $divisionId,
        'name' => 'Test Section',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    $categoryId = workflowTestCategory();
    $channelId = workflowTestChannel();

    $director = workflowTestUser('Director', ['grievances.view', 'grievances.allocate', 'grievances.resolve']);
    $divisionDirector = workflowTestUser('Division Director', ['grievances.view', 'grievances.allocate'], [
        'division_id' => $divisionId,
    ]);
    $sectionManager = workflowTestUser('Section Manager', ['grievances.view', 'grievances.assign'], [
        'section_id' => $sectionId,
    ]);
    $helpdesk = workflowTestUser('Helpdesk Officer', ['grievances.view', 'grievances.resolve'], [
        'section_id' => $sectionId,
    ]);

    $steps = GrievanceWorkflowStep::query()->where('workflow_key', 'default')->orderBy('step_number')->get();
    $grievance = Grievance::query()->create([
        'reference_no' => 'GRM-TEST-000001',
        'grievance_category_id' => $categoryId,
        'channel_id' => $channelId,
        'description' => 'A test grievance needing review and approval.',
        'status' => 'submitted',
        'current_workflow_step_id' => $steps[0]->id,
        'registered_by' => $director->id,
    ]);

    $this->actingAs($director)
        ->post(route('grievances.workflow-action', $grievance), [
            'decision' => 'approve',
            'division_id' => $divisionId,
        ])->assertRedirect()->assertSessionHasNoErrors();
    Notification::assertSentTo($divisionDirector, GrievanceWorkflowActionRequired::class);

    $grievance->refresh();
    expect($grievance->currentWorkflowStep->action_type)->toBe('allocate_section')
        ->and($grievance->status)->toBe('allocated_division');

    $this->actingAs($divisionDirector)
        ->post(route('grievances.workflow-action', $grievance), [
            'decision' => 'approve',
            'section_id' => $sectionId,
        ])->assertRedirect()->assertSessionHasNoErrors();

    $grievance->refresh();
    expect($grievance->currentWorkflowStep->action_type)->toBe('assign_officer')
        ->and($grievance->status)->toBe('allocated_section');

    $unassignedSectionManager = workflowTestUser('Section Manager', ['grievances.view', 'grievances.assign']);
    $this->actingAs($unassignedSectionManager)
        ->post(route('grievances.workflow-action', $grievance), [
            'decision' => 'approve',
            'officer_id' => $helpdesk->id,
        ])->assertForbidden();

    $this->actingAs($sectionManager)
        ->post(route('grievances.workflow-action', $grievance), [
            'decision' => 'reject',
            'reason' => 'Please review the division assignment before proceeding.',
        ])->assertRedirect()->assertSessionHasNoErrors();

    $grievance->refresh();
    expect($grievance->currentWorkflowStep->action_type)->toBe('allocate_section')
        ->and($grievance->status)->toBe('allocated_division')
        ->and($grievance->workflowActions()->latest('id')->value('decision'))->toBe('reject');

    $this->actingAs($divisionDirector)
        ->post(route('grievances.workflow-action', $grievance), [
            'decision' => 'approve',
            'section_id' => $sectionId,
        ])->assertRedirect()->assertSessionHasNoErrors();

    $document = UploadedFile::fake()->create('investigation.pdf', 100, 'application/pdf');
    $this->actingAs($sectionManager)
        ->post(route('grievances.workflow-action', $grievance), [
            'decision' => 'approve',
            'officer_id' => $helpdesk->id,
            'documents' => [$document],
        ])->assertRedirect()->assertSessionHasNoErrors();

    $grievance->refresh();
    expect($grievance->currentWorkflowStep->action_type)->toBe('prepare_resolution')
        ->and($grievance->status)->toBe('assigned_officer');

    $workflowDocument = $grievance->workflowActions()->latest('id')->firstOrFail()->documents()->firstOrFail();
    Storage::disk('local')->assertExists($workflowDocument->path);
    $unauthorizedUser = User::factory()->create();
    $this->actingAs($unauthorizedUser)
        ->get(route('grievances.workflow-documents.download', $workflowDocument))
        ->assertForbidden();
    $this->actingAs($director)
        ->get(route('grievances.workflow-documents.download', $workflowDocument))
        ->assertOk()
        ->assertHeader('X-Content-Type-Options', 'nosniff');

    $otherHelpdesk = workflowTestUser('Helpdesk Officer', ['grievances.view', 'grievances.resolve'], [
        'section_id' => $sectionId,
    ]);
    $this->actingAs($otherHelpdesk)
        ->post(route('grievances.start-investigation', $grievance), ['remarks' => 'Attempted unauthorized start.'])
        ->assertForbidden();

    $this->actingAs($helpdesk)
        ->post(route('grievances.start-investigation', $grievance), ['remarks' => 'Investigation started.'])
        ->assertRedirect()->assertSessionHasNoErrors();

    $this->actingAs($helpdesk)
        ->post(route('grievances.workflow-action', $grievance), [
            'decision' => 'approve',
            'resolution_html' => '<p onclick="alert(1)">The repair will be completed.</p><script>alert(1)</script>',
        ])->assertRedirect()->assertSessionHasNoErrors();

    $grievance->refresh();
    $draft = $grievance->resolutions()->latest('id')->firstOrFail();
    expect($grievance->currentWorkflowStep->action_type)->toBe('final_approval')
        ->and($grievance->status)->toBe('pending_approval')
        ->and($draft->resolution_text)->toContain('The repair will be completed.')
        ->and($draft->resolution_text)->not->toContain('script')
        ->and($draft->resolution_text)->not->toContain('onclick');

    $this->actingAs($director)
        ->post(route('grievances.workflow-action', $grievance), ['decision' => 'approve'])
        ->assertRedirect()->assertSessionHasNoErrors();

    $grievance->refresh();
    expect($grievance->status)->toBe('resolved')
        ->and($grievance->current_workflow_step_id)->toBeNull()
        ->and($draft->fresh()->approved_by)->toBe($director->id)
        ->and($draft->fresh()->approved_at)->not->toBeNull();
});

it('seeds the nine-stage grievance workflow with informal and escalation routes', function () {
    $this->seed(GrievanceWorkflowSeeder::class);

    $steps = GrievanceWorkflowStep::query()
        ->where('workflow_key', 'default')
        ->orderBy('step_number')
        ->get();

    expect($steps)->toHaveCount(9)
        ->and($steps->pluck('action_type')->all())->toBe([
            'informal_resolution',
            'review',
            'screen_allocate',
            'acknowledge',
            'investigation',
            'decision_approval',
            'closure',
            'escalation',
            'monitoring',
        ])
        ->and($steps[0]->approval_target_step)->toBe(9)
        ->and($steps[0]->rejection_target_step)->toBe(2)
        ->and($steps[5]->rejection_target_step)->toBe(8)
        ->and($steps[6]->approval_target_step)->toBe(9)
        ->and($steps[6]->rejection_target_step)->toBe(5);
});

it('records informal resolution and routes resolved cases to per-case monitoring', function () {
    $this->withoutMiddleware(HandleInertiaRequests::class);
    Notification::fake();
    $this->seed(GrievanceWorkflowSeeder::class);

    $officer = workflowTestUser('RD Officer', ['grievances.view']);
    workflowTestUser('Executive', ['grievances.view']);
    $grievance = Grievance::query()->create([
        'reference_no' => 'GRM-INFORMAL-000001',
        'grievance_category_id' => workflowTestCategory(),
        'channel_id' => workflowTestChannel(),
        'description' => 'A grievance resolved during informal intake.',
        'status' => 'submitted',
        'current_workflow_step_id' => GrievanceWorkflowStep::query()
            ->where('workflow_key', 'default')
            ->where('step_number', 1)
            ->value('id'),
        'registered_by' => $officer->id,
    ]);

    $this->actingAs($officer)
        ->post(route('grievances.workflow-action', $grievance), [
            'decision' => 'approve',
            'reason' => 'The contractor repaired the damaged access point.',
            'workflow_data' => [
                'informal_outcome' => 'The complainant confirmed the repair.',
            ],
        ])
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    $grievance->refresh();
    $action = $grievance->workflowActions()->latest('id')->firstOrFail();
    expect($grievance->status)->toBe('resolved')
        ->and($grievance->currentWorkflowStep->step_number)->toBe(9)
        ->and($action->data['informal_outcome'])->toBe('The complainant confirmed the repair.');
});

it('routes an unaccepted closure back to investigation and an unapproved decision to escalation', function () {
    $this->withoutMiddleware(HandleInertiaRequests::class);
    Notification::fake();
    $this->seed(GrievanceWorkflowSeeder::class);

    $categoryId = workflowTestCategory();
    $channelId = workflowTestChannel();
    $investigator = workflowTestUser('Investigator', ['grievances.view', 'grievances.resolve']);
    $clo = workflowTestUser('Contractor CLO', ['grievances.view', 'grievances.close']);
    $grievance = Grievance::query()->create([
        'reference_no' => 'GRM-CLOSURE-000001',
        'grievance_category_id' => $categoryId,
        'channel_id' => $channelId,
        'description' => 'A grievance whose proposed remedy was not accepted.',
        'status' => 'resolved',
        'current_workflow_step_id' => GrievanceWorkflowStep::query()
            ->where('workflow_key', 'default')
            ->where('step_number', 7)
            ->value('id'),
        'assigned_officer_id' => $investigator->id,
        'registered_by' => $clo->id,
    ]);

    $this->actingAs($clo)
        ->post(route('grievances.workflow-action', $grievance), [
            'decision' => 'approve',
            'workflow_data' => [
                'acceptance' => '0',
                'satisfaction_rating' => '2',
            ],
        ])
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    $grievance->refresh();
    expect($grievance->status)->toBe('in_progress')
        ->and($grievance->currentWorkflowStep->step_number)->toBe(5);

    $chair = workflowTestUser('GRC Chair', ['grievances.view', 'grievances.resolve']);
    workflowTestUser('RD E&S Unit', ['grievances.view', 'grievances.escalate']);
    $grievance->update([
        'status' => 'pending_approval',
        'current_workflow_step_id' => GrievanceWorkflowStep::query()
            ->where('workflow_key', 'default')
            ->where('step_number', 6)
            ->value('id'),
    ]);
    $grievance->resolutions()->create([
        'proposed_by' => $investigator->id,
        'document_type' => 'report',
        'resolution_text' => '<p>Recommended corrective action.</p>',
    ]);

    $this->actingAs($chair)
        ->post(route('grievances.workflow-action', $grievance), [
            'decision' => 'reject',
            'reason' => 'The case remains unresolved and needs external review.',
        ])
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    expect($grievance->fresh()->status)->toBe('escalated')
        ->and($grievance->fresh()->currentWorkflowStep->step_number)->toBe(8);
});

it('runs a formally submitted case through screening, investigation, approval, closure and reporting', function () {
    $this->withoutMiddleware(HandleInertiaRequests::class);
    Notification::fake();
    $this->seed(GrievanceWorkflowSeeder::class);

    $divisionId = DB::table('divisions')->insertGetId([
        'ulid' => (string) Str::ulid(),
        'code' => 'FLOW-DIV',
        'name' => 'Workflow Division',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    $sectionId = DB::table('sections')->insertGetId([
        'ulid' => (string) Str::ulid(),
        'code' => 'FLOW-SEC',
        'division_id' => $divisionId,
        'name' => 'Workflow Section',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $rdOfficer = workflowTestUser('RD Officer', ['grievances.view']);
    $helpdesk = workflowTestUser('Helpdesk Officer', ['grievances.view']);
    $manager = workflowTestUser('Responsible Manager', ['grievances.view', 'grievances.allocate', 'grievances.assign'], [
        'division_id' => $divisionId,
    ]);
    $investigator = workflowTestUser('Investigator', ['grievances.view', 'grievances.resolve'], [
        'division_id' => $divisionId,
        'section_id' => $sectionId,
    ]);
    $chair = workflowTestUser('GRC Chair', ['grievances.view', 'grievances.resolve']);
    $clo = workflowTestUser('Contractor CLO', ['grievances.view', 'grievances.close']);
    $executive = workflowTestUser('Executive', ['grievances.view']);

    $grievance = Grievance::query()->create([
        'reference_no' => 'GRM-FULL-FLOW-000001',
        'grievance_category_id' => workflowTestCategory(),
        'channel_id' => workflowTestChannel(),
        'description' => 'A case used to verify all required default workflow transitions.',
        'status' => 'submitted',
        'current_workflow_step_id' => GrievanceWorkflowStep::query()
            ->where('workflow_key', 'default')
            ->where('step_number', 1)
            ->value('id'),
        'registered_by' => $rdOfficer->id,
    ]);

    $this->actingAs($rdOfficer)->post(route('grievances.workflow-action', $grievance), [
        'decision' => 'reject',
        'reason' => 'Informal resolution was not successful; proceed with the formal case.',
    ])->assertRedirect()->assertSessionHasNoErrors();

    $this->actingAs($helpdesk)->post(route('grievances.workflow-action', $grievance), [
        'decision' => 'approve',
        'reason' => 'Formal complaint registered.',
    ])->assertRedirect()->assertSessionHasNoErrors();

    $this->actingAs($manager)->post(route('grievances.workflow-action', $grievance), [
        'decision' => 'approve',
        'division_id' => $divisionId,
        'section_id' => $sectionId,
        'officer_id' => $investigator->id,
        'workflow_data' => [
            'severity' => 'high',
            'repeat_case' => '1',
            'requested_remedy' => 'Repair the damaged road shoulder.',
        ],
    ])->assertRedirect()->assertSessionHasNoErrors();

    $this->actingAs($investigator)->post(route('grievances.workflow-action', $grievance), [
        'decision' => 'acknowledge',
        'reason' => 'Receipt confirmed; investigation started.',
    ])->assertRedirect()->assertSessionHasNoErrors();

    $this->actingAs($investigator)->post(route('grievances.workflow-action', $grievance), [
        'decision' => 'approve',
        'resolution_html' => '<p>Repair the road shoulder and verify completion.</p>',
        'workflow_data' => [
            'corrective_action' => 'Repair and inspect the shoulder.',
            'action_owner' => 'Road maintenance contractor',
            'action_due_at' => now()->addWeek()->toDateString(),
        ],
    ])->assertRedirect()->assertSessionHasNoErrors();

    expect($grievance->fresh()->currentWorkflowStep->role_name)->toBe('GRC Chair')
        ->and($grievance->fresh()->resolutions()->whereNull('approved_at')->value('proposed_by'))
        ->toBe($investigator->id);

    $this->actingAs($chair)->post(route('grievances.workflow-action', $grievance), [
        'decision' => 'approve',
        'reason' => 'Finding and proposed remedy approved.',
    ])->assertRedirect()->assertSessionHasNoErrors();

    $this->actingAs($clo)->post(route('grievances.workflow-action', $grievance), [
        'decision' => 'approve',
        'reason' => 'The remedy was communicated and verified.',
        'workflow_data' => [
            'acceptance' => '1',
            'satisfaction_rating' => '5',
        ],
    ])->assertRedirect()->assertSessionHasNoErrors();

    $grievance->refresh();
    expect($grievance->currentWorkflowStep->step_number)->toBe(9)
        ->and($grievance->status)->toBe('resolved')
        ->and($grievance->priority)->toBe('high')
        ->and($grievance->is_previously_lodged)->toBeTrue()
        ->and($grievance->satisfaction_rating)->toBe(5)
        ->and($grievance->resolutions()->latest('id')->value('approved_by'))->toBe($chair->id)
        ->and($grievance->resolutions()->latest('id')->value('approved_at'))->not->toBeNull();

    $this->actingAs($executive)->post(route('grievances.workflow-action', $grievance), [
        'decision' => 'approve',
        'reason' => 'Case included in monthly performance reporting.',
        'workflow_data' => [
            'monitoring_notes' => 'Corrective action completed within the reporting period.',
        ],
    ])->assertRedirect()->assertSessionHasNoErrors();

    expect($grievance->fresh()->status)->toBe('closed')
        ->and($grievance->fresh()->current_workflow_step_id)->toBeNull()
        ->and($grievance->fresh()->closed_at)->not->toBeNull();
});

it('seeds the documented workflow roles with the permissions used by their steps', function () {
    $this->seed(PermissionSeeder::class);
    $this->seed(RoleSeeder::class);

    expect(Role::query()->whereIn('name', [
        'RD Officer',
        'Contractor CLO',
        'GRC Member',
        'Responsible Manager',
        'Investigator',
        'Engineer',
        'GRC Chair',
        'RD Manager',
        'Director General',
        'RD E&S Unit',
        'External Reviewer',
        'Executive',
    ])->count())->toBe(12)
        ->and(Role::findByName('Responsible Manager')->hasPermissionTo('grievances.allocate'))->toBeTrue()
        ->and(Role::findByName('Investigator')->hasPermissionTo('grievances.resolve'))->toBeTrue()
        ->and(Role::findByName('GRC Chair')->hasPermissionTo('grievances.resolve'))->toBeTrue()
        ->and(Role::findByName('RD Officer')->hasPermissionTo('grievances.view'))->toBeTrue()
        ->and(Role::findByName('RD Officer')->hasPermissionTo('grievances.close'))->toBeFalse()
        ->and(Role::findByName('Contractor CLO')->hasPermissionTo('grievances.close'))->toBeTrue()
        ->and(Role::findByName('RD E&S Unit')->hasPermissionTo('grievances.escalate'))->toBeTrue()
        ->and(Role::findByName('External Reviewer')->hasPermissionTo('grievances.escalate'))->toBeTrue();
});

it('does not allow final approval until the investigation step submits a resolution draft', function () {
    $this->withoutMiddleware(HandleInertiaRequests::class);
    $this->seed(GrievanceWorkflowSeeder::class);
    seedLegacyWorkflowStepsForExistingFlowTests();

    $director = workflowTestUser('Director', ['grievances.view', 'grievances.resolve']);
    $finalApprovalStep = GrievanceWorkflowStep::query()
        ->where('workflow_key', 'default')
        ->where('action_type', 'final_approval')
        ->firstOrFail();
    $grievance = Grievance::query()->create([
        'reference_no' => 'GRM-TEST-000002',
        'grievance_category_id' => workflowTestCategory(),
        'channel_id' => workflowTestChannel(),
        'description' => 'A test grievance without a resolution draft.',
        'status' => 'pending_approval',
        'current_workflow_step_id' => $finalApprovalStep->id,
        'registered_by' => $director->id,
    ]);

    $this->actingAs($director)
        ->post(route('grievances.workflow-action', $grievance), ['decision' => 'approve'])
        ->assertRedirect()
        ->assertSessionHasErrors('workflow');

    $this->actingAs($director)
        ->post(route('resolutions.store'), [
            'grievance_id' => $grievance->id,
            'document_type' => 'report',
            'resolution_text' => 'Unapproved direct entry.',
        ])->assertForbidden();

    expect($grievance->fresh()->status)->toBe('pending_approval')
        ->and($grievance->fresh()->workflowActions()->count())->toBe(0);

    $helpdesk = workflowTestUser('Helpdesk Officer', ['grievances.view', 'grievances.resolve']);
    $earlyStepGrievance = Grievance::query()->create([
        'reference_no' => 'GRM-TEST-000003',
        'grievance_category_id' => workflowTestCategory(),
        'channel_id' => workflowTestChannel(),
        'description' => 'A case assigned to an officer before the investigation step.',
        'status' => 'submitted',
        'current_workflow_step_id' => GrievanceWorkflowStep::query()
            ->where('workflow_key', 'default')
            ->where('step_number', 1)
            ->value('id'),
        'assigned_officer_id' => $helpdesk->id,
        'registered_by' => $director->id,
    ]);

    $this->actingAs($helpdesk)
        ->post(route('grievances.start-investigation', $earlyStepGrievance))
        ->assertRedirect()
        ->assertSessionHasErrors('workflow');

    expect($earlyStepGrievance->fresh()->status)->toBe('submitted')
        ->and($earlyStepGrievance->fresh()->workflowActions()->count())->toBe(0);
});

it('routes legacy resolution submissions through the configured workflow', function () {
    $this->withoutMiddleware(HandleInertiaRequests::class);
    Notification::fake();
    $this->seed(GrievanceWorkflowSeeder::class);
    seedLegacyWorkflowStepsForExistingFlowTests();

    workflowTestUser('Director', ['grievances.view', 'grievances.resolve']);
    $helpdesk = workflowTestUser('Helpdesk Officer', ['grievances.view', 'grievances.resolve']);
    $grievance = Grievance::query()->create([
        'reference_no' => 'GRM-TEST-000004',
        'grievance_category_id' => workflowTestCategory(),
        'channel_id' => workflowTestChannel(),
        'description' => 'A grievance with a resolution ready for submission.',
        'status' => 'in_progress',
        'current_workflow_step_id' => GrievanceWorkflowStep::query()
            ->where('workflow_key', 'default')
            ->where('action_type', 'prepare_resolution')
            ->value('id'),
        'assigned_officer_id' => $helpdesk->id,
        'registered_by' => $helpdesk->id,
    ]);

    $this->actingAs($helpdesk)
        ->post(route('resolutions.store'), [
            'grievance_id' => $grievance->id,
            'document_type' => 'memo',
            'resolution_text' => '<p>The matter has been addressed.</p><script>unsafe()</script>',
        ])
        ->assertRedirect(route('grievances.edit', $grievance))
        ->assertSessionHasNoErrors();

    $grievance->refresh();
    $draft = $grievance->resolutions()->latest('id')->firstOrFail();
    expect($grievance->status)->toBe('pending_approval')
        ->and($grievance->currentWorkflowStep->action_type)->toBe('final_approval')
        ->and($draft->document_type)->toBe('memo')
        ->and($draft->proposed_by)->toBe($helpdesk->id)
        ->and($draft->resolution_text)->not->toContain('script')
        ->and($grievance->workflowActions()->latest('id')->value('decision'))->toBe('approve');
});

it('lets the assigned officer acknowledge the case and upload a document to workflow history', function () {
    $this->withoutMiddleware(HandleInertiaRequests::class);
    Notification::fake();
    Storage::fake('local');
    $this->seed(GrievanceWorkflowSeeder::class);
    seedLegacyWorkflowStepsForExistingFlowTests();
    $helpdesk = workflowTestUser('Helpdesk Officer', ['grievances.view', 'grievances.resolve']);
    $step = GrievanceWorkflowStep::query()
        ->where('workflow_key', 'default')
        ->where('action_type', 'prepare_resolution')
        ->firstOrFail();
    $grievance = Grievance::query()->create([
        'reference_no' => 'GRM-ACK-000001',
        'grievance_category_id' => workflowTestCategory(),
        'channel_id' => workflowTestChannel(),
        'description' => 'A case assigned for investigation.',
        'status' => 'assigned_officer',
        'current_workflow_step_id' => $step->id,
        'assigned_officer_id' => $helpdesk->id,
        'registered_by' => $helpdesk->id,
    ]);

    $this->actingAs($helpdesk)
        ->post(route('grievances.workflow-action', $grievance), [
            'decision' => 'acknowledge',
            'reason' => 'I have received the case and started the investigation.',
            'documents' => [UploadedFile::fake()->create('initial-review.pdf', 100, 'application/pdf')],
        ])
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    $grievance->refresh();
    $action = $grievance->workflowActions()->latest('id')->firstOrFail();
    $document = $action->documents()->firstOrFail();

    expect($grievance->status)->toBe('in_progress')
        ->and($action->decision)->toBe('acknowledge')
        ->and($action->reason)->toBe('I have received the case and started the investigation.');
    Storage::disk('local')->assertExists($document->path);
});

it('shows admins complete lifecycle lists and restricts those lists to admins', function () {
    $this->withoutMiddleware(HandleInertiaRequests::class);
    $this->seed(GrievanceWorkflowSeeder::class);
    seedLegacyWorkflowStepsForExistingFlowTests();
    $admin = workflowTestUser('Admin', ['grievances.view']);
    $officer = workflowTestUser('Helpdesk Officer', ['grievances.view']);
    $categoryId = workflowTestCategory();
    $channelId = workflowTestChannel();

    foreach (['submitted', 'in_progress', 'resolved', 'closed', 'rejected'] as $index => $status) {
        Grievance::query()->create([
            'reference_no' => 'GRM-LIST-'.str_pad((string) $index, 6, '0', STR_PAD_LEFT),
            'grievance_category_id' => $categoryId,
            'channel_id' => $channelId,
            'description' => "A {$status} grievance.",
            'status' => $status,
            'registered_by' => $admin->id,
        ]);
    }

    $this->actingAs($admin)
        ->get('/grievances?list=pending')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('listType', 'pending')
            ->where('meta.total', 2));

    $this->actingAs($admin)
        ->get('/grievances?list=resolved')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('listType', 'resolved')
            ->where('meta.total', 2));

    $this->actingAs($admin)
        ->get('/grievances?list=rejected')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('listType', 'rejected')
            ->where('meta.total', 1));

    $this->actingAs($officer)->get('/grievances?list=pending')->assertForbidden();
});

it('shows directors only grievances they approved in the resolved list', function () {
    $this->withoutMiddleware(HandleInertiaRequests::class);
    $director = workflowTestUser('Director', ['grievances.view']);
    $otherDirector = workflowTestUser('Other Director', ['grievances.view']);
    workflowTestUser('Helpdesk Officer', ['grievances.view']);
    $approvedByDirector = Grievance::query()->create([
        'reference_no' => 'GRM-DIRECTOR-RESOLVED-1',
        'grievance_category_id' => workflowTestCategory(),
        'channel_id' => workflowTestChannel(),
        'description' => 'Resolved by the current director.',
        'status' => 'resolved',
        'registered_by' => $director->id,
    ]);
    $approvedByOtherDirector = Grievance::query()->create([
        'reference_no' => 'GRM-DIRECTOR-RESOLVED-2',
        'grievance_category_id' => workflowTestCategory(),
        'channel_id' => workflowTestChannel(),
        'description' => 'Resolved by another director.',
        'status' => 'resolved',
        'registered_by' => $otherDirector->id,
    ]);

    $approvedByDirector->resolutions()->create([
        'proposed_by' => $director->id,
        'approved_by' => $director->id,
        'resolution_text' => 'Approved by current director.',
        'approved_at' => now(),
    ]);
    $approvedByOtherDirector->resolutions()->create([
        'proposed_by' => $otherDirector->id,
        'approved_by' => $otherDirector->id,
        'resolution_text' => 'Approved by another director.',
        'approved_at' => now(),
    ]);

    $this->actingAs($director)
        ->get('/grievances?list=resolved')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('listType', 'resolved')
            ->where('meta.total', 1)
            ->where('data.0.reference_no', 'GRM-DIRECTOR-RESOLVED-1'));

    $this->actingAs($otherDirector)
        ->get('/grievances?list=rejected')
        ->assertForbidden();
});

it('includes address and coordinates in grievance details', function () {
    $grievance = Grievance::query()->create([
        'reference_no' => 'GRM-LOCATION-0001',
        'grievance_category_id' => workflowTestCategory(),
        'channel_id' => workflowTestChannel(),
        'description' => 'A grievance with location details.',
        'location_description' => '12 Main Street',
        'latitude' => -29.31,
        'longitude' => 27.48,
        'status' => 'submitted',
        'registered_by' => workflowTestUser('Helpdesk Officer', ['grievances.view'])->id,
    ]);

    $details = (new GrievanceResource($grievance))->resolve(request());

    expect($details['location_description'])->toBe('12 Main Street')
        ->and((float) $details['latitude'])->toBe(-29.31)
        ->and((float) $details['longitude'])->toBe(27.48);
});

it('shows workflow cases forwarded by the signed-in officer after they leave the officer queue', function () {
    $this->withoutMiddleware(HandleInertiaRequests::class);
    $this->seed(GrievanceWorkflowSeeder::class);
    seedLegacyWorkflowStepsForExistingFlowTests();
    $director = workflowTestUser('Director', ['grievances.view', 'grievances.allocate']);
    workflowTestUser('Helpdesk Officer', ['grievances.view']);
    $divisionId = DB::table('divisions')->insertGetId([
        'ulid' => (string) Str::ulid(),
        'code' => 'FWD-DIV',
        'name' => 'Forwarded Division',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    $otherDivisionId = DB::table('divisions')->insertGetId([
        'ulid' => (string) Str::ulid(),
        'code' => 'OTHER-DIV',
        'name' => 'Other Division',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    $divisionDirector = workflowTestUser('Division Director', ['grievances.view'], [
        'division_id' => $divisionId,
    ]);
    $directorStep = GrievanceWorkflowStep::query()
        ->where('workflow_key', 'default')
        ->where('role_name', 'Director')
        ->firstOrFail();
    $divisionStep = GrievanceWorkflowStep::query()
        ->where('workflow_key', 'default')
        ->where('role_name', 'Division Director')
        ->firstOrFail();

    $forwarded = Grievance::query()->create([
        'reference_no' => 'GRM-FWD-000001',
        'grievance_category_id' => workflowTestCategory(),
        'channel_id' => workflowTestChannel(),
        'description' => 'A grievance forwarded to a division.',
        'status' => 'allocated_division',
        'current_workflow_step_id' => $divisionStep->id,
        'division_id' => $divisionId,
        'registered_by' => $director->id,
    ]);
    $forwarded->workflowActions()->create([
        'from_step_id' => $directorStep->id,
        'to_step_id' => $divisionStep->id,
        'actor_id' => $director->id,
        'decision' => 'approve',
        'reason' => 'Forwarded for division review.',
    ]);

    Grievance::query()->create([
        'reference_no' => 'GRM-FWD-000002',
        'grievance_category_id' => workflowTestCategory(),
        'channel_id' => workflowTestChannel(),
        'description' => 'A grievance waiting in the division queue.',
        'status' => 'allocated_division',
        'current_workflow_step_id' => $divisionStep->id,
        'division_id' => $divisionId,
        'registered_by' => $director->id,
    ]);

    $this->actingAs($director)
        ->get('/grievances?list=forwarded')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('listType', 'forwarded')
            ->where('meta.total', 1)
            ->where('data.0.reference_no', 'GRM-FWD-000001')
            ->where('data.0.current_workflow_step.pending_with', 'Forwarded Division'));

    $this->actingAs($divisionDirector)
        ->get('/grievances?list=forwarded')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('listType', 'forwarded')
            ->where('meta.total', 0));
});

it('offers unassigned helpdesk officers for a section and accepts the section manager assignment', function () {
    $this->withoutMiddleware(HandleInertiaRequests::class);
    $this->seed(GrievanceWorkflowSeeder::class);
    seedLegacyWorkflowStepsForExistingFlowTests();

    $divisionId = DB::table('divisions')->insertGetId([
        'ulid' => (string) Str::ulid(),
        'code' => 'OFFICER-DIV',
        'name' => 'Officer Division',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    $sectionId = DB::table('sections')->insertGetId([
        'ulid' => (string) Str::ulid(),
        'code' => 'OFFICER-SEC',
        'division_id' => $divisionId,
        'name' => 'Officer Section',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    $otherSectionId = DB::table('sections')->insertGetId([
        'ulid' => (string) Str::ulid(),
        'code' => 'OTHER-OFFICER-SEC',
        'division_id' => $divisionId,
        'name' => 'Other Officer Section',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    $manager = workflowTestUser('Section Manager', ['grievances.view', 'grievances.assign'], [
        'division_id' => $divisionId,
        'section_id' => $sectionId,
    ]);
    $unassignedOfficer = workflowTestUser('Helpdesk Officer', ['grievances.view'], [
        'name' => 'Unassigned Helpdesk Officer',
        'section_id' => null,
    ]);
    workflowTestUser('Helpdesk Officer', ['grievances.view'], [
        'name' => 'Other Section Helpdesk Officer',
        'section_id' => $otherSectionId,
    ]);
    $step = GrievanceWorkflowStep::query()
        ->where('workflow_key', 'default')
        ->where('action_type', 'assign_officer')
        ->firstOrFail();
    $grievance = Grievance::query()->create([
        'reference_no' => 'GRM-OFFICER-000001',
        'grievance_category_id' => workflowTestCategory(),
        'channel_id' => workflowTestChannel(),
        'description' => 'A grievance awaiting an investigating officer.',
        'status' => 'allocated_section',
        'current_workflow_step_id' => $step->id,
        'division_id' => $divisionId,
        'section_id' => $sectionId,
        'registered_by' => $manager->id,
    ]);

    $this->actingAs($manager)
        ->get(route('grievances.edit', $grievance))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('officers.0.id', $unassignedOfficer->id)
            ->where('officers.0.name', $unassignedOfficer->name)
            ->missing('officers.1'));

    $this->actingAs($manager)
        ->post(route('grievances.workflow-action', $grievance), [
            'decision' => 'approve',
            'officer_id' => $unassignedOfficer->id,
        ])
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    expect($grievance->fresh()->assigned_officer_id)->toBe($unassignedOfficer->id);
});

it('allows only grievance registrants and admins with delete permission to soft delete cases', function () {
    $this->withoutMiddleware(HandleInertiaRequests::class);
    $registrant = workflowTestUser('Helpdesk Officer', ['grievances.view', 'grievances.delete']);
    $assignedOfficer = workflowTestUser('Content Editor', ['grievances.view', 'grievances.delete']);
    $admin = workflowTestUser('Admin', ['grievances.view', 'grievances.delete']);
    $attributes = [
        'grievance_category_id' => workflowTestCategory(),
        'channel_id' => workflowTestChannel(),
        'description' => 'A case with restricted deletion.',
        'status' => 'submitted',
    ];
    $owned = Grievance::query()->create([
        ...$attributes,
        'reference_no' => 'GRM-DELETE-OWNER',
        'registered_by' => $registrant->id,
    ]);
    $notOwned = Grievance::query()->create([
        ...$attributes,
        'reference_no' => 'GRM-DELETE-OTHER',
        'registered_by' => $registrant->id,
    ]);
    $adminCase = Grievance::query()->create([
        ...$attributes,
        'reference_no' => 'GRM-DELETE-ADMIN',
        'registered_by' => $registrant->id,
    ]);

    $this->actingAs($assignedOfficer)
        ->delete(route('grievances.destroy', $notOwned))
        ->assertForbidden();

    $this->actingAs($registrant)
        ->delete(route('grievances.destroy', $owned))
        ->assertRedirect();
    expect(Grievance::withTrashed()->find($owned->id)?->trashed())->toBeTrue();

    $this->actingAs($admin)
        ->delete(route('grievances.destroy', $adminCase))
        ->assertRedirect();
    expect(Grievance::withTrashed()->find($adminCase->id)?->trashed())->toBeTrue();
});

it('rejects bulk deletion when a selected grievance was not registered by the user', function () {
    $this->withoutMiddleware(HandleInertiaRequests::class);
    $registrant = workflowTestUser('Helpdesk Officer', ['grievances.view', 'grievances.delete']);
    $otherOfficer = workflowTestUser('Content Editor', ['grievances.view', 'grievances.delete']);
    $grievance = Grievance::query()->create([
        'reference_no' => 'GRM-BULK-DELETE',
        'grievance_category_id' => workflowTestCategory(),
        'channel_id' => workflowTestChannel(),
        'description' => 'A case not registered by this user.',
        'status' => 'submitted',
        'registered_by' => $registrant->id,
    ]);

    $this->actingAs($otherOfficer)
        ->delete(route('grievances.bulk-destroy'), ['ids' => [$grievance->id]])
        ->assertForbidden();

    expect($grievance->fresh())->not->toBeNull();
});

it('denies admin edit access and reports missing workflow migrations gracefully', function () {
    $this->withoutMiddleware(HandleInertiaRequests::class);
    $admin = workflowTestUser('Admin', ['grievances.view', 'grievances.edit']);
    $categoryId = workflowTestCategory();
    $channelId = workflowTestChannel();
    $grievance = Grievance::query()->create([
        'reference_no' => 'GRM-ADMIN-EDIT-0001',
        'grievance_category_id' => $categoryId,
        'channel_id' => $channelId,
        'description' => 'Read-only grievance for administrators.',
        'status' => 'submitted',
        'registered_by' => $admin->id,
    ]);

    $this->actingAs($admin)
        ->get(route('grievances.edit', $grievance))
        ->assertForbidden();

    $this->actingAs($admin)
        ->put(route('grievances.update', $grievance), ['description' => 'Attempted update.'])
        ->assertForbidden();

    $officer = workflowTestUser('Helpdesk Officer', ['grievances.view', 'grievances.edit']);
    Schema::dropIfExists('grievance_workflow_documents');
    Schema::dropIfExists('grievance_workflow_actions');

    $this->actingAs($officer)
        ->get(route('grievances.edit', $grievance))
        ->assertRedirect(route('grievances.index'))
        ->assertSessionHas('error', 'Grievance workflow database migrations are pending. Run php artisan migrate before opening this grievance.');
});

function workflowTestUser(string $roleName, array $permissions, array $attributes = []): User
{
    $role = Role::query()->firstOrCreate(
        ['name' => $roleName, 'guard_name' => 'web'],
        [
            'name_st' => $roleName,
            'code' => strtoupper(Str::slug($roleName, '_')),
            'status' => true,
        ],
    );
    $user = User::factory()->create($attributes);

    foreach ($permissions as $permissionName) {
        $permission = Permission::findOrCreate($permissionName, 'web');
        $role->givePermissionTo($permission);
    }

    $user->assignRole($role);

    return $user;
}

function workflowTestCategory(): int
{
    $suffix = strtoupper(Str::random(8));

    return DB::table('grievance_categories')->insertGetId([
        'ulid' => (string) Str::ulid(),
        'code' => "WFTEST-{$suffix}",
        'name_en' => 'Workflow test',
        'name_st' => 'Teko ya mosebetsi',
        'slug' => 'workflow-test-'.strtolower($suffix),
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

function workflowTestChannel(): int
{
    return DB::table('grievance_channels')->insertGetId([
        'ulid' => (string) Str::ulid(),
        'code' => 'wf-'.Str::lower(Str::random(8)),
        'name' => 'Helpdesk',
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

function seedLegacyWorkflowStepsForExistingFlowTests(): void
{
    $steps = [
        [1, 'Initial review and allocation', 'allocate_division', 'Director', 'reject', null, false],
        [2, 'Division review', 'allocate_section', 'Division Director', 'return_to_step', 1, false],
        [3, 'Section review', 'assign_officer', 'Section Manager', 'return_to_step', 2, false],
        [4, 'Investigation and proposed resolution', 'prepare_resolution', 'Helpdesk Officer', 'return_to_step', 3, false],
        [5, 'Final approval', 'final_approval', 'Director', 'return_to_step', 4, true],
    ];

    foreach ($steps as [$number, $name, $actionType, $roleName, $rejectionAction, $rejectionTarget, $isFinal]) {
        DB::table('grievance_workflow_steps')->updateOrInsert(
            ['workflow_key' => 'default', 'step_number' => $number],
            [
                'name' => $name,
                'description' => $name,
                'action_type' => $actionType,
                'role_name' => $roleName,
                'approver_user_id' => null,
                'approval_action' => $isFinal ? 'resolve' : 'advance',
                'approval_target_step' => null,
                'rejection_action' => $rejectionAction,
                'rejection_target_step' => $rejectionTarget,
                'is_final_approval' => $isFinal,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        );
    }
}
