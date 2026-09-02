<?php

namespace Modules\Setting\Models;
use Illuminate\Database\Eloquent\Model;

class ApplicationSetting extends Model
{
    protected $fillable = [
        // Identity
        'project_name', 'project_slug', 'short_name', 'tagline', 'description',
        // Branding
        'logo_path', 'favicon_path', 'primary_color', 'secondary_color',
        // Location
        'address_line', 'district_id', 'latitude', 'longitude',
        // Contact
        'email', 'phone', 'whatsapp', 'social_links',
        // SEO
        'seo_title', 'seo_description', 'seo_keywords', 'og_image_path', 'seo_meta',
        // GRMS config
        'sla_level1_hours', 'sla_level2_days', 'sla_level3_days',
        'default_locale', 'support_hours', 'anonymous_submissions_enabled', 'maintenance_mode',
        // Legal / footer
        'footer_text', 'privacy_policy_url', 'terms_url',
        // Escape hatch
        'extra',
    ];
    protected $guarded = ['id'];
    protected $casts = [
        'social_links' => 'array',
        'seo_meta' => 'array',
        'extra' => 'array',
        'anonymous_submissions_enabled' => 'boolean',
        'maintenance_mode' => 'boolean',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    public static function current(): self
    {
        // firstOrCreate guarantees exactly one row ever exists, and callers
        // never have to null-check "what if settings were never configured".
        return static::query()->firstOrCreate(
            [],
            ['project_name' => config('app.name'), 'project_slug' => str(config('app.name'))->slug()],
        );
    }
}
