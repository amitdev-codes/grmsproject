<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('grievance_workflow_steps')) {
            Schema::create('grievance_workflow_steps', function (Blueprint $table) {
                $table->id();
                $table->string('workflow_key', 100)->default('default');
                $table->unsignedInteger('step_number');
                $table->string('name');
                $table->text('description')->nullable();
                $table->string('role_name')->nullable();
                $table->foreignId('approver_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('approval_action', 20)->default('advance');
                $table->string('rejection_action', 20)->default('reject');
                $table->unsignedInteger('rejection_target_step')->nullable();
                $table->boolean('is_final_approval')->default(false);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasIndex('grievance_workflow_steps', ['workflow_key', 'step_number'], 'unique')) {
            Schema::table('grievance_workflow_steps', function (Blueprint $table) {
                $table->unique(
                    ['workflow_key', 'step_number'],
                    'grievance_wf_workflow_step_unq',
                );
            });
        }

        if (! Schema::hasIndex('grievance_workflow_steps', ['workflow_key', 'is_active', 'step_number'])) {
            Schema::table('grievance_workflow_steps', function (Blueprint $table) {
                $table->index(
                    ['workflow_key', 'is_active', 'step_number'],
                    'grievance_wf_active_step_idx',
                );
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('grievance_workflow_steps');
    }
};
