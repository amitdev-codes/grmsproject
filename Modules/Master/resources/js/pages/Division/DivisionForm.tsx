import { TextareaField, TextField } from '@/components/form-fields';
import { FormLayout } from '@/components/form-layout';
import { useTranslation } from '@/hooks/use-translation';
import { rules, validateForm } from '@/lib/validation';
import { useForm } from '@inertiajs/react';
import { Building2 } from 'lucide-react';
import { route } from 'ziggy-js';
import type { Division } from './Columns';

interface DivisionFormProps {
    division: Division | null;
}

interface FormValues {
    code: string;
    name: string;
    name_st: string;
    description: string;
}

export default function Form({ division }: DivisionFormProps) {
    const isEdit = !!division;
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
        code: division?.code ?? '',
        name: division?.name ?? '',
        name_st: division?.name_st ?? '',
        description: division?.description ?? '',
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
            ? route('divisions.update', division!.id)
            : route('divisions.store');

        transform((formData) => ({
            ...formData,
            ...(isEdit ? { _method: 'put' } : {}),
        }));

        post(url, { preserveScroll: true });
    };

    return (
        <FormLayout
            title={isEdit ? t('Edit Division') : t('Create Division')}
            description={
                isEdit
                    ? t("Update :name's details.", { name: division!.name })
                    : t('Add a new division to the system.')
            }
            breadcrumbs={[
                {
                    label: t('Divisions'),
                    icon: Building2,
                    href: route('divisions.index'),
                },
                { label: isEdit ? t('Edit Division') : t('Create Division') },
            ]}
            onSubmit={submit}
            processing={processing}
            submitLabel={isEdit ? t('Save changes') : t('Create Division')}
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
            <TextareaField
                id="description"
                label={t('Description')}
                value={data.description}
                onChange={(v) => setData('description', v)}
                error={errors.description}
            />
        </FormLayout>
    );
}
