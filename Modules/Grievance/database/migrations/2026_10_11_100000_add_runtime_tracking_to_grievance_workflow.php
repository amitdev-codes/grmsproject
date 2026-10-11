<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('grievance_workflow_steps', function (Blueprint $table): void {
            $table->string('action_type', 40)->default('review')->after('description');
        });

        DB::table('grievance_workflow_steps')->where('workflow_key', 'default')->get()->each(
            function (object $step): void {
                $actionType = match ($step->role_name) {
                    'Director' => $step->is_final_approval ? 'final_approval' : 'allocate_division',
                    'Division Director' => 'allocate_section',
                    'Section Manager' => 'assign_officer',
                    'Helpdesk Officer' => 'prepare_resolution',
                    default => 'review',
                };

                DB::table('grievance_workflow_steps')
                    ->where('id', $step->id)
                    ->update(['action_type' => $actionType]);
            },
        );

        $stepByAction = DB::table('grievance_workflow_steps')
            ->where('workflow_key', 'default')
            ->where('is_active', true)
            ->pluck('id', 'action_type');

        Schema::table('grievances', function (Blueprint $table): void {
            $table->foreignId('current_workflow_step_id')
                ->nullable()
                ->after('status')
                ->constrained('grievance_workflow_steps')
                ->nullOnDelete();
        });

        foreach ([
            'submitted' => 'allocate_division',
            'reallocation_required' => 'allocate_division',
            'allocated_division' => 'allocate_section',
            'allocated_section' => 'assign_officer',
            'assigned_officer' => 'prepare_resolution',
            'assigned' => 'prepare_resolution',
            'in_progress' => 'prepare_resolution',
            'escalated' => 'prepare_resolution',
            'pending_approval' => 'final_approval',
        ] as $status => $actionType) {
            $stepId = $stepByAction[$actionType] ?? null;

            if ($stepId) {
                DB::table('grievances')
                    ->where('status', $status)
                    ->whereNull('current_workflow_step_id')
                    ->update(['current_workflow_step_id' => $stepId]);
            }
        }

        Schema::create('grievance_workflow_actions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('grievance_id')->constrained()->cascadeOnDelete();
            $table->foreignId('from_step_id')->nullable()->constrained('grievance_workflow_steps')->nullOnDelete();
            $table->foreignId('to_step_id')->nullable()->constrained('grievance_workflow_steps')->nullOnDelete();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('decision', 20);
            $table->text('reason')->nullable();
            $table->timestamps();
            $table->index(['grievance_id', 'created_at']);
        });

        Schema::create('grievance_workflow_documents', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('grievance_workflow_action_id')->constrained()->cascadeOnDelete();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('disk', 50)->default('local');
            $table->string('path');
            $table->string('original_name');
            $table->string('mime_type', 150);
            $table->unsignedBigInteger('size');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grievance_workflow_documents');
        Schema::dropIfExists('grievance_workflow_actions');

        Schema::table('grievances', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('current_workflow_step_id');
        });

        Schema::table('grievance_workflow_steps', function (Blueprint $table): void {
            $table->dropColumn('action_type');
        });
    }
};
