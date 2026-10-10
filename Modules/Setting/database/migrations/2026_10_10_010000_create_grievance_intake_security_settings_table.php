<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grievance_intake_security_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('lodging_requests_per_minute')->default(5);
            $table->unsignedInteger('duplicate_window_hours')->default(24);
            $table->json('whitelisted_ip_addresses')->nullable();
            $table->json('blacklisted_ip_addresses')->nullable();
            $table->string('captcha_provider', 30)->default('local');
            $table->string('cloudflare_site_key')->nullable();
            $table->text('cloudflare_secret_key')->nullable();
            $table->timestamps();
        });

        Schema::table('grievances', function (Blueprint $table) {
            $table->string('public_duplicate_fingerprint', 64)->nullable();
            $table->index('public_duplicate_fingerprint', 'grievance_dup_fingerprint_idx');
        });
    }

    public function down(): void
    {
        Schema::table('grievances', function (Blueprint $table) {
            $table->dropIndex('grievance_dup_fingerprint_idx');
            $table->dropColumn('public_duplicate_fingerprint');
        });

        Schema::dropIfExists('grievance_intake_security_settings');
    }
};
