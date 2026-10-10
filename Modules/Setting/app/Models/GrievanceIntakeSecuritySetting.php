<?php

namespace Modules\Setting\Models;

use Illuminate\Database\Eloquent\Model;

class GrievanceIntakeSecuritySetting extends Model
{
    protected $table = 'grievance_intake_security_settings';

    protected $fillable = [
        'lodging_requests_per_minute',
        'duplicate_window_hours',
        'whitelisted_ip_addresses',
        'blacklisted_ip_addresses',
        'captcha_provider',
        'cloudflare_site_key',
        'cloudflare_secret_key',
    ];

    protected function casts(): array
    {
        return [
            'lodging_requests_per_minute' => 'integer',
            'duplicate_window_hours' => 'integer',
            'whitelisted_ip_addresses' => 'array',
            'blacklisted_ip_addresses' => 'array',
            'cloudflare_secret_key' => 'encrypted',
        ];
    }

    public static function current(): self
    {
        return self::query()->firstOrCreate(['id' => 1]);
    }
}
