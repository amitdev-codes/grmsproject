<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Reference data that is administered centrally and reused by GRMS workflows.
     */
    public function up(): void
    {
        Schema::create('project_types', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name');
            $table->string('name_st')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('service_providers', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name');
            $table->string('provider_type', 20); // contractor | consultant | other
            $table->string('contact_name')->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('email')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['provider_type', 'is_active']);
        });

        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('code', 40)->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->foreignId('project_type_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('district_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('division_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('consultant_id')->nullable()->constrained('service_providers')->nullOnDelete();
            $table->foreignId('contractor_id')->nullable()->constrained('service_providers')->nullOnDelete();
            $table->decimal('contract_amount', 15, 2)->nullable();
            $table->string('currency', 3)->default('LSL');
            $table->date('starts_on')->nullable();
            $table->date('expected_completion_on')->nullable();
            $table->date('completed_on')->nullable();
            $table->string('status', 20)->default('planned'); // planned | current | completed | archived
            $table->json('metadata')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->index(['status', 'district_id']);
        });

        Schema::create('grievance_sla_policies', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name');
            $table->string('priority', 10)->nullable();
            $table->unsignedSmallInteger('acknowledgement_hours')->default(48);
            $table->unsignedSmallInteger('resolution_hours');
            $table->boolean('use_business_hours')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('grievance_escalation_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grievance_sla_policy_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedTinyInteger('escalation_level');
            $table->unsignedSmallInteger('breach_after_hours');
            $table->unsignedSmallInteger('extension_hours')->nullable();
            $table->string('target_role', 50); // zonal_officer | regional_head | director_roads
            $table->boolean('requires_manual_review')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['grievance_sla_policy_id', 'escalation_level'], 'sla_escalation_level_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grievance_escalation_rules');
        Schema::dropIfExists('grievance_sla_policies');
        Schema::dropIfExists('projects');
        Schema::dropIfExists('service_providers');
        Schema::dropIfExists('project_types');
    }
};
