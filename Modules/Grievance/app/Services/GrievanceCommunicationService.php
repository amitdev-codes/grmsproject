<?php

namespace Modules\Grievance\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Modules\Grievance\Jobs\SendGrievanceCommunicationJob;
use Modules\Grievance\Mail\GrievanceStatusUpdated;
use Modules\Grievance\Models\Grievance;
use Modules\Setting\Models\EmailSetting;
use Modules\Setting\Models\SmsSetting;

class GrievanceCommunicationService
{
    public function queueStatusUpdate(
        Grievance $grievance,
        ?string $fromStatus,
        string $toStatus,
        ?string $reason = null,
    ): void {
        if (! config('grievance.notifications.enabled', true)
            || $grievance->is_anonymous
            || (! $grievance->complainant_email && ! $grievance->complainant_phone)
            || ! $this->statusIsEnabled($toStatus)) {
            return;
        }

        $message = $this->messageFor($grievance, $fromStatus, $toStatus, $reason);
        $templateData = [
            'reference_no' => $grievance->reference_no,
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'reason' => $reason,
            'tracking_url' => url('/grievances/track'),
        ];

        foreach ($this->channelsFor($grievance) as $channel => $recipient) {
            $communicationId = DB::table('grievance_communications')->insertGetId([
                'grievance_id' => $grievance->id,
                'message_type' => $toStatus === Grievance::STATUS_SUBMITTED ? 'acknowledgement' : 'status_update',
                'channel' => $channel,
                'recipient' => $recipient,
                'body' => $message,
                'template_data' => json_encode($templateData, JSON_THROW_ON_ERROR),
                'delivery_status' => 'queued',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            SendGrievanceCommunicationJob::dispatch($communicationId)->afterCommit();
        }
    }

    public function deliver(int $communicationId): void
    {
        $communication = DB::table('grievance_communications')->where('id', $communicationId)->first();

        if (! $communication || $communication->delivery_status === 'sent') {
            return;
        }

        try {
            $data = json_decode($communication->template_data ?: '{}', true, 512, JSON_THROW_ON_ERROR);
            $data['contact_name'] = Grievance::find($communication->grievance_id)?->complainant_name;

            if ($communication->channel === 'email') {
                $this->deliverEmail($communication->recipient, $data);
            } elseif ($communication->channel === 'sms') {
                $this->deliverSms($communication->recipient, $communication->body);
            } else {
                throw new \RuntimeException("Unsupported grievance communication channel [{$communication->channel}].");
            }

            DB::table('grievance_communications')->where('id', $communicationId)->update([
                'delivery_status' => 'sent',
                'sent_at' => now(),
                'failure_reason' => null,
                'updated_at' => now(),
            ]);
        } catch (\Throwable $exception) {
            DB::table('grievance_communications')->where('id', $communicationId)->update([
                'delivery_status' => 'failed',
                'failed_at' => now(),
                'failure_reason' => $exception->getMessage(),
                'updated_at' => now(),
            ]);

            throw $exception;
        }
    }

    protected function deliverEmail(string $recipient, array $data): void
    {
        $setting = EmailSetting::query()->where('is_active', true)->latest()->first();

        if ($setting) {
            $mailer = Mail::build([
                'transport' => $setting->mailer,
                // Symfony Mailer expects "smtps" for implicit TLS. Mailtrap
                // uses STARTTLS on smtp/2525, so the legacy "tls" setting
                // must not be passed as a transport scheme.
                'scheme' => $setting->encryption === 'ssl'
                    ? 'smtps'
                    : null,
                'host' => $setting->host,
                'port' => $setting->port,
                'username' => $setting->username,
                'password' => $setting->password,
                'from' => [
                    'address' => $setting->from_address,
                    'name' => $setting->from_name,
                ],
            ]);
        } else {
            $mailer = Mail::mailer();
        }

        $mailer->to($recipient)->send(new GrievanceStatusUpdated(
            $data,
            $setting?->from_address,
            $setting?->from_name,
        ));
    }

    protected function deliverSms(string $recipient, string $body): void
    {
        $setting = SmsSetting::query()->where('is_active', true)->latest()->first();

        if (! $setting || ! $setting->base_url) {
            throw new \RuntimeException('No active SMS provider with an API base URL is configured.');
        }

        $request = Http::acceptJson()->timeout(15);
        if ($setting->api_key) {
            $request = $request->withToken($setting->api_key);
        }
        if ($setting->api_secret) {
            $request = $request->withHeader('X-API-Secret', $setting->api_secret);
        }

        $request->post($setting->base_url, [
            'to' => $recipient,
            'from' => $setting->sender_id,
            'message' => $body,
        ])->throw();
    }

    /**
     * @return array<string, string>
     */
    protected function channelsFor(Grievance $grievance): array
    {
        $channels = [];

        if ($grievance->complainant_email) {
            $channels['email'] = $grievance->complainant_email;
        }

        if ($grievance->complainant_phone) {
            $channels['sms'] = $grievance->complainant_phone;
        }

        return $channels;
    }

    protected function statusIsEnabled(string $status): bool
    {
        $statuses = config('grievance.notifications.statuses', []);

        return ($statuses[$status] ?? true) === true;
    }

    protected function messageFor(
        Grievance $grievance,
        ?string $fromStatus,
        string $toStatus,
        ?string $reason,
    ): string {
        $message = "Grievance {$grievance->reference_no} status: "
            .str_replace('_', ' ', $toStatus).'.';

        if ($fromStatus && $fromStatus !== $toStatus) {
            $message = "Grievance {$grievance->reference_no} moved from "
                .str_replace('_', ' ', $fromStatus).' to '.str_replace('_', ' ', $toStatus).'.';
        }

        if ($reason) {
            $message .= " Note: {$reason}";
        }

        return $message.' Track it using the grievance reference number.';
    }
}
