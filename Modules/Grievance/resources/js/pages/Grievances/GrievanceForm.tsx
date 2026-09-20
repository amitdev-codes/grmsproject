import { FileDropzone } from '@/components/file-dropzone'; // adjust path if it lives elsewhere in your tree
import { Select2Field, StatusField, TextField } from '@/components/form-fields';
import { PhoneField } from '@/components/form-fields/phone-field';
import { FormLayout } from '@/components/form-layout';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { useTranslation } from '@/hooks/use-translation';
import { rules, validateForm } from '@/lib/validation';
import { statusLabel, statusVariant } from '@/types/grievance-status';
import { router, useForm, usePage } from '@inertiajs/react';
import { FileWarning, LocateFixed, Loader2 } from 'lucide-react';
import { useState } from 'react';
import { route } from 'ziggy-js';
import type { Grievance } from './columns';

interface LookupOption {
    id: number;
    name: string;
}
interface CategoryOption {
    id: number;
    name_en: string;
    name_st: string;
    division_id: number | null;
    is_sensitive: boolean;
}
interface ChannelOption {
    id: number;
    code: string;
    name: string;
}
interface DistrictOption {
    id: number;
    name: string;
}
interface SectionOption {
    id: number;
    name: string;
    division_id: number;
}
interface OfficerOption {
    id: number;
    name: string;
}

interface GrievanceFormProps {
    grievance: Grievance | null;
    categories: CategoryOption[];
    channels: ChannelOption[];
    districts: DistrictOption[];
    divisions: LookupOption[];
    sections: SectionOption[];
    officers?: OfficerOption[];
}

interface FormValues {
    grievance_category_id: string;
    channel_id: string;
    district_id: string;
    division_id: string;
    section_id: string;
    priority: 'low' | 'normal' | 'high';
    is_anonymous: '1' | '0';
    complainant_name: string;
    complainant_phone: string;
    complainant_email: string;
    description: string;
    remarks: string;
    location_description: string;
    latitude: string;
    longitude: string;
    attachments: File[];
    remove_attachment_ids: number[];
    officer_id: string;
}

