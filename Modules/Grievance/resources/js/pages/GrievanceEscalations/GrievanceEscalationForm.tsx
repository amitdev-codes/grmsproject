import {
    Select2Field,
    StatusField,
    TextareaField,
} from '@/components/form-fields';
import { FormLayout } from '@/components/form-layout';
import { useTranslation } from '@/hooks/use-translation';
import { rules, validateForm } from '@/lib/validation';
import { useForm } from '@inertiajs/react';
import { Users } from 'lucide-react';
import { route } from 'ziggy-js';
import type { GrievanceEscalation } from './columns';

interface LookupOption {
    id: number;
    name: string;
}

interface GrievanceLookupOption {
    id: number;
    reference_number: string;
}

interface GrievanceEscalationFormProps {
    grievanceEscalation: GrievanceEscalation | null;
    grievances: GrievanceLookupOption[];
    officers: LookupOption[];
}

interface FormValues {
    grievance_id: string;
    escalation_level: string;
    escalated_to: string;
    reason: string;
    resolved: '1' | '0';
}

export default function Form({
    grievanceEscalation,
    grievances,
    officers,
}: GrievanceEscalationFormProps) {
    const isEdit = !!grievanceEscalation;
    const { t } = useTranslation();
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
        grievance_id: grievanceEscalation?.grievance_id
            ? String(grievanceEscalation.grievance_id)
            : '',
        escalation_level: grievanceEscalation?.escalation_level
            ? String(grievanceEscalation.escalation_level)
            : '1',
        escalated_to: grievanceEscalation?.escalated_to
            ? String(grievanceEscalation.escalated_to)
            : '',
        reason: grievanceEscalation?.reason ?? '',
        resolved: grievanceEscalation?.resolved ? '1' : '0',
    });

    const submit = (e: React.SyntheticEvent) => {
        e.preventDefault();

        const clientErrors = validateForm(data, {
            grievance_id: [rules.required()],
            escalation_level: [rules.required()],
        });

        if (Object.keys(clientErrors).length > 0) {
            setError(clientErrors as Record<keyof FormValues, string>);

            return;
        }

        clearErrors();

        const url = isEdit
            ? route('grievance-escalations.update', grievanceEscalation!.id)
            : route('grievance-escalations.store');

        transform((formData) => ({
            ...formData,
            escalated_to: formData.escalated_to || null,
            ...(isEdit ? { _method: 'put' } : {}),
        }));

        post(url, { preserveScroll: true });
    };

    const grievanceOptions = grievances.map((g) => ({
        value: String(g.id),
        label: g.reference_number,
    }));
    const officerOptions = officers.map((o) => ({
        value: String(o.id),
        label: o.name,
    }));
    const levelOptions = [
        { value: '1', label: t('Level 1 – Zonal Officer (48hr)') },
        { value: '2', label: t('Level 2 – Regional Head (5 days)') },
        { value: '3', label: t('Level 3 – Director (7 days)') },
    ];

    return (
        <FormLayout
            title={isEdit ? t('Edit Escalation') : t('Create Escalation')}
            description={
                isEdit
                    ? t('Update escalation #:id.', {
                          id: grievanceEscalation!.id,
                      })
                    : t('Manually escalate a grievance.')
            }
            breadcrumbs={[
                {
                    label: t('Grievance Escalations'),
                    icon: Users,
                    href: route('grievance-escalations.index'),
                },
                {
                    label: isEdit
                        ? t('Edit Escalation')
                        : t('Create Escalation'),
                },
            ]}
            onSubmit={submit}
            processing={processing}
            submitLabel={isEdit ? t('Save changes') : t('Create Escalation')}
        >
            <Select2Field
                id="grievance_id"
                required
                label={t('Grievance')}
                value={data.grievance_id}
                onChange={(v) => setData('grievance_id', v)}
                options={grievanceOptions}
                placeholder={t('Select a grievance')}
                error={errors.grievance_id}
            />

            <Select2Field
                id="escalation_level"
                required
                label={t('Escalation Level')}
                value={data.escalation_level}
                onChange={(v) => setData('escalation_level', v)}
                options={levelOptions}
                error={errors.escalation_level}
            />

            <Select2Field
                id="escalated_to"
                label={t('Escalated To')}
                value={data.escalated_to}
                onChange={(v) => setData('escalated_to', v)}
                options={officerOptions}
                placeholder={t('Select an officer')}
                error={errors.escalated_to}
            />

            <TextareaField
                id="reason"
                label={t('Reason')}
                value={data.reason}
                onChange={(v) => setData('reason', v)}
                error={errors.reason}
            />

            <StatusField
                id="resolved"
                label={t('Resolved')}
                value={data.resolved}
                onChange={(v) => setData('resolved', v)}
                error={errors.resolved}
                activeLabel={t('Resolved')}
                inactiveLabel={t('Open')}
            />
        </FormLayout>
    );
}
