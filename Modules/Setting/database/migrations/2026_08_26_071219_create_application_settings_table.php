<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('application_settings', function (Blueprint $table) {
            $table->id();

            // --- Identity ---
            $table->string('project_name');
            $table->string('project_slug')->unique(); // e.g. "grms-lesotho" — used in URLs/exports/generated file names
            $table->string('short_name')->nullable(); // e.g. "GRMS" — for tight UI spots (navbar, favicon alt text)
            $table->text('tagline')->nullable();
            $table->text('description')->nullable(); // used as SEO meta description fallback + About text

            // --- Branding ---
            $table->string('logo_path')->nullable(); // or use Spatie Media if you prefer media collections here too
            $table->string('favicon_path')->nullable();
            $table->string('primary_color', 7)->nullable();  // hex, e.g. #1D4ED8 — theming later
            $table->string('secondary_color', 7)->nullable();

            // --- Location (single HQ / registration office) ---
            $table->string('address_line')->nullable();
            $table->foreignId('district_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            // --- Contact channels (grouped — these grow/change together, don't warrant separate columns each) ---
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('whatsapp')->nullable();
            $table->json('social_links')->nullable();
            // { "facebook": "...", "linkedin": "...", "youtube": "...", "twitter": "...", "instagram": "..." }
            // JSON here (not per-platform columns) so adding TikTok/Threads/etc later needs no migration.

            // --- SEO ---
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->string('seo_keywords')->nullable();
            $table->string('og_image_path')->nullable();
            $table->json('seo_meta')->nullable();
            // catch-all for extra/less-common meta tags (robots, canonical override, twitter:card, etc.)
            // without needing a migration for every new tag you decide to support.

            // --- GRMS-specific operational config ---
            $table->unsignedTinyInteger('sla_level1_hours')->default(48);  // matches your escalation levels
            $table->unsignedTinyInteger('sla_level2_days')->default(5);
            $table->unsignedTinyInteger('sla_level3_days')->default(7);
            $table->string('default_locale', 5)->default('en'); // en | st — site-wide default, distinct from a user's personal locale
            $table->string('support_hours')->nullable(); // free text, e.g. "Mon–Fri, 8am–4:30pm"
            $table->boolean('anonymous_submissions_enabled')->default(true);
            $table->boolean('maintenance_mode')->default(false);

            // --- Legal / footer ---
            $table->text('footer_text')->nullable();
            $table->string('privacy_policy_url')->nullable();
            $table->string('terms_url')->nullable();

            // --- Extensibility escape hatch ---
            $table->json('extra')->nullable();
            // anything that doesn't fit yet — avoids another migration for every one-off field
            // you think of after this ships. Treat as genuinely miscellaneous, not a dumping
            // ground for things that deserve their own column once they stabilize.

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('application_settings');
    }
};
