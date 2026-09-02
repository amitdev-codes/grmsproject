import { Select2Field, TextField } from '@/components/form-fields';
import { FormLayout } from '@/components/form-layout';
import { useTranslation } from '@/hooks/use-translation';
import { rules, validateForm } from '@/lib/validation';
import { useForm } from '@inertiajs/react';
import { Layers } from 'lucide-react';
import { route } from 'ziggy-js';
import type { Section } from './Columns';

interface LookupOption {
    id: number;
    name: string;
}

interface SectionFormProps {
    section: Section | null;
    divisions: LookupOption[];
}

interface FormValues {
    code: string;
    division_id: string;
    name: string;
    name_st: string;
}

export default function Form({ section, divisions }: SectionFormProps) {
    const isEdit = !!section;
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
        code: section?.code ?? '',
        division_id: section?.division_id ? String(section.division_id) : '',
        name: section?.name ?? '',
        name_st: section?.name_st ?? '',
    });

    const submit = (e: React.SyntheticEvent) => {
        e.preventDefault();

        const clientErrors = validateForm(data, {
            code: [rules.required()],
            division_id: [rules.required()],
            name: [rules.required()],
        });

        if (Object.keys(clientErrors).length > 0) {
            setError(clientErrors as Record<keyof FormValues, string>);
            return;
        }

        clearErrors();

        const url = isEdit
            ? route('sections.update', section!.id)
            : route('sections.store');

        transform((formData) => ({
            ...formData,
            ...(isEdit ? { _method: 'put' } : {}),
        }));

        post(url, { preserveScroll: true });
    };

    const divisionOptions = divisions.map((division) => ({
        value: String(division.id),
        label: division.name,
    }));

    return (
        <FormLayout
            title={isEdit ? t('Edit Section') : t('Create Section')}
            description={
                isEdit
                    ? t("Update :name's details.", { name: section!.name })
                    : t('Add a new section to the system.')
            }
            breadcrumbs={[
                {
                    label: t('Sections'),
                    icon: Layers,
                    href: route('sections.index'),
                },
                { label: isEdit ? t('Edit Section') : t('Create Section') },
            ]}
            onSubmit={submit}
            processing={processing}
            submitLabel={isEdit ? t('Save changes') : t('Create Section')}
        >
            <TextField
                id="code"
                required
                label={t('Code')}
                value={data.code}
                onChange={(v) => setData('code', v)}
                error={errors.code}
            />
            <Select2Field
                id="division_id"
                required
                label={t('Division')}
                value={data.division_id}
                onChange={(v) => setData('division_id', v)}
                options={divisionOptions}
                placeholder={t('Select a division')}
                error={errors.division_id}
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
