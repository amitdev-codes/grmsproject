// resources/js/Pages/GrievanceStatusHistories/Form.tsx
import { Select2Field, TextareaField } from '@/components/form-fields';
import { FormLayout } from '@/components/form-layout';
import { useTranslation } from '@/hooks/use-translation';
import { rules, validateForm } from '@/lib/validation';
import { useForm } from '@inertiajs/react';
import { History } from 'lucide-react';
import { route } from 'ziggy-js';

interface GrievanceLookupOption {
    id: number;
    reference_number: string;
}
interface StatusOption {
    value: string;
    label: string;
}

interface GrievanceStatusHistoryFormProps {
    grievances: GrievanceLookupOption[];
    statusOptions: StatusOption[];
    lockedGrievanceId?: number | null;
}

interface FormValues {
    grievance_id: string;
    from_status: string;
    to_status: string;
    note: string;
}

// This form only ever creates a new entry — status history is an
// append-only audit log, so there is intentionally no edit mode here.
export default function Form({
    grievances,
    statusOptions,
    lockedGrievanceId = null,
}: GrievanceStatusHistoryFormProps) {
    const { t } = useTranslation();

    const { data, setData, post, processing, errors, setError, clearErrors } =
        useForm<FormValues>({
            grievance_id: lockedGrievanceId ? String(lockedGrievanceId) : '',
            from_status: '',
            to_status: '',
            note: '',
        });

    const submit = (e: React.SyntheticEvent) => {
        e.preventDefault();

        const clientErrors = validateForm(data, {
            grievance_id: [rules.required()],
            to_status: [rules.required()],
        });

        if (Object.keys(clientErrors).length > 0) {
            setError(clientErrors as Record<keyof FormValues, string>);
            return;
        }

        clearErrors();

        post(route('grievance-status-histories.store'), {
            preserveScroll: true,
        });
    };

    const grievanceOptions = grievances.map((g) => ({
        value: String(g.id),
        label: g.reference_number,
    }));

    return (
        <FormLayout
            title={t('Record Status Change')}
            description={t(
                'Manually record a historical status transition. Routine status changes should go through the grievance detail page instead.',
            )}
            breadcrumbs={[
                {
                    label: t('Status History'),
                    icon: History,
                    href: route('grievance-status-histories.index'),
                },
                { label: t('Record Status Change') },
            ]}
            onSubmit={submit}
            processing={processing}
            submitLabel={t('Record')}
        >
            <Select2Field
                id="grievance_id"
                required
                disabled={!!lockedGrievanceId}
                label={t('Grievance')}
                value={data.grievance_id}
                onChange={(v) => setData('grievance_id', v)}
                options={grievanceOptions}
                placeholder={t('Select a grievance')}
                error={errors.grievance_id}
            />

            <Select2Field
                id="from_status"
                label={t('From Status')}
                value={data.from_status}
                onChange={(v) => setData('from_status', v)}
                options={statusOptions}
                placeholder={t('(none — initial status)')}
                error={errors.from_status}
            />

            <Select2Field
                id="to_status"
                required
                label={t('To Status')}
                value={data.to_status}
                onChange={(v) => setData('to_status', v)}
                options={statusOptions}
                placeholder={t('Select a status')}
                error={errors.to_status}
            />

            <TextareaField
                id="note"
                label={t('Note')}
                value={data.note}
                onChange={(v) => setData('note', v)}
                error={errors.note}
                placeholder={t('Why is this entry being recorded manually?')}
            />
        </FormLayout>
    );
}