export default function Form({
    grievance,
    categories,
    channels,
    districts,
    divisions,
    sections,
    officers = [],
}: GrievanceFormProps) {
    const isEdit = !!grievance;
    const { t } = useTranslation();
    const page = usePage<{
        auth: {
            user?: {
                role_names?: string;
                division_id?: number | null;
                section_id?: number | null;
            };
        };
    }>();
    const user = page.props.auth.user;
    const roles =
        page.props.auth.user?.role_names
            ?.split(',')
            .map((role) => role.trim()) ?? [];
    const canSave =
        !isEdit ||
        roles.some((role) =>
            ['Super Admin', 'IT Admin', 'Admin', 'Developer'].includes(role),
        );
    const canOperateWorkflow =
        !!grievance &&
        ((roles.includes('Director') &&
            ['submitted', 'reallocation_required'].includes(grievance.status) &&
            !grievance.division) ||
            (roles.includes('Division Director') &&
                grievance.status === 'allocated_division' &&
                grievance.division?.id === user?.division_id) ||
            (roles.includes('Section Manager') &&
                grievance.status === 'allocated_section' &&
                grievance.section?.id === user?.section_id));
    const [locating, setLocating] = useState(false);
    const [locError, setLocError] = useState<string | null>(null);

    const {
        data,
        setData,
        post,
        transform,
        processing,
        errors,
        setError,
        clearErrors,
    } = useForm<FormValues>({
        grievance_category_id: grievance?.category?.id
            ? String(grievance.category.id)
            : '',
        channel_id: grievance?.channel?.id ? String(grievance.channel.id) : '',
        district_id: grievance?.district?.id
            ? String(grievance.district.id)
            : '',
        division_id: grievance?.division?.id
            ? String(grievance.division.id)
            : '',
        section_id: grievance?.section?.id ? String(grievance.section.id) : '',
        priority: grievance?.priority ?? 'normal',
        is_anonymous: grievance?.is_anonymous ? '1' : '0',
        complainant_name: grievance?.complainant_name ?? '',
        complainant_phone: grievance?.complainant_phone ?? '',
        complainant_email: grievance?.complainant_email ?? '',
        description: grievance?.description ?? '',
        remarks: grievance?.remarks ?? '',
        location_description: '',
        latitude: '',
        longitude: '',
        attachments: [],
        remove_attachment_ids: [],
        officer_id: '',
    });

    const isAnonymous = data.is_anonymous === '1';

    const workflowAction = (action: 'forward' | 'reject') => {
        if (action === 'reject') {
            if (!data.remarks.trim()) {
                setError('remarks', 'Rejection remarks are required.');

                return;
            }

            const endpoint =
                roles.includes('Section Manager') &&
                grievance?.status === 'allocated_section'
                    ? 'grievances.reject-allocation'
                    : 'grievances.reject';
            router.post(
                route(endpoint, grievance!.id),
                { reason: data.remarks },
                { preserveScroll: true },
            );

            return;
        }

        let endpoint: string | null = null;
        let payload: Record<string, string> = {};

        if (roles.includes('Director') || roles.includes('Super Admin')) {
            endpoint = 'grievances.allocate-division';
            payload = { division_id: data.division_id };
        } else if (roles.includes('Division Director')) {
            endpoint = 'grievances.allocate-section';
            payload = { section_id: data.section_id };
        } else if (roles.includes('Section Manager')) {
            endpoint = 'grievances.assign-officer';
            payload = { officer_id: data.officer_id };
        }

        if (!endpoint || Object.values(payload).some((value) => !value)) {
            setError(
                'remarks',
                'Select the next workflow destination before forwarding.',
            );

            return;
        }

        router.post(route(endpoint, grievance!.id), payload, {
            preserveScroll: true,
        });
    };

    const resolve = () => {
        if (!data.remarks.trim()) {
            setError('remarks', 'Resolution remarks are required.');

            return;
        }

        router.post(
            route('grievances.resolve', grievance!.id),
            { reason: data.remarks },
            { preserveScroll: true },
        );
    };

    const submit = (e: React.SyntheticEvent) => {
        e.preventDefault();

        const clientErrors = validateForm(data, {
            grievance_category_id: [rules.required()],
            channel_id: [rules.required()],
            district_id: [rules.required()],
            description: [rules.required()],
            ...(isAnonymous
                ? {}
                : {
                      complainant_name: [rules.required()],
                  }),
        });

        if (Object.keys(clientErrors).length > 0) {
            setError(clientErrors as Record<keyof FormValues, string>);

            return;
        }

        clearErrors();

        const url = isEdit
            ? route('grievances.update', grievance!.id)
            : route('grievances.store');

        transform((formData) => ({
            ...formData,
            division_id: formData.division_id || null,
            section_id: formData.section_id || null,
            complainant_name: isAnonymous ? null : formData.complainant_name,
            complainant_phone: isAnonymous ? null : formData.complainant_phone,
            complainant_email: isAnonymous ? null : formData.complainant_email,
            // No latitude/longitude columns exist on `grievances` yet — packed
            // into `metadata` for now. Confirm with backend whether this JSON
            // shape is what the controller expects, or add real columns.
            metadata:
                formData.latitude || formData.longitude
                    ? {
                          latitude: formData.latitude || null,
                          longitude: formData.longitude || null,
                      }
                    : null,
            latitude: undefined,
            longitude: undefined,
            ...(isEdit ? { _method: 'put' } : {}),
        }));

        post(url, { preserveScroll: true, forceFormData: true });
    };

    const categoryOptions = categories.map((c) => ({
        value: String(c.id),
        label: c.is_sensitive ? `${c.name_en} (Sensitive)` : c.name_en,
    }));
    const channelOptions = channels.map((c) => ({
        value: String(c.id),
        label: c.name,
    }));
    const districtOptions = districts.map((d) => ({
        value: String(d.id),
        label: d.name,
    }));
    const divisionOptions = divisions.map((d) => ({
        value: String(d.id),
        label: d.name,
    }));
    // Sections cascade from the selected division
    const sectionOptions = sections
        .filter(
            (s) =>
                !data.division_id || s.division_id === Number(data.division_id),
        )
        .map((s) => ({ value: String(s.id), label: s.name }));

    const handleCategoryChange = (value: string) => {
        setData('grievance_category_id', value);

        // Auto-suggest the category's default division, but only if the
        // officer hasn't already picked one themselves.
        if (!data.division_id) {
            const category = categories.find((c) => String(c.id) === value);

            if (category?.division_id) {
                setData('division_id', String(category.division_id));
            }
        }
    };

    /** Auto-capture coordinates from the browser's Geolocation API. */
    const captureLocation = () => {
        if (!('geolocation' in navigator)) {
            setLocError(t('Geolocation is not supported by your browser.'));

            return;
        }

        setLocating(true);
        setLocError(null);

        navigator.geolocation.getCurrentPosition(
            (pos) => {
                setData('latitude', pos.coords.latitude.toFixed(6));
                setData('longitude', pos.coords.longitude.toFixed(6));
                setLocating(false);
            },
            (err) => {
                const msg =
                    err.code === err.PERMISSION_DENIED
                        ? t(
                              'Location permission denied. Please enable it in your browser settings.',
                          )
                        : err.code === err.TIMEOUT
                          ? t('Location request timed out. Please try again.')
                          : t('Unable to retrieve location. Please try again.');
                setLocError(msg);
                setLocating(false);
            },
            { enableHighAccuracy: true, timeout: 10000, maximumAge: 60000 },
        );
    };

    return (
        <FormLayout
            title={isEdit ? t('Edit Grievance') : t('Register Grievance')}
            description={
                isEdit
                    ? t('Update case :ref.', {
                          ref: grievance!.reference_no,
                      })
                    : t('Register a new grievance on behalf of a complainant.')
            }
            breadcrumbs={[
                {
                    label: t('Grievances'),
                    icon: FileWarning,
                    href: route('grievances.index'),
                },
                {
                    label: isEdit
                        ? t('Edit Grievance')
                        : t('Register Grievance'),
                },
            ]}
            onSubmit={submit}
            processing={processing}
            submitLabel={
                canSave
                    ? isEdit
                        ? t('Save changes')
                        : t('Register Grievance')
                    : undefined
            }
            hideFooter={isEdit && !canSave}
        >
            {isEdit && (
                <div className="flex items-center gap-3 rounded-md border bg-muted/40 px-3 py-2 text-sm">
                    <span className="font-mono text-muted-foreground">
                        {grievance!.reference_no}
                    </span>
                    <Badge variant={statusVariant(grievance!.status)}>
                        {statusLabel(grievance!.status)}
                    </Badge>
                    <span className="ml-auto text-xs text-muted-foreground">
                        {t(
                            'Status changes happen from the case detail page, not here.',
                        )}
                    </span>
                </div>
            )}

            <StatusField
                id="is_anonymous"
                label={t('Anonymous Submission')}
                value={data.is_anonymous}
                onChange={(v) => setData('is_anonymous', v)}
                error={errors.is_anonymous}
                activeLabel={t('Anonymous')}
                inactiveLabel={t('Named')}
            />

            {!isAnonymous && (
                <>
                    <TextField
                        id="complainant_name"
                        required
                        label={t('Complainant Name')}
                        value={data.complainant_name}
                        onChange={(v) => setData('complainant_name', v)}
                        error={errors.complainant_name}
                    />
                    <PhoneField
                        id="complainant_phone"
                        label={t('Phone')}
                        value={data.complainant_phone}
                        onChange={(v) => setData('complainant_phone', v)}
                        error={errors.complainant_phone}
                    />
                    <TextField
                        id="complainant_email"
                        label={t('Email')}
                        value={data.complainant_email}
                        onChange={(v) => setData('complainant_email', v)}
                        error={errors.complainant_email}
                    />
                </>
            )}

            <Select2Field
                id="grievance_category_id"
                required
                label={t('Category')}
                value={data.grievance_category_id}
                onChange={handleCategoryChange}
                options={categoryOptions}
                placeholder={t('Select a category')}
                error={errors.grievance_category_id}
            />

            <Select2Field
                id="channel_id"
                required
                label={t('Intake Channel')}
                value={data.channel_id}
                onChange={(v) => setData('channel_id', v)}
                options={channelOptions}
                placeholder={t('How did this complaint reach you?')}
                error={errors.channel_id}
            />

            <Select2Field
                id="district_id"
                required
                label={t('District')}
                value={data.district_id}
                onChange={(v) => setData('district_id', v)}
                options={districtOptions}
                placeholder={t('Select a district')}
                error={errors.district_id}
            />

            <Select2Field
                id="division_id"
                label={t('Division')}
                value={data.division_id}
                onChange={(v) => {
                    setData('division_id', v);
                    setData('section_id', ''); // clear section when division changes, since options just shifted
                }}
                options={divisionOptions}
                placeholder={t('Select a division')}
                error={errors.division_id}
            />

            <Select2Field
                id="section_id"
                label={t('Section')}
                value={data.section_id}
                onChange={(v) => setData('section_id', v)}
                options={sectionOptions}
                placeholder={
                    data.division_id
                        ? t('Select a section')
                        : t('Select a division first')
                }
                error={errors.section_id}
            />

            {isEdit && roles.some((role) => role === 'Section Manager') && (
                <Select2Field
                    id="officer_id"
                    label={t('Investigating Officer')}
                    value={data.officer_id}
                    onChange={(value) => setData('officer_id', value)}
                    options={officers.map((officer) => ({
                        value: String(officer.id),
                        label: officer.name,
                    }))}
                    placeholder={t('Select an officer before forwarding')}
                    error={errors.officer_id}
                />
            )}

            <Select2Field
                id="priority"
                required
                label={t('Priority')}
                value={data.priority}
                onChange={(v) =>
                    setData('priority', v as FormValues['priority'])
                }
                options={[
                    { value: 'low', label: t('Low') },
                    { value: 'normal', label: t('Normal') },
                    { value: 'high', label: t('High') },
                ]}
                error={errors.priority}
            />

            <div className="space-y-1.5">
                <label htmlFor="description" className="text-sm font-medium">
                    {t('Description')}{' '}
                    <span className="text-destructive">*</span>
                </label>
                <textarea
                    id="description"
                    rows={5}
                    className="w-full rounded-md border bg-background px-3 py-2 text-sm shadow-sm focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                    value={data.description}
                    onChange={(e) => setData('description', e.target.value)}
                    placeholder={t('Describe the grievance in detail…')}
                />
                {errors.description && (
                    <p className="text-sm text-destructive">
                        {errors.description}
                    </p>
                )}
            </div>

            <div className="space-y-1.5">
                <label htmlFor="remarks" className="text-sm font-medium">
                    {t('Officer Remarks')}
                </label>
                <textarea
                    id="remarks"
                    rows={4}
                    className="w-full rounded-md border bg-background px-3 py-2 text-sm shadow-sm focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                    value={data.remarks}
                    onChange={(e) => setData('remarks', e.target.value)}
                    placeholder={t(
                        'Add investigation notes or handover remarks…',
                    )}
                />
                {errors.remarks && (
                    <p className="text-sm text-destructive">{errors.remarks}</p>
                )}
            </div>

            <TextField
                id="location_description"
                label={t('Location')}
                value={data.location_description}
                onChange={(v) => setData('location_description', v)}
                error={errors.location_description}
                placeholder={t('e.g. Mafeteng access road, near the bridge')}
            />

            <div className="grid grid-cols-2 gap-3">
                <TextField
                    id="latitude"
                    label={t('Latitude')}
                    value={data.latitude}
                    onChange={(v) => setData('latitude', v)}
                    error={errors.latitude}
                    placeholder="-29.3167"
                />
                <TextField
                    id="longitude"
                    label={t('Longitude')}
                    value={data.longitude}
                    onChange={(v) => setData('longitude', v)}
                    error={errors.longitude}
                    placeholder="27.4833"
                />
            </div>

            <div className="flex flex-wrap items-center gap-2">
                <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    onClick={captureLocation}
                    disabled={locating}
                >
                    {locating ? (
                        <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                    ) : (
                        <LocateFixed className="mr-2 h-4 w-4" />
                    )}
                    {locating ? t('Capturing…') : t('Use my location')}
                </Button>
                <span className="text-xs text-muted-foreground">
                    {t('Auto-capture coordinates from your device.')}
                </span>
            </div>
            {locError && <p className="text-sm text-destructive">{locError}</p>}

            {/* Evidence attachments. FileDropzone's existingPreviewUrl/
                onRemoveExisting props are single-file-only (per its own
                docblock), so they don't apply here in multiple mode — the
                already-uploaded list below is the only way to show and link
                to previously attached evidence in edit mode. */}
            <div className="col-span-full space-y-3">
                {canSave ? (
                    <FileDropzone
                        multiple
                        maxFiles={5}
                        maxSizeMB={20}
                        accept={{
                            'image/*': ['.png', '.jpg', '.jpeg'],
                            'video/*': ['.mp4', '.mov'],
                            'audio/*': ['.mp3', '.wav', '.m4a'],
                            'application/pdf': ['.pdf'],
                        }}
                        value={data.attachments}
                        onChange={(files) => setData('attachments', files)}
                        existingFiles={
                            isEdit
                                ? (grievance!.attachments ?? [])
                                      .filter(
                                          (a) =>
                                              !data.remove_attachment_ids.includes(
                                                  a.id,
                                              ),
                                      )
                                      .map((a) => ({
                                          id: a.id,
                                          url: a.url,
                                          name: a.file_name,
                                          isImage:
                                              a.mime_type.startsWith('image/'),
                                      }))
                                : []
                        }
                        onRemoveExistingFile={(id) =>
                            setData('remove_attachment_ids', [
                                ...data.remove_attachment_ids,
                                id as number,
                            ])
                        }
                        label={t('Evidence Attachments')}
                        helperText={t(
                            'Images, video, audio or PDF — up to 5 files, 20MB each.',
                        )}
                    />
                ) : (
                    <div className="space-y-2 rounded-md border p-3 text-sm">
                        <p className="font-medium">{t('Attached documents')}</p>
                        {(grievance?.attachments ?? []).length === 0 && (
                            <p className="text-muted-foreground">
                                {t('No documents attached.')}
                            </p>
                        )}
                        {(grievance?.attachments ?? []).map((attachment) => (
                            <a
                                key={attachment.id}
                                href={attachment.url}
                                target="_blank"
                                rel="noreferrer"
                                className="block text-primary underline"
                            >
                                {attachment.file_name}
                            </a>
                        ))}
                    </div>
                )}
                {errors.attachments && (
                    <p className="-mt-2 text-sm text-destructive">
                        {errors.attachments}
                    </p>
                )}
            </div>

            {isEdit && (
                <div className="col-span-full flex flex-wrap justify-end gap-2 border-t pt-4">
                    <Button
                        type="button"
                        variant="outline"
                        onClick={() => window.history.back()}
                    >
                        {t('Back')}
                    </Button>
                    {canOperateWorkflow && (
                        <Button
                            type="button"
                            onClick={() => workflowAction('forward')}
                        >
                            {t('Forward')}
                        </Button>
                    )}
                    {canOperateWorkflow &&
                        (roles.includes('Director') ||
                            roles.includes('Section Manager')) && (
                            <Button
                                type="button"
                                variant="destructive"
                                onClick={() => workflowAction('reject')}
                            >
                                {t('Reject')}
                            </Button>
                        )}
                    {roles.includes('Helpdesk Officer') &&
                        [
                            'assigned_officer',
                            'in_progress',
                            'escalated',
                        ].includes(grievance!.status) && (
                            <Button type="button" onClick={resolve}>
                                {t('Resolve with remarks')}
                            </Button>
                        )}
                </div>
            )}
        </FormLayout>
    );
}
