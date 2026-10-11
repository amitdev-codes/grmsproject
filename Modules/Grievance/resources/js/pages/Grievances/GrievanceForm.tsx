import { FileDropzone } from '@/components/file-dropzone'; // adjust path if it lives elsewhere in your tree
import { Select2Field, StatusField, TextField } from '@/components/form-fields';
import { PhoneField } from '@/components/form-fields/phone-field';
import { FormLayout } from '@/components/form-layout';
import { RichTextEditor } from '@/components/rich-text-editor';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { useTranslation } from '@/hooks/use-translation';
import { rules, validateForm } from '@/lib/validation';
import { statusLabel, statusVariant } from '@/types/grievance-status';
import { router, useForm, usePage } from '@inertiajs/react';
import { Download, FileWarning, LocateFixed, Loader2 } from 'lucide-react';
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
    workflowStep?: {
        id: number;
        name: string;
        description: string | null;
        action_type: string;
        role_name: string | null;
        approver_user_id: number | null;
        rejection_action: string;
        rejection_target_step: number | null;
    } | null;
    workflowActions?: {
        id: number;
        decision: string;
        reason: string | null;
        from_step: string | null;
        to_step: string | null;
        actor: string | null;
        created_at: string;
        documents: {
            id: number;
            original_name: string;
            mime_type: string;
            download_url: string;
        }[];
    }[];
    resolutionDraft?: { resolution_text: string } | null;
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
    documents: File[];
    resolution_html: string;
    remove_attachment_ids: number[];
    officer_id: string;
    workflow_decision: 'approve' | 'reject' | 'acknowledge';
}

