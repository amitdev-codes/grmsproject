<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('grievance_workflow_steps', function (Blueprint $table): void {
            $table->unsignedInteger('approval_target_step')->nullable()->after('approval_action');
        });

        Schema::table('grievance_workflow_actions', function (Blueprint $table): void {
            $table->json('data')->nullable()->after('reason');
        });
    }

    public function down(): void
    {
        Schema::table('grievance_workflow_actions', function (Blueprint $table): void {
            $table->dropColumn('data');
        });

        Schema::table('grievance_workflow_steps', function (Blueprint $table): void {
            $table->dropColumn('approval_target_step');
        });
    }
};
