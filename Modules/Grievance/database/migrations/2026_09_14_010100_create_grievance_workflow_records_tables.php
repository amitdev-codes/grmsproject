<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grievance_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grievance_id')->constrained()->cascadeOnDelete();
            $table->string('action', 20); // allocated | assigned | reassigned | rejected
            $table->foreignId('from_division_id')->nullable()->constrained('divisions')->nullOnDelete();
            $table->foreignId('to_division_id')->nullable()->constrained('divisions')->nullOnDelete();
            $table->foreignId('from_section_id')->nullable()->constrained('sections')->nullOnDelete();
            $table->foreignId('to_section_id')->nullable()->constrained('sections')->nullOnDelete();
            $table->foreignId('from_officer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('to_officer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('acted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('reason')->nullable();
            $table->timestamps();
            $table->index(['grievance_id', 'created_at']);
        });

        Schema::create('grievance_delay_notices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grievance_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('reason');
            $table->timestamp('previous_due_at')->nullable();
            $table->timestamp('revised_due_at');
            $table->timestamp('communicated_at')->nullable();
            $table->timestamps();
        });

        Schema::create('resolution_approval_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resolution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('decision', 10); // approved | rejected
            $table->text('reason')->nullable();
            $table->timestamp('decided_at')->useCurrent();
        });

        Schema::create('resolution_sign_offs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resolution_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('signer_name')->nullable();
            $table->string('signer_contact', 100)->nullable();
            $table->string('method', 20); // e_signature | uploaded_form
            $table->string('signature_hash', 128)->nullable();
            $table->timestamp('signed_at');
            $table->timestamp('tracking_link_revoked_at')->nullable();
            $table->timestamps();
        });

        Schema::create('grievance_communications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grievance_id')->constrained()->cascadeOnDelete();
            $table->foreignId('initiated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('message_type', 30); // acknowledgement | delay | resolution | status_update
            $table->string('channel', 20); // sms | email | whatsapp | in_app
            $table->string('recipient', 255)->nullable();
            $table->text('body');
            $table->json('template_data')->nullable();
            $table->string('provider_message_id')->nullable();
            $table->string('delivery_status', 20)->default('queued');
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->text('failure_reason')->nullable();
            $table->timestamps();
            $table->index(['grievance_id', 'message_type']);
            $table->index(['delivery_status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grievance_communications');
        Schema::dropIfExists('resolution_sign_offs');
        Schema::dropIfExists('resolution_approval_actions');
        Schema::dropIfExists('grievance_delay_notices');
        Schema::dropIfExists('grievance_assignments');
    }
};
