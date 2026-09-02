// resources/js/Pages/Resolutions/Form.tsx
import { Select2Field, TextareaField } from '@/components/form-fields';
import { FormLayout } from '@/components/form-layout';
import { useTranslation } from '@/hooks/use-translation';
import { rules, validateForm } from '@/lib/validation';
import { useForm } from '@inertiajs/react';
import { CheckCircle2 } from 'lucide-react';
import { route } from 'ziggy-js';
import type { Resolution } from './columns';

interface GrievanceLookupOption {
    id: number;
    reference_number: string;
}

interface ResolutionFormProps {
    resolution: Resolution | null;
    grievances: GrievanceLookupOption[];
    lockedGrievanceId?: number | null;
}

interface FormValues {
    grievance_id: string;
    resolution_text: string;
}

export default function Form({
    resolution,
    grievances,
    lockedGrievanceId = null,
}: ResolutionFormProps) {
    const isEdit = !!resolution;
    const { t } = useTranslation();

    // Once approved, this form shouldn't be the way to touch it further —
    // approve/confirm/reject are separate actions (see design note).
    const isLocked = isEdit && resolution!.approved_at !== null;

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
        grievance_id: resolution?.grievance_id
            ? String(resolution.grievance_id)
            : lockedGrievanceId
              ? String(lockedGrievanceId)
              : '',
        resolution_text: resolution?.resolution_text ?? '',
    });

    const submit = (e: React.SyntheticEvent) => {
        e.preventDefault();

        const clientErrors = validateForm(data, {
            grievance_id: [rules.required()],
            resolution_text: [rules.required()],
        });

        if (Object.keys(clientErrors).length > 0) {
            setError(clientErrors as Record<keyof FormValues, string>);

            return;
        }

        clearErrors();

        const url = isEdit
            ? route('resolutions.update', resolution!.id)
            : route('resolutions.store');

        transform((formData) => ({
            ...formData,
            ...(isEdit ? { _method: 'put' } : {}),
        }));

        post(url, { preserveScroll: true });
    };

    const grievanceOptions = grievances.map((g) => ({
        value: String(g.id),
        label: g.reference_number,
    }));

    return (
        <FormLayout
            title={isEdit ? t('Edit Resolution') : t('Propose Resolution')}
            description={
                isEdit
                    ? t('Update resolution #:id.', { id: resolution!.id })
                    : t('Propose a resolution for a grievance.')
            }
            breadcrumbs={[
                {
                    label: t('Resolutions'),
                    icon: CheckCircle2,
                    href: route('resolutions.index'),
                },
                {
                    label: isEdit
                        ? t('Edit Resolution')
                        : t('Propose Resolution'),
                },
            ]}
            onSubmit={submit}
            processing={processing || isLocked}
            submitLabel={isEdit ? t('Save changes') : t('Propose Resolution')}
        >
            {isLocked && (
                <div className="col-span-full rounded-md border bg-muted/40 px-3 py-2 text-sm text-muted-foreground">
                    {t(
                        'This resolution has already been approved and can no longer be edited here.',
                    )}
                </div>
            )}

            <Select2Field
                id="grievance_id"
                required
                disabled={isEdit || !!lockedGrievanceId}
                label={t('Grievance')}
                value={data.grievance_id}
                onChange={(v) => setData('grievance_id', v)}
                options={grievanceOptions}
                placeholder={t('Select a grievance')}
                error={errors.grievance_id}
            />

            <TextareaField
                id="resolution_text"
                required
                disabled={isLocked}
                label={t('Resolution')}
                value={data.resolution_text}
                onChange={(v) => setData('resolution_text', v)}
                error={errors.resolution_text}
                placeholder={t('Describe how this grievance was resolved…')}
            />
        </FormLayout>
    );
}
