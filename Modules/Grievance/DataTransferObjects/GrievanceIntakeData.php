<?php

namespace Modules\Grievance\DataTransferObjects;

use Illuminate\Http\UploadedFile;

/**
 * Carries everything needed to register a grievance, regardless of where it
 * came from. Every controller/handler builds one of these and hands it to
 * GrievanceRegistrationService::submit() — nothing else creates a
 * Grievance row.
 *
 * `channel` is a plain string matching the `code` column on
 * grievance_channels (web, mobile_app, sms, ussd, whatsapp, helpdesk, clo,
 * rd_office, box, grc, community_forum, chief, social_media, ...). It is
 * intentionally NOT a PHP enum — that table is seeded/config-driven data,
 * not a fixed set baked into code.
 */
final class GrievanceIntakeData
{
    /** @param UploadedFile[] $attachments */
    public function __construct(
        public readonly string $channel,
        public readonly string $description,
        public readonly ?int $categoryId = null,       // null => let the AI classifier pick one
        public readonly ?int $districtId = null,
        public readonly ?int $divisionId = null,        // null => auto-routed from category/district
        public readonly ?int $sectionId = null,
        public readonly ?string $contactPhone = null,
        public readonly ?string $contactEmail = null,
        public readonly ?string $contactName = null,
        public readonly bool $isAnonymous = false,
        public readonly ?int $registeredBy = null,       // staff user id, when a person keyed it in
        public readonly ?string $actorRole = null,       // override; usually left null (see resolveActorRole())
        public readonly ?string $locationDescription = null,
        public readonly ?float $latitude = null,
        public readonly ?float $longitude = null,
        public readonly ?float $locationAccuracyMeters = null,
        public readonly ?int $projectId = null,
        public readonly ?string $sourceGrievanceReference = null,
        public readonly bool $isPreviouslyLodged = false,
        public readonly bool $isPreviouslyFinalized = false,
        public readonly ?array $metadata = null,
        public readonly ?array $rawPayload = null,
        public readonly array $attachments = [],
        public readonly string $preferredLanguage = 'en',
    ) {}

    /** Who to record as the actor in status history / audit log. */
    public function resolveActorRole(): string
    {
        return $this->actorRole ?? ($this->registeredBy ? 'helpdesk_officer' : 'self_service');
    }

    /** Public web app — grievances/add (StorePublicGrievanceRequest::safe()). */
    public static function fromPublicWebRequest(array $v, array $attachments): self
    {
        return new self(
            channel: 'web',
            description: $v['description'],
            categoryId: $v['category_id'] ?? null,
            districtId: $v['district_id'] ?? null,
            divisionId: $v['division_id'] ?? null,
            contactPhone: ($v['is_anonymous'] ?? false) ? null : ($v['contact_phone'] ?? null),
            contactEmail: ($v['is_anonymous'] ?? false) ? null : ($v['contact_email'] ?? null),
            contactName: ($v['is_anonymous'] ?? false) ? null : ($v['contact_name'] ?? null),
            isAnonymous: (bool) ($v['is_anonymous'] ?? false),
            locationDescription: $v['location_description'] ?? null,
            latitude: $v['latitude'] ?? null,
            longitude: $v['longitude'] ?? null,
            locationAccuracyMeters: $v['location_accuracy_meters'] ?? null,
            projectId: $v['project_id'] ?? null,
            sourceGrievanceReference: $v['source_grievance_reference'] ?? null,
            isPreviouslyLodged: (bool) ($v['is_previously_lodged'] ?? false),
            isPreviouslyFinalized: (bool) ($v['is_previously_finalized'] ?? false),
            metadata: $v['metadata'] ?? null,
            attachments: $attachments,
            preferredLanguage: $v['preferred_language'] ?? 'en',
        );
    }

    /** Mobile app submission. */
    public static function fromMobileApi(array $v, array $attachments = []): self
    {
        return new self(
            channel: 'mobile_app',
            description: $v['description'],
            categoryId: $v['category_id'] ?? null,
            districtId: $v['district_id'] ?? null,
            contactPhone: $v['contact_phone'] ?? null,
            contactEmail: $v['contact_email'] ?? null,
            contactName: $v['contact_name'] ?? null,
            isAnonymous: (bool) ($v['is_anonymous'] ?? false),
            latitude: $v['latitude'] ?? null,
            longitude: $v['longitude'] ?? null,
            attachments: $attachments,
        );
    }

    /**
     * SMS — category/district are only known when the citizen used the
     * "GRV <catcode> <distcode> <text>" format; otherwise pass them as null
     * and the AI classifier decides the category inside submit().
     */
    public static function fromSms(string $description, string $phone, array $rawPayload, ?int $categoryId = null, ?int $districtId = null): self
    {
        return new self(
            channel: 'sms',
            description: $description,
            categoryId: $categoryId,
            districtId: $districtId,
            contactPhone: $phone,
            rawPayload: $rawPayload,
        );
    }

    /** WhatsApp bot — same shape as SMS. */
    public static function fromWhatsapp(string $description, string $phone, array $rawPayload, ?int $categoryId = null, ?int $districtId = null): self
    {
        return new self(
            channel: 'whatsapp',
            description: $description,
            categoryId: $categoryId,
            districtId: $districtId,
            contactPhone: $phone,
            rawPayload: $rawPayload,
        );
    }

    public static function fromUssd(int $categoryId, int $districtId, string $description, string $phone, array $rawPayload): self
    {
        return new self(
            channel: 'ussd',
            description: $description,
            categoryId: $categoryId,
            districtId: $districtId,
            contactPhone: $phone,
            rawPayload: $rawPayload,
        );
    }

    /**
     * One factory for every staff-mediated / physically-collected channel:
     * helpdesk officer, Community Liaison Officer, RD office walk-in,
     * grievance box, Grievance Redress Committee, multi-stakeholder forum,
     * chief/councillor, or social media a staffer noticed and logged.
     * Pass the grievance_channels.code that applies — no new method needed
     * per channel.
     */
    public static function fromStaffChannel(string $channelCode, array $v, int $registeredBy, array $attachments = []): self
    {
        return new self(
            channel: $channelCode,
            description: $v['description'],
            categoryId: $v['category_id'] ?? null,
            districtId: $v['district_id'] ?? null,
            divisionId: $v['division_id'] ?? null,
            sectionId: $v['section_id'] ?? null,
            contactPhone: $v['contact_phone'] ?? null,
            contactEmail: $v['contact_email'] ?? null,
            contactName: $v['contact_name'] ?? null,
            isAnonymous: (bool) ($v['is_anonymous'] ?? false),
            registeredBy: $registeredBy,
            locationDescription: $v['location_description'] ?? null,
            metadata: $v['metadata'] ?? null,
            attachments: $attachments,
        );
    }
}
