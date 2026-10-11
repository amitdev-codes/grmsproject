<?php

namespace Modules\Setting\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ApplicationSetting extends Model
{
    protected $fillable = [
        // Identity
        'project_name', 'project_slug', 'short_name', 'tagline', 'description',
        // Branding
        'logo_path', 'favicon_path', 'primary_color', 'secondary_color', 'theme',
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

    protected $appends = ['logo_url', 'favicon_url', 'og_image_url'];

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
            ['id' => 1],
            ['project_name' => config('app.name'), 'project_slug' => str(config('app.name'))->slug()],
        );
    }

    public function getLogoUrlAttribute(): ?string
    {
        return $this->assetUrl($this->logo_path);
    }

    public function getFaviconUrlAttribute(): ?string
    {
        return $this->assetUrl($this->favicon_path);
    }

    public function getOgImageUrlAttribute(): ?string
    {
        return $this->assetUrl($this->og_image_path);
    }

    private function assetUrl(?string $path): ?string
    {
        return $path ? Storage::disk('public')->url($path) : null;
    }
}
