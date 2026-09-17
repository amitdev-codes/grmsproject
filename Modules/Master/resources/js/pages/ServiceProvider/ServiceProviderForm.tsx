import { Select2Field, StatusField, TextField } from '@/components/form-fields';
import { FormLayout } from '@/components/form-layout';
import { useTranslation } from '@/hooks/use-translation';
import { rules, validateForm } from '@/lib/validation';
import { useForm } from '@inertiajs/react';
import { Building2 } from 'lucide-react';
import { route } from 'ziggy-js';
import type { ServiceProvider } from './Columns';

interface ServiceProviderFormProps {
    provider: ServiceProvider | null;
}

interface FormValues {
    code: string;
    name: string;
    provider_type: string;
    contact_name: string;
    phone: string;
    email: string;
    is_active: '1' | '0';
}

const PROVIDER_TYPES = [
    { value: 'contractor', label: 'Contractor' },
    { value: 'consultant', label: 'Consultant' },
    { value: 'other', label: 'Other' },
];

export default function Form({ provider }: ServiceProviderFormProps) {
    const isEdit = !!provider;
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
        code: provider?.code ?? '',
        name: provider?.name ?? '',
        provider_type: provider?.provider_type ?? 'contractor',
        contact_name: provider?.contact_name ?? '',
        phone: provider?.phone ?? '',
        email: provider?.email ?? '',
        is_active: provider ? (provider.is_active ? '1' : '0') : '1',
    });

    const submit = (e: React.SyntheticEvent) => {
        e.preventDefault();

        const clientErrors = validateForm(data, {
            code: [rules.required()],
            name: [rules.required()],
            provider_type: [rules.required()],
        });

        if (Object.keys(clientErrors).length > 0) {
            setError(clientErrors as Record<keyof FormValues, string>);

            return;
        }

        clearErrors();

        const url = isEdit
            ? route('service-providers.update', provider!.id)
            : route('service-providers.store');

        transform((formData) => ({
            ...formData,
            ...(isEdit ? { _method: 'put' } : {}),
        }));

        post(url, { preserveScroll: true });
    };

    return (
        <FormLayout
            title={isEdit ? t('Edit Service Provider') : t('Create Service Provider')}
            description={
                isEdit
                    ? t("Update :name's details.", { name: provider!.name })
                    : t('Add a new service provider to the system.')
            }
            breadcrumbs={[
                {
                    label: t('Service Providers'),
                    icon: Building2,
                    href: route('service-providers.index'),
                },
                { label: isEdit ? t('Edit Service Provider') : t('Create Service Provider') },
            ]}
            onSubmit={submit}
            processing={processing}
            submitLabel={isEdit ? t('Save changes') : t('Create Service Provider')}
        >
            <TextField
                id="code"
                required
                label={t('Code')}
                value={data.code}
                onChange={(v) => setData('code', v.toUpperCase())}
                error={errors.code}
                placeholder={t('e.g. SP-001, CON-042')}
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
                id="provider_type"
                required
                label={t('Type')}
                value={data.provider_type}
                onChange={(v) => setData('provider_type', v)}
                options={PROVIDER_TYPES}
                placeholder={t('Select a type')}
                error={errors.provider_type}
            />
            <TextField
                id="contact_name"
                label={t('Contact Name')}
                value={data.contact_name}
                onChange={(v) => setData('contact_name', v)}
                error={errors.contact_name}
            />
            <TextField
                id="phone"
                label={t('Phone')}
                value={data.phone}
                onChange={(v) => setData('phone', v)}
                error={errors.phone}
            />
            <TextField
                id="email"
                label={t('Email')}
                value={data.email}
                onChange={(v) => setData('email', v)}
                error={errors.email}
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