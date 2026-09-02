import { TextField } from '@/components/form-fields';
import { FormLayout } from '@/components/form-layout';
import { useTranslation } from '@/hooks/use-translation';
import { rules, validateForm } from '@/lib/validation';
import { useForm } from '@inertiajs/react';
import { MapPin } from 'lucide-react';
import { route } from 'ziggy-js';
import type { District } from './Columns';

interface DistrictFormProps {
    district: District | null;
}

interface FormValues {
    code: string;
    name: string;
    name_st: string;
}

export default function Form({ district }: DistrictFormProps) {
    const isEdit = !!district;
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
        code: district?.code ?? '',
        name: district?.name ?? '',
        name_st: district?.name_st ?? '',
    });

    const submit = (e: React.SyntheticEvent) => {
        e.preventDefault();

        const clientErrors = validateForm(data, {
            code: [rules.required()],
            name: [rules.required()],
        });

        if (Object.keys(clientErrors).length > 0) {
            setError(clientErrors as Record<keyof FormValues, string>);

            return;
        }

        clearErrors();

        const url = isEdit
            ? route('districts.update', district!.id)
            : route('districts.store');

        transform((formData) => ({
            ...formData,
            ...(isEdit ? { _method: 'put' } : {}),
        }));

        post(url, { preserveScroll: true });
    };

    return (
        <FormLayout
            title={isEdit ? t('Edit District') : t('Create District')}
            description={
                isEdit
                    ? t("Update :name's details.", { name: district!.name })
                    : t('Add a new district to the system.')
            }
            breadcrumbs={[
                {
                    label: t('Districts'),
                    icon: MapPin,
                    href: route('districts.index'),
                },
                { label: isEdit ? t('Edit District') : t('Create District') },
            ]}
            onSubmit={submit}
            processing={processing}
            submitLabel={isEdit ? t('Save changes') : t('Create District')}
        >
            <TextField
                id="code"
                required
                label={t('Code')}
                value={data.code}
                onChange={(v) => setData('code', v)}
                error={errors.code}
            />
            <TextField
                id="name"
                required
                label={t('Name')}
                value={data.name}
                onChange={(v) => setData('name', v)}
                error={errors.name}
            />
            <TextField
                id="name_st"
                label={t('Name St')}
                value={data.name_st}
                onChange={(v) => setData('name_st', v)}
                error={errors.name_st}
            />
        </FormLayout>
    );
}
