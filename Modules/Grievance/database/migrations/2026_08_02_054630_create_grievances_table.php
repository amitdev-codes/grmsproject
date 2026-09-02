<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grievances', function (Blueprint $table) {
            $table->id();
            $table->string('reference_no', 20)->unique();

            $table->foreignId('grievance_category_id')->constrained()->restrictOnDelete();
            $table->foreignId('channel_id')->constrained('grievance_channels')->restrictOnDelete();
            $table->foreignId('district_id')->nullable()->constrained()->nullOnDelete();
            $table->text('location_description')->nullable();
            // routing columns exist now so the next phase (movement) doesn't need
            // another migration; they stay null until that module is built
            $table->foreignId('division_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('section_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('assigned_officer_id')->nullable()->constrained('users')->nullOnDelete();

            $table->foreignId('complainant_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('complainant_name')->nullable();
            $table->string('complainant_phone', 20)->nullable();
            $table->string('complainant_email')->nullable();
            $table->boolean('is_anonymous')->default(false);

            $table->text('description');
            $table->json('metadata')->nullable();
            $table->json('raw_payload')->nullable(); // original USSD/SMS payload, for support/debugging
            $table->string('ussd_session_id')->nullable();

            $table->string('status', 30)->default('submitted');
            $table->string('priority', 10)->default('normal');

            $table->foreignId('registered_by')->nullable()->constrained('users')->nullOnDelete(); // null = self-service
            $table->timestamp('acknowledged_at')->nullable();
            $table->timestamp('sla_due_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['status']);
            $table->index(['complainant_phone']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grievances');
    }
};
