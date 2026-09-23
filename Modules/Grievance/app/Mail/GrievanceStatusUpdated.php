<?php

namespace Modules\Grievance\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Modules\Setting\Models\ApplicationSetting;

class GrievanceStatusUpdated extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly array $data,
        public readonly ?string $fromAddress = null,
        public readonly ?string $fromName = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "GRMS grievance {$this->data['reference_no']} update",
            from: $this->fromAddress
                ? new Address($this->fromAddress, $this->fromName ?? config('app.name'))
                : null,
        );
    }

    public function content(): Content
    {
        $settings = ApplicationSetting::current();
        $configuredLogo = $settings->logo_path
            ? storage_path('app/public/'.$settings->logo_path)
            : null;
        $logoPath = $configuredLogo && is_file($configuredLogo)
            ? $configuredLogo
            : public_path('logo.png');

        return new Content(
            view: 'grievance::emails.status-updated',
            with: [
                'data' => $this->data,
                'settings' => $settings,
                'logoPath' => is_file($logoPath) ? $logoPath : null,
            ],
        );
    }
}
