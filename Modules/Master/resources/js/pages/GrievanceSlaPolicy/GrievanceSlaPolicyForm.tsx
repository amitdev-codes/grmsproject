import { NumberField, Select2Field, StatusField, TextField } from '@/components/form-fields';
import { FormLayout } from '@/components/form-layout';
import { useTranslation } from '@/hooks/use-translation';
import { rules, validateForm } from '@/lib/validation';
import { useForm } from '@inertiajs/react';
import { FolderKanban } from 'lucide-react';
import { route } from 'ziggy-js';
import type { GrievanceSlaPolicy } from './Columns';

interface GrievanceSlaPolicyFormProps {
    policy: GrievanceSlaPolicy | null;
}

interface FormValues {
    code: string;
    name: string;
    priority: string;
    acknowledgement_hours: string;
    resolution_hours: string;
    use_business_hours: '1' | '0';
    is_active: '1' | '0';
}

const PRIORITIES = [
    { value: 'low', label: 'Low' },
    { value: 'medium', label: 'Medium' },
    { value: 'high', label: 'High' },
    { value: 'critical', label: 'Critical' },
];

export default function Form({ policy }: GrievanceSlaPolicyFormProps) {
    const isEdit = !!policy;
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
        code: policy?.code ?? '',
        name: policy?.name ?? '',
        priority: policy?.priority ?? 'medium',
        acknowledgement_hours: policy?.acknowledgement_hours?.toString() ?? '48',
        resolution_hours: policy?.resolution_hours?.toString() ?? '72',
        use_business_hours: policy ? (policy.use_business_hours ? '1' : '0') : '1',
        is_active: policy ? (policy.is_active ? '1' : '0') : '1',
    });

    const submit = (e: React.SyntheticEvent) => {
        e.preventDefault();

        const clientErrors = validateForm(data, {
            code: [rules.required()],
            name: [rules.required()],
            acknowledgement_hours: [rules.required()],
            resolution_hours: [rules.required()],
        });

        if (Object.keys(clientErrors).length > 0) {
            setError(clientErrors as Record<keyof FormValues, string>);

            return;
        }

        clearErrors();

        const url = isEdit
            ? route('grievance-sla-policies.update', policy!.id)
            : route('grievance-sla-policies.store');

        transform((formData) => ({
            ...formData,
            ...(isEdit ? { _method: 'put' } : {}),
        }));

        post(url, { preserveScroll: true });
    };

    return (
        <FormLayout
            title={isEdit ? t('Edit SLA Policy') : t('Create SLA Policy')}
            description={
                isEdit
                    ? t("Update :name's details.", { name: policy!.name })
                    : t('Add a new SLA policy to the system.')
            }
            breadcrumbs={[
                {
                    label: t('SLA Policies'),
                    icon: FolderKanban,
                    href: route('grievance-sla-policies.index'),
                },
                { label: isEdit ? t('Edit SLA Policy') : t('Create SLA Policy') },
            ]}
            onSubmit={submit}
            processing={processing}
            submitLabel={isEdit ? t('Save changes') : t('Create SLA Policy')}
        >
            <TextField
                id="code"
                required
                label={t('Code')}
                value={data.code}
                onChange={(v) => setData('code', v.toUpperCase())}
                error={errors.code}
                placeholder={t('e.g. SLA-001, SLA-002')}
            />
            <TextField
                id="name"
                required
                label={t('Name')}
                value={data.name}
                onChange={(v) => setData('name', v)}
                error={errors.name}
            />
            <Select2Field
                id="priority"
                label={t('Priority')}
                value={data.priority}
                onChange={(v) => setData('priority', v)}
                options={PRIORITIES}
                placeholder={t('Select a priority')}
                error={errors.priority}
            />
            <NumberField
                id="acknowledgement_hours"
                required
                label={t('Acknowledgement (hours)')}
                value={data.acknowledgement_hours}
                onChange={(v) => setData('acknowledgement_hours', v)}
                error={errors.acknowledgement_hours}
                placeholder="48"
            />
            <NumberField
                id="resolution_hours"
                required
                label={t('Resolution (hours)')}
                value={data.resolution_hours}
                onChange={(v) => setData('resolution_hours', v)}
                error={errors.resolution_hours}
                placeholder="72"
            />
            <StatusField
                id="use_business_hours"
                label={t('Use Business Hours')}
                value={data.use_business_hours}
                onChange={(v) => setData('use_business_hours', v)}
                error={errors.use_business_hours}
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