<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('grievances', function (Blueprint $table) {
            $table->foreignId('project_id')->nullable()->after('district_id')->constrained()->nullOnDelete();
            $table->foreignId('source_grievance_id')->nullable()->after('project_id')->constrained('grievances')->nullOnDelete();
            $table->boolean('is_previously_lodged')->default(false)->after('is_anonymous');
            $table->boolean('is_previously_finalized')->default(false)->after('is_previously_lodged');
            $table->decimal('latitude', 10, 7)->nullable()->after('location_description');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            $table->unsignedInteger('location_accuracy_meters')->nullable()->after('longitude');
            $table->string('preferred_language', 5)->default('en')->after('priority');
            $table->foreignId('grievance_sla_policy_id')->nullable()->after('sla_due_at')->constrained()->nullOnDelete();
            $table->timestamp('first_response_due_at')->nullable()->after('grievance_sla_policy_id');
            $table->timestamp('resolution_due_at')->nullable()->after('first_response_due_at');
            $table->unsignedTinyInteger('satisfaction_rating')->nullable()->after('resolution_due_at');
            $table->foreignId('closed_by')->nullable()->after('satisfaction_rating')->constrained('users')->nullOnDelete();
            $table->text('closed_reason')->nullable()->after('closed_by');
            $table->timestamp('closed_at')->nullable()->after('closed_reason');
            $table->timestamp('tracking_access_expires_at')->nullable()->after('closed_at');
            $table->timestamp('tracking_access_revoked_at')->nullable()->after('tracking_access_expires_at');

            $table->index(['project_id', 'status']);
            $table->index(['source_grievance_id']);
        });
    }

    public function down(): void
    {
        Schema::table('grievances', function (Blueprint $table) {
            $table->dropIndex(['project_id', 'status']);
            $table->dropIndex(['source_grievance_id']);
            $table->dropConstrainedForeignId('closed_by');
            $table->dropConstrainedForeignId('grievance_sla_policy_id');
            $table->dropConstrainedForeignId('source_grievance_id');
            $table->dropConstrainedForeignId('project_id');
            $table->dropColumn([
                'is_previously_lodged', 'is_previously_finalized', 'latitude', 'longitude',
                'location_accuracy_meters', 'preferred_language', 'first_response_due_at',
                'resolution_due_at', 'satisfaction_rating', 'closed_reason', 'closed_at',
                'tracking_access_expires_at', 'tracking_access_revoked_at',
            ]);
        });
    }
};
