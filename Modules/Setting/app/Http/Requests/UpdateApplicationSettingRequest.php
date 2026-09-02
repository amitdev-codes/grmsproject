<?php

namespace Modules\Setting\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateApplicationSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manageApplicationSettings') ?? false;
    }

    public function rules(): array
    {
        return [
            // Identity
            'project_name' => ['required', 'string', 'max:255'],
            'project_slug' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('application_settings', 'project_slug')->ignore($this->route('setting')?->id)],
            'short_name' => ['nullable', 'string', 'max:50'],
            'tagline' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],

            // Branding
            'logo' => ['nullable', 'image', 'max:2048'],
            'favicon' => ['nullable', 'image', 'max:512'],
            'primary_color' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'secondary_color' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],

            // Location
            'address_line' => ['nullable', 'string', 'max:255'],
            'district_id' => ['nullable', 'integer', 'exists:districts,id'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],

            // Contact
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'whatsapp' => ['nullable', 'string', 'max:30'],
            'social_links' => ['nullable', 'array'],
            'social_links.facebook' => ['nullable', 'url'],
            'social_links.linkedin' => ['nullable', 'url'],
            'social_links.youtube' => ['nullable', 'url'],
            'social_links.twitter' => ['nullable', 'url'],
            'social_links.instagram' => ['nullable', 'url'],

            // SEO
            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string', 'max:500'],
            'seo_keywords' => ['nullable', 'string', 'max:255'],
            'og_image' => ['nullable', 'image', 'max:2048'],
            'seo_meta' => ['nullable', 'array'],
            'seo_meta.robots' => ['nullable', 'string', 'max:100'],
            'seo_meta.twitter_card' => ['nullable', 'string', 'max:100'],
            'seo_meta.canonical_url' => ['nullable', 'url'],

            // GRMS config
            'sla_level1_hours' => ['required', 'integer', 'min:1'],
            'sla_level2_days' => ['required', 'integer', 'min:1'],
            'sla_level3_days' => ['required', 'integer', 'min:1'],
            'default_locale' => ['required', Rule::in(['en', 'st'])],
            'support_hours' => ['nullable', 'string', 'max:255'],
            'anonymous_submissions_enabled' => ['nullable', 'boolean'],
            'maintenance_mode' => ['nullable', 'boolean'],

            // Legal / footer
            'footer_text' => ['nullable', 'string'],
            'privacy_policy_url' => ['nullable', 'url'],
            'terms_url' => ['nullable', 'url'],

            // Escape hatch — free-form, deliberately unvalidated beyond being an array
            'extra' => ['nullable', 'array'],
        ];
    }
}
