import { Select2Field, StatusField, TextField } from '@/components/form-fields';
import { FormLayout } from '@/components/form-layout';
import { useTranslation } from '@/hooks/use-translation';
import { rules, validateForm } from '@/lib/validation';
import { useForm } from '@inertiajs/react';
import { ArrowUpRight } from 'lucide-react';
import { route } from 'ziggy-js';
import type { GrievanceEscalationRule } from './Columns';

interface GrievanceEscalationRuleFormProps {
    rule: GrievanceEscalationRule | null;
    slaPolicies: { id: number; name: string }[];
}

interface FormValues {
    grievance_sla_policy_id: string;
    escalation_level: string;
    breach_after_hours: string;
    extension_hours: string;
    target_role: string;
    requires_manual_review: '1' | '0';
    is_active: '1' | '0';
}

const TARGET_ROLES = [
    { value: 'zonal_officer', label: 'Zonal Officer' },
    { value: 'regional_head', label: 'Regional Head' },
    { value: 'director_roads', label: 'Director Roads' },
];

export default function Form({ rule, slaPolicies }: GrievanceEscalationRuleFormProps) {
    const isEdit = !!rule;
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
        grievance_sla_policy_id: rule?.grievance_sla_policy_id
            ? String(rule.grievance_sla_policy_id)
            : '',
        escalation_level: rule?.escalation_level?.toString() ?? '1',
        breach_after_hours: rule?.breach_after_hours?.toString() ?? '24',
        extension_hours: rule?.extension_hours?.toString() ?? '',
        target_role: rule?.target_role ?? 'zonal_officer',
        requires_manual_review: rule ? (rule.requires_manual_review ? '1' : '0') : '0',
        is_active: rule ? (rule.is_active ? '1' : '0') : '1',
    });

    const submit = (e: React.SyntheticEvent) => {
        e.preventDefault();

        const clientErrors = validateForm(data, {
            escalation_level: [rules.required()],
            breach_after_hours: [rules.required()],
            target_role: [rules.required()],
        });

        if (Object.keys(clientErrors).length > 0) {
            setError(clientErrors as Record<keyof FormValues, string>);

            return;
        }

        clearErrors();

        const url = isEdit
            ? route('grievance-escalation-rules.update', rule!.id)
            : route('grievance-escalation-rules.store');

        transform((formData) => ({
            ...formData,
            ...(isEdit ? { _method: 'put' } : {}),
        }));

        post(url, { preserveScroll: true });
    };

    const slaPolicyOptions = slaPolicies.map((p) => ({
        value: String(p.id),
        label: p.name,
    }));

    return (
        <FormLayout
            title={isEdit ? t('Edit Escalation Rule') : t('Create Escalation Rule')}
            description={
                isEdit
                    ? t("Update :name's details.", {
                          name: `Level ${rule!.escalation_level}`,
                      })
                    : t('Add a new escalation rule to the system.')
            }
            breadcrumbs={[
                {
                    label: t('Escalation Rules'),
                    icon: ArrowUpRight,
                    href: route('grievance-escalation-rules.index'),
                },
                { label: isEdit ? t('Edit Escalation Rule') : t('Create Escalation Rule') },
            ]}
            onSubmit={submit}
            processing={processing}
            submitLabel={isEdit ? t('Save changes') : t('Create Escalation Rule')}
        >
            <Select2Field
                id="grievance_sla_policy_id"
                label={t('SLA Policy')}
                value={data.grievance_sla_policy_id}
                onChange={(v) => setData('grievance_sla_policy_id', v)}
                options={slaPolicyOptions}
                placeholder={t('Select an SLA policy')}
                error={errors.grievance_sla_policy_id}
            />
            <TextField
                id="escalation_level"
                required
                label={t('Escalation Level')}
                value={data.escalation_level}
                onChange={(v) => setData('escalation_level', v)}
                error={errors.escalation_level}
            />
            <TextField
                id="breach_after_hours"
                required
                label={t('Breach After (hours)')}
                value={data.breach_after_hours}
                onChange={(v) => setData('breach_after_hours', v)}
                error={errors.breach_after_hours}
            />
            <TextField
                id="extension_hours"
                label={t('Extension (hours)')}
                value={data.extension_hours}
                onChange={(v) => setData('extension_hours', v)}
                error={errors.extension_hours}
            />
            <Select2Field
                id="target_role"
                required
                label={t('Target Role')}
                value={data.target_role}
                onChange={(v) => setData('target_role', v)}
                options={TARGET_ROLES}
                placeholder={t('Select a target role')}
                error={errors.target_role}
            />
            <StatusField
                id="requires_manual_review"
                label={t('Requires Manual Review')}
                value={data.requires_manual_review}
                onChange={(v) => setData('requires_manual_review', v)}
                error={errors.requires_manual_review}
                activeLabel={t('Yes')}
                inactiveLabel={t('No')}
            />
            <StatusField
                id="is_active"
                label={t('Is Active')}
                value={data.is_active}
                onChange={(v) => setData('is_active', v)}
                error={errors.is_active}
                activeLabel={t('Active')}
                inactiveLabel={t('Inactive')}
            />
        </FormLayout>
    );
}