export default function Form({
    grievance,
    categories,
    channels,
    districts,
    divisions,
    sections,
    officers = [],
    workflowStep = null,
    workflowActions = [],
    resolutionDraft = null,
}: GrievanceFormProps) {
    const isEdit = Boolean(grievance);
    const { t } = useTranslation();
    const page = usePage();
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
    const isWorkflowApprover = !!workflowStep && (
        (workflowStep.approver_user_id !== null &&
            workflowStep.approver_user_id === user?.id) ||
        (workflowStep.approver_user_id === null &&
            !!workflowStep.role_name &&
            roles.includes(workflowStep.role_name))
    );
    const canOperateWorkflow = !!grievance && isWorkflowApprover &&
        !['rejected', 'resolved', 'closed'].includes(grievance.status) &&
        (workflowStep?.action_type !== 'prepare_resolution' ||
            grievance.assigned_officer_id === user?.id);
    const [locating, setLocating] = useState(false);
    const [locError, setLocError] = useState<string | null>(null);
    const [workflowSubmitting, setWorkflowSubmitting] = useState(false);

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
        documents: [],
        resolution_html: resolutionDraft?.resolution_text ?? '',
        remove_attachment_ids: [],
        officer_id: '',
        workflow_decision:
            grievance?.status === 'assigned_officer' ? 'acknowledge' : 'approve',
    });

    const isAnonymous = data.is_anonymous === '1';

    const workflowAction = () => {
        clearErrors();

        const requestOptions = {
            preserveScroll: true,
            forceFormData: true,
            onStart: () => setWorkflowSubmitting(true),
            onFinish: () => setWorkflowSubmitting(false),
            onError: (validationErrors: Record<string, string>) =>
                setError(validationErrors as Record<keyof FormValues, string>),
        };

        if (data.workflow_decision === 'reject') {
            if (!data.remarks.trim()) {
                setError('remarks', 'Rejection remarks are required.');

                return;
            }

            router.post(
                route('grievances.workflow-action', grievance!.id),
                {
                    decision: data.workflow_decision,
                    reason: data.remarks,
                    documents: data.documents,
                },
                requestOptions,
            );

            return;
        }

        if (data.workflow_decision === 'acknowledge') {
            router.post(
                route('grievances.workflow-action', grievance!.id),
                {
                    decision: data.workflow_decision,
                    reason: data.remarks,
                    documents: data.documents,
                },
                requestOptions,
            );

            return;
        }

        if (!workflowStep) {
            setError('remarks', 'There is no active configured workflow step.');

            return;
        }

        router.post(
            route('grievances.workflow-action', grievance!.id),
            {
                decision: data.workflow_decision,
                division_id: data.division_id,
                section_id: data.section_id,
                officer_id: data.officer_id,
                resolution_html: data.resolution_html,
                reason: data.remarks,
                documents: data.documents,
            },
            requestOptions,
        );
    };

    const resolve = () => {
        if (!data.resolution_html.trim()) {
            setError('resolution_html', 'Resolution draft is required.');

            return;
        }

        workflowAction();
    };

    const startInvestigation = () => {
        router.post(
            route('grievances.start-investigation', grievance!.id),
            { remarks: data.remarks },
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

    if (isEdit && grievance) {
        const canSubmitWorkflow = canOperateWorkflow && !!workflowStep;
        const isInitialAcknowledgement =
            grievance.status === 'assigned_officer' &&
            workflowStep?.action_type === 'prepare_resolution';
        const decisionLabel =
            workflowStep?.action_type === 'final_approval'
                ? t('Approve and resolve')
                : t('Forward to next workflow step');

        return (
            <FormLayout
                title={t('Review grievance')}
                description={t('Case :ref · original intake details are read-only.', {
                    ref: grievance.reference_no,
                })}
                breadcrumbs={[
                    {
                        label: t('Grievances'),
                        icon: FileWarning,
                        href: route('grievances.index'),
                    },
                    { label: t('Review grievance') },
                ]}
                onSubmit={(event) => event.preventDefault()}
                hideFooter
                contentClassName="grid-cols-1 lg:grid-cols-2"
            >
                <section className="col-span-full space-y-5 rounded-lg border bg-muted/20 p-4 sm:p-6">
                    <div className="flex flex-wrap items-center gap-3 border-b pb-4">
                        <div className="min-w-0 flex-1">
                            <p className="text-xs font-medium tracking-wide text-muted-foreground uppercase">
                                {t('Grievance reference')}
                            </p>
                            <h2 className="font-mono text-lg font-semibold">
                                {grievance.reference_no}
                            </h2>
                        </div>
                        <Badge variant={statusVariant(grievance.status)}>
                            {statusLabel(grievance.status)}
                        </Badge>
                        <Badge variant="outline">
                            {t(workflowStep?.name ?? 'Workflow step unavailable')}
                        </Badge>
                    </div>

                    <div className="flex flex-wrap gap-2">
                        {grievance.category?.name_en && (
                            <Badge variant="secondary">{t('Category')}: {grievance.category.name_en}</Badge>
                        )}
                        {grievance.division?.name && (
                            <Badge variant="outline">{t('Division')}: {grievance.division.name}</Badge>
                        )}
                        {grievance.section?.name && (
                            <Badge variant="outline">{t('Section')}: {grievance.section.name}</Badge>
                        )}
                        <Badge variant={grievance.priority === 'high' ? 'destructive' : 'secondary'}>
                            {t('Priority')}: {t(grievance.priority || 'normal')}
                        </Badge>
                    </div>

                    <div className="grid gap-5 md:grid-cols-2">
                        <section className="space-y-3 rounded-md border bg-background p-4">
                            <h3 className="font-semibold">{t('Original grievance')}</h3>
                            <dl className="grid grid-cols-1 gap-x-4 gap-y-3 text-sm sm:grid-cols-2">
                                <div>
                                    <dt className="text-muted-foreground">{t('Complainant')}</dt>
                                    <dd>{grievance.is_anonymous ? t('Anonymous') : grievance.complainant_name || '—'}</dd>
                                </div>
                                <div>
                                    <dt className="text-muted-foreground">{t('Phone')}</dt>
                                    <dd>{grievance.complainant_phone || '—'}</dd>
                                </div>
                                <div>
                                    <dt className="text-muted-foreground">{t('Email')}</dt>
                                    <dd className="break-words">{grievance.complainant_email || '—'}</dd>
                                </div>
                                <div>
                                    <dt className="text-muted-foreground">{t('Channel')}</dt>
                                    <dd>{grievance.channel?.name || '—'}</dd>
                                </div>
                                <div>
                                    <dt className="text-muted-foreground">{t('District')}</dt>
                                    <dd>{grievance.district?.name || '—'}</dd>
                                </div>
                                <div>
                                    <dt className="text-muted-foreground">{t('Address')}</dt>
                                    <dd>{grievance.location_description || '—'}</dd>
                                </div>
                                <div>
                                    <dt className="text-muted-foreground">{t('Latitude')}</dt>
                                    <dd>{grievance.latitude ?? '—'}</dd>
                                </div>
                                <div>
                                    <dt className="text-muted-foreground">{t('Longitude')}</dt>
                                    <dd>{grievance.longitude ?? '—'}</dd>
                                </div>
                                <div>
                                    <dt className="text-muted-foreground">{t('Received')}</dt>
                                    <dd>{new Date(grievance.created_at).toLocaleString()}</dd>
                                </div>
                            </dl>
                        </section>
                        <section className="space-y-3 rounded-md border bg-background p-4">
                            <h3 className="font-semibold">{t('Original description')}</h3>
                            <p className="max-h-72 overflow-y-auto whitespace-pre-wrap text-sm leading-relaxed">
                                {grievance.description}
                            </p>
                        </section>
                    </div>

                    <div className="space-y-3">
                        <h3 className="font-semibold">{t('Original documents and evidence')}</h3>
                        {(grievance.attachments ?? []).length === 0 ? (
                            <p className="rounded-md border border-dashed p-4 text-sm text-muted-foreground">
                                {t('No original documents were attached.')}
                            </p>
                        ) : (
                            <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                                {grievance.attachments.map((attachment) => (
                                    <article key={attachment.id} className="overflow-hidden rounded-md border bg-background">
                                        <div className="flex items-center justify-between gap-3 p-3">
                                            <div className="min-w-0">
                                                <p className="truncate text-sm font-medium">{attachment.file_name}</p>
                                                <p className="text-xs text-muted-foreground">
                                                    {attachment.mime_type} · {(attachment.size / 1024 / 1024).toFixed(2)} MB
                                                </p>
                                            </div>
                                            <a
                                                href={attachment.url}
                                                download={attachment.file_name}
                                                className="inline-flex shrink-0 items-center gap-1 text-sm text-primary hover:underline"
                                            >
                                                <Download className="size-4" />
                                                {t('Download')}
                                            </a>
                                        </div>
                                        {attachment.mime_type.startsWith('image/') && (
                                            <a href={attachment.url} target="_blank" rel="noreferrer">
                                                <img src={attachment.url} alt={attachment.file_name} className="h-40 w-full bg-muted object-contain" />
                                            </a>
                                        )}
                                        {attachment.mime_type === 'application/pdf' && (
                                            <iframe title={attachment.file_name} src={attachment.url} className="h-44 w-full border-t" />
                                        )}
                                        {attachment.mime_type.startsWith('audio/') && (
                                            <audio controls preload="metadata" src={attachment.url} className="w-full px-3 pb-3" />
                                        )}
                                        {attachment.mime_type.startsWith('video/') && (
                                            <video controls preload="metadata" src={attachment.url} className="h-40 w-full bg-black object-contain" />
                                        )}
                                    </article>
                                ))}
                            </div>
                        )}
                    </div>
                </section>

                {workflowActions.length > 0 && (
                    <section className="col-span-full space-y-3 rounded-lg border p-4 sm:p-6">
                        <div>
                            <h3 className="font-semibold">{t('Workflow history and staff documents')}</h3>
                            <p className="text-sm text-muted-foreground">
                                {t('Documents uploaded at previous workflow levels remain available to authorized officers.')}
                            </p>
                        </div>
                        {workflowActions.map((action) => (
                            <article key={action.id} className="space-y-3 border-t pt-3 text-sm">
                                <div className="flex flex-wrap items-center gap-2">
                                    <span className="font-medium">{action.actor ?? t('Staff')}</span>
                                    <Badge variant="outline">{t(action.decision)}</Badge>
                                    <span className="text-muted-foreground">
                                        {action.from_step}
                                        {action.to_step && action.to_step !== action.from_step ? ` → ${action.to_step}` : ''}
                                    </span>
                                    <time className="ml-auto text-xs text-muted-foreground">
                                        {new Date(action.created_at).toLocaleString()}
                                    </time>
                                </div>
                                {action.reason && (
                                    <p className="whitespace-pre-wrap rounded-md bg-muted/40 p-3 text-muted-foreground">
                                        {action.reason}
                                    </p>
                                )}
                                {action.documents.length > 0 && (
                                    <div className="grid gap-3 sm:grid-cols-2">
                                        {action.documents.map((document) => (
                                            <article key={document.id} className="overflow-hidden rounded-md border">
                                                <div className="flex items-center justify-between gap-3 p-3">
                                                    <span className="truncate font-medium">{document.original_name}</span>
                                                    <a href={document.download_url} className="inline-flex shrink-0 items-center gap-1 text-primary hover:underline">
                                                        <Download className="size-4" />
                                                        {t('Download')}
                                                    </a>
                                                </div>
                                                {document.mime_type.startsWith('image/') && (
                                                    <a href={document.download_url} target="_blank" rel="noreferrer">
                                                        <img src={document.download_url} alt={document.original_name} className="max-h-64 w-full bg-muted object-contain" />
                                                    </a>
                                                )}
                                                {document.mime_type === 'application/pdf' && (
                                                    <iframe title={document.original_name} src={document.download_url} className="h-64 w-full border-t" />
                                                )}
                                            </article>
                                        ))}
                                    </div>
                                )}
                            </article>
                        ))}
                    </section>
                )}

                <section className="col-span-full space-y-4 rounded-lg border p-4 sm:p-6">
                    <div>
                        <h3 className="font-semibold">{t('Your workflow response')}</h3>
                        <p className="text-sm text-muted-foreground">
                            {workflowStep?.description ?? t('Choose what should happen next and add any supporting remarks or documents.')}
                        </p>
                    </div>
                    {canSubmitWorkflow ? (
                        <>
                            <div className="space-y-1.5">
                                <label htmlFor="workflow_decision" className="text-sm font-medium">
                                    {t('Next action')}
                                </label>
                                <select
                                    id="workflow_decision"
                                    value={data.workflow_decision}
                                    onChange={(event) =>
                                        setData('workflow_decision', event.target.value as FormValues['workflow_decision'])
                                    }
                                    className="h-10 w-full rounded-md border bg-background px-3 text-sm"
                                >
                                    {isInitialAcknowledgement && (
                                        <option value="acknowledge">{t('Acknowledge receipt and start investigation')}</option>
                                    )}
                                    {!isInitialAcknowledgement && (
                                        <option value="approve">{decisionLabel}</option>
                                    )}
                                    <option value="reject">{t('Reject or return for changes')}</option>
                                </select>
                            </div>
                            <div className="col-span-full space-y-2">
                                <label className="text-sm font-medium" htmlFor="remarks">
                                    {t('Remarks')}
                                    {data.workflow_decision === 'reject' && (
                                        <span className="ml-1 text-destructive">*</span>
                                    )}
                                </label>
                                <textarea
                                    id="remarks"
                                    rows={5}
                                    maxLength={5000}
                                    required={data.workflow_decision === 'reject'}
                                    value={data.remarks}
                                    onChange={(event) => setData('remarks', event.target.value)}
                                    placeholder={data.workflow_decision === 'reject'
                                        ? t('Explain why this grievance is being rejected or returned.')
                                        : t('Add your review, handover, or investigation remarks.')}
                                    className="w-full rounded-md border bg-background px-3 py-2 text-sm"
                                />
                                {errors.remarks && <p role="alert" className="text-sm text-destructive">{errors.remarks}</p>}
                            </div>
                            {workflowStep?.action_type === 'allocate_division' && (
                                <Select2Field
                                    id="division_id"
                                    required
                                    label={t('Forward to division')}
                                    value={data.division_id}
                                    onChange={(value) => setData('division_id', value)}
                                    options={divisionOptions}
                                    placeholder={t('Select a division')}
                                    error={errors.division_id}
                                />
                            )}
                            {workflowStep?.action_type === 'allocate_section' && (
                                <Select2Field
                                    id="section_id"
                                    required
                                    label={t('Forward to section')}
                                    value={data.section_id}
                                    onChange={(value) => setData('section_id', value)}
                                    options={sectionOptions}
                                    placeholder={t('Select a section')}
                                    error={errors.section_id}
                                />
                            )}
                            {workflowStep?.action_type === 'assign_officer' && (
                                <div className="col-span-full space-y-2">
                                    <Select2Field
                                        id="officer_id"
                                        required
                                        label={t('Assign investigating officer')}
                                        value={data.officer_id}
                                        onChange={(value) => setData('officer_id', value)}
                                        options={officers.map((officer) => ({
                                            value: String(officer.id),
                                            label: officer.name,
                                        }))}
                                        placeholder={t('Select an officer')}
                                        error={errors.officer_id}
                                    />
                                    {officers.length === 0 && (
                                        <p role="alert" className="text-sm text-destructive">
                                            {t('No active Helpdesk Officers are assigned to this section or available for assignment. Ask an administrator to assign an officer to this section first.')}
                                        </p>
                                    )}
                                </div>
                            )}
                            {workflowStep?.action_type === 'prepare_resolution' &&
                                data.workflow_decision === 'approve' && (
                                    <>
                                        <div className="col-span-full space-y-2">
                                            <label className="text-sm font-medium" htmlFor="resolution_html">
                                                {t('Proposed resolution draft')}
                                            </label>
                                            <RichTextEditor
                                                id="resolution_html"
                                                label={t('Proposed resolution draft')}
                                                value={data.resolution_html}
                                                onChange={(value) => setData('resolution_html', value)}
                                            />
                                            {errors.resolution_html && <p role="alert" className="text-sm text-destructive">{errors.resolution_html}</p>}
                                        </div>
                                    </>
                                )}
                            {workflowStep?.action_type === 'final_approval' && (
                                <div className="col-span-full space-y-2">
                                    <label className="text-sm font-medium">{t('Resolution draft submitted for approval')}</label>
                                    {resolutionDraft ? (
                                        <RichTextEditor
                                            id="resolution_draft_preview"
                                            label={t('Resolution draft submitted for approval')}
                                            value={resolutionDraft.resolution_text}
                                            onChange={() => undefined}
                                            disabled
                                        />
                                    ) : (
                                        <p className="rounded-md border border-dashed p-4 text-sm text-muted-foreground">
                                            {t('No resolution draft has been submitted yet.')}
                                        </p>
                                    )}
                                </div>
                            )}
                            <div className="col-span-full space-y-2">
                                <FileDropzone
                                    multiple
                                    maxFiles={5}
                                    maxSizeMB={20}
                                    accept={{
                                        'application/pdf': ['.pdf'],
                                        'application/msword': ['.doc'],
                                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document': ['.docx'],
                                        'application/vnd.ms-excel': ['.xls'],
                                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet': ['.xlsx'],
                                        'text/csv': ['.csv'],
                                        'image/jpeg': ['.jpg', '.jpeg'],
                                        'image/png': ['.png'],
                                    }}
                                    value={data.documents}
                                    onChange={(files) => setData('documents', files)}
                                    label={t('Supporting documents')}
                                    helperText={t('Up to five private files, 20 MB each. Files are added to this workflow step and remain visible to authorized officers.')}
                                />
                                {errors.documents && <p role="alert" className="text-sm text-destructive">{errors.documents}</p>}
                            </div>
                            <div className="col-span-full flex flex-wrap justify-end gap-2 border-t pt-4">
                                <Button type="button" variant="outline" onClick={() => window.history.back()}>
                                    {t('Cancel')}
                                </Button>
                                <Button type="button" disabled={processing || workflowSubmitting} onClick={workflowAction}>
                                    {processing || workflowSubmitting ? t('Submitting…') : t('Submit workflow action')}
                                </Button>
                            </div>
                        </>
                    ) : (
                        <p className="rounded-md bg-muted/40 p-3 text-sm text-muted-foreground">
                            {t('This grievance is waiting for the authorized workflow officer. You can review the case details and documents above.')}
                        </p>
                    )}
                </section>
            </FormLayout>
        );
    }

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

            {isEdit && workflowStep && (
                <section className="col-span-full space-y-1 rounded-md border border-primary/30 bg-primary/5 p-4">
                    <h2 className="font-semibold">
                        Current workflow: {workflowStep.name}
                    </h2>
                    {workflowStep.description && (
                        <p className="text-sm text-muted-foreground">
                            {workflowStep.description}
                        </p>
                    )}
                    {workflowStep.rejection_action === 'return_to_step' && (
                        <p className="text-xs text-muted-foreground">
                            Rejection returns this case to level {workflowStep.rejection_target_step}.
                        </p>
                    )}
                </section>
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

            {(!isEdit || workflowStep?.action_type === 'allocate_division') && (
                <Select2Field
                    id="division_id"
                    label={t('Division')}
                    value={data.division_id}
                    onChange={(v) => {
                        setData('division_id', v);
                        setData('section_id', '');
                    }}
                    options={divisionOptions}
                    placeholder={t('Select a division')}
                    error={errors.division_id}
                />
            )}

            {(!isEdit || workflowStep?.action_type === 'allocate_section') && (
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
            )}

            {isEdit && workflowStep?.action_type === 'assign_officer' && (
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
                    {t('Workflow notes or rejection reason')}
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

            {isEdit && workflowStep?.action_type === 'prepare_resolution' && (
                <div className="col-span-full space-y-2">
                    <label className="text-sm font-medium" htmlFor="resolution_html">
                        {t('Proposed resolution draft')}
                    </label>
                    <RichTextEditor
                        id="resolution_html"
                        label={t('Proposed resolution draft')}
                        value={data.resolution_html}
                        onChange={(value) => setData('resolution_html', value)}
                        disabled={!canOperateWorkflow || !['in_progress', 'escalated'].includes(grievance?.status ?? '')}
                    />
                    {errors.resolution_html && (
                        <p role="alert" className="text-sm text-destructive">
                            {errors.resolution_html}
                        </p>
                    )}
                </div>
            )}

            {isEdit && workflowStep?.action_type === 'final_approval' && (
                <div className="col-span-full space-y-2">
                    <label className="text-sm font-medium" htmlFor="resolution_draft_preview">
                        {t('Resolution draft submitted for approval')}
                    </label>
                    {resolutionDraft ? (
                        <RichTextEditor
                            id="resolution_draft_preview"
                            label={t('Resolution draft submitted for approval')}
                            value={resolutionDraft.resolution_text}
                            onChange={() => undefined}
                            disabled
                        />
                    ) : (
                        <p role="alert" className="text-sm text-destructive">
                            {t('No resolution draft has been submitted yet.')}
                        </p>
                    )}
                </div>
            )}

            {isEdit && workflowStep && canOperateWorkflow && (
                <div className="col-span-full space-y-2">
                    <label className="text-sm font-medium" htmlFor="workflow_documents">
                        {t('Workflow documents')}
                    </label>
                    <input
                        id="workflow_documents"
                        type="file"
                        multiple
                        accept=".pdf,.doc,.docx,.xls,.xlsx,.csv,.jpg,.jpeg,.png"
                        onChange={(event) =>
                            setData('documents', Array.from(event.target.files ?? []))
                        }
                        className="block w-full rounded-md border bg-background p-2 text-sm file:mr-3 file:rounded file:border-0 file:bg-muted file:px-3 file:py-1"
                    />
                    <p className="text-xs text-muted-foreground">
                        {t('Up to five private files, 20 MB each. They are only available to authorized staff.')}
                    </p>
                    {data.documents.length > 0 && (
                        <ul className="list-inside list-disc text-sm">
                            {data.documents.map((file) => (
                                <li key={`${file.name}-${file.size}`}>{file.name}</li>
                            ))}
                        </ul>
                    )}
                    {errors.documents && (
                        <p role="alert" className="text-sm text-destructive">
                            {errors.documents}
                        </p>
                    )}
                </div>
            )}

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

            {isEdit && workflowActions.length > 0 && (
                <section className="col-span-full space-y-3 rounded-md border p-4">
                    <h2 className="font-semibold">{t('Workflow history and documents')}</h2>
                    {workflowActions.map((action) => (
                        <div key={action.id} className="border-t pt-3 text-sm">
                            <p className="font-medium">
                                {action.actor ?? t('Staff')} — {action.decision}
                                {action.from_step && ` · ${action.from_step}`}
                                {action.to_step && ` → ${action.to_step}`}
                            </p>
                            {action.reason && (
                                <p className="mt-1 whitespace-pre-wrap text-muted-foreground">
                                    {action.reason}
                                </p>
                            )}
                            <time className="text-xs text-muted-foreground">
                                {new Date(action.created_at).toLocaleString()}
                            </time>
                            {action.documents.length > 0 && (
                                <ul className="mt-2 list-inside list-disc">
                                    {action.documents.map((document) => (
                                        <li key={document.id}>
                                            <a
                                                href={document.download_url}
                                                className="text-primary underline"
                                            >
                                                {document.original_name}
                                            </a>
                                        </li>
                                    ))}
                                </ul>
                            )}
                        </div>
                    ))}
                </section>
            )}

            {isEdit && (
                <div className="col-span-full flex flex-wrap justify-end gap-2 border-t pt-4">
                    <Button
                        type="button"
                        variant="outline"
                        onClick={() => window.history.back()}
                    >
                        {t('Back')}
                    </Button>
                    {canOperateWorkflow && workflowStep && [
                        'allocate_division',
                        'allocate_section',
                        'assign_officer',
                        'review',
                    ].includes(workflowStep.action_type) && (
                        <Button
                            type="button"
                            onClick={workflowAction}
                        >
                            {t('Forward')}
                        </Button>
                    )}
                    {canOperateWorkflow && workflowStep?.rejection_action && (
                            <Button
                                type="button"
                                variant="destructive"
                                onClick={workflowAction}
                            >
                                {t('Reject')}
                            </Button>
                        )}
                    {roles.some((role) =>
                        ['Helpdesk Officer', 'Content Editor'].includes(role),
                    ) &&
                        grievance!.assigned_officer_id === user?.id &&
                        grievance!.status === 'assigned_officer' && (
                            <Button type="button" onClick={startInvestigation}>
                                {t('Start investigation')}
                            </Button>
                        )}
                    {canOperateWorkflow &&
                        workflowStep?.action_type === 'prepare_resolution' && (
                            <Button
                                type="button"
                                disabled={processing}
                                onClick={resolve}
                            >
                                {t('Submit resolution for approval')}
                            </Button>
                        )}
                    {canOperateWorkflow &&
                        workflowStep?.action_type === 'final_approval' && (
                            <Button
                                type="button"
                                disabled={processing || !resolutionDraft}
                                onClick={workflowAction}
                            >
                                {t('Approve final resolution')}
                            </Button>
                        )}
                </div>
            )}
        </FormLayout>
    );
}
