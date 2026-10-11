<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('voice_grievances', function (Blueprint $table): void {
            $table->id();
            $table->string('reference_no')->unique();
            $table->string('audio_path');
            $table->string('audio_mime');
            $table->unsignedInteger('duration_seconds')->nullable();
            $table->string('language_selected')->nullable();
            $table->string('language_detected')->nullable();
            $table->text('transcript_original')->nullable();
            $table->text('transcript_english')->nullable();
            $table->string('contact_phone', 30)->nullable();
            $table->string('status')->default('received')->index();
            $table->foreignId('assigned_officer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('grievance_id')->nullable()->constrained('grievances')->nullOnDelete();
            $table->text('failure_reason')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('voice_grievances');
    }
};
