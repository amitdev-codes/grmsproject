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
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

it('routes each grievance through configured approval levels, rejection returns, draft and final approval', function () {
    $this->withoutMiddleware(HandleInertiaRequests::class);
    Notification::fake();
    Queue::fake();
    Storage::fake('local');
    $this->seed(GrievanceWorkflowSeeder::class);

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

it('does not allow final approval until the investigation step submits a resolution draft', function () {
    $this->withoutMiddleware(HandleInertiaRequests::class);
    $this->seed(GrievanceWorkflowSeeder::class);

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
