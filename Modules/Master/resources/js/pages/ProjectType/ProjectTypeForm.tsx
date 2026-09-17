import { NumberField, Select2Field, StatusField, TextField } from '@/components/form-fields';
import { FormLayout } from '@/components/form-layout';
import { useTranslation } from '@/hooks/use-translation';
import { rules, validateForm } from '@/lib/validation';
import { useForm } from '@inertiajs/react';
import { FolderKanban } from 'lucide-react';
import { route } from 'ziggy-js';
import type { ProjectType } from './Columns';

interface ProjectTypeFormProps {
    projectType: ProjectType | null;
}

interface FormValues {
    code: string;
    name: string;
    name_st: string;
    sort_order: string;
    is_active: '1' | '0';
}

export default function Form({ projectType }: ProjectTypeFormProps) {
    const isEdit = !!projectType;
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
        code: projectType?.code ?? '',
        name: projectType?.name ?? '',
        name_st: projectType?.name_st ?? '',
        sort_order: projectType?.sort_order?.toString() ?? '0',
        is_active: projectType ? (projectType.is_active ? '1' : '0') : '1',
    });

    const submit = (e: React.SyntheticEvent) => {
        e.preventDefault();

        const clientErrors = validateForm(data, {
            code: [rules.required()],
            name: [rules.required()],
            sort_order: [rules.required()],
        });

        if (Object.keys(clientErrors).length > 0) {
            setError(clientErrors as Record<keyof FormValues, string>);

            return;
        }

        clearErrors();

        const url = isEdit
            ? route('project-types.update', projectType!.id)
            : route('project-types.store');

        transform((formData) => ({
            ...formData,
            ...(isEdit ? { _method: 'put' } : {}),
        }));

        post(url, { preserveScroll: true });
    };

    return (
        <FormLayout
            title={isEdit ? t('Edit Project Type') : t('Create Project Type')}
            description={
                isEdit
                    ? t("Update :name's details.", { name: projectType!.name })
                    : t('Add a new project type to the system.')
            }
            breadcrumbs={[
                {
                    label: t('Project Types'),
                    icon: FolderKanban,
                    href: route('project-types.index'),
                },
                { label: isEdit ? t('Edit Project Type') : t('Create Project Type') },
            ]}
            onSubmit={submit}
            processing={processing}
            submitLabel={isEdit ? t('Save changes') : t('Create Project Type')}
        >
            <TextField
                id="code"
                required
                label={t('Code')}
                value={data.code}
                onChange={(v) => setData('code', v.toUpperCase())}
                error={errors.code}
                placeholder={t('e.g. ROAD, BRIDGE, DRAIN')}
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
            <NumberField
                id="sort_order"
                required
                label={t('Sort Order')}
                value={data.sort_order}
                onChange={(v) => setData('sort_order', v)}
                error={errors.sort_order}
                placeholder={t('0')}
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