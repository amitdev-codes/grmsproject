import {
    NumberField,
    Select2Field,
    StatusField,
    TextField,
} from '@/components/form-fields';
import { FormLayout } from '@/components/form-layout';
import { useTranslation } from '@/hooks/use-translation';
import { rules, validateForm } from '@/lib/validation';
import { Link, useForm } from '@inertiajs/react';
import { icons as LucideIcons, Users } from 'lucide-react';
import { route } from 'ziggy-js';
import type { GrievanceCategory } from './columns';

interface GrievanceCategoryFormProps {
    grievanceCategory: GrievanceCategory | null;
}

interface FormValues {
    code: string;
    name_en: string;
    name_st: string;
    slug: string;
    icon: string;
    sort_order: string;
    is_sensitive: '1' | '0';
    is_active: '1' | '0';
}

/** All lucide-react icon names, sorted, as combobox options */
const iconOptions = Object.keys(LucideIcons)
    .sort()
    .map((name) => ({ value: name, label: name }));

export default function Form({
    grievanceCategory,
}: GrievanceCategoryFormProps) {
    const isEdit = !!grievanceCategory;
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
        code: grievanceCategory?.code ?? '',
        name_en: grievanceCategory?.name_en ?? '',
        name_st: grievanceCategory?.name_st ?? '',
        slug: grievanceCategory?.slug ?? '',
        icon: grievanceCategory?.icon ?? '',
        sort_order: grievanceCategory?.sort_order?.toString() ?? '0',
        is_sensitive: grievanceCategory
            ? grievanceCategory.is_sensitive
                ? '1'
                : '0'
            : '1',
        is_active: grievanceCategory
            ? grievanceCategory.is_active
                ? '1'
                : '0'
            : '1',
    });

    const submit = (e: React.SyntheticEvent) => {
        e.preventDefault();

        const clientErrors = validateForm(data, {
            code: [rules.required()],
            name_en: [rules.required()],
            name_st: [rules.required()],
            slug: [rules.required()],
            sort_order: [rules.required()],
        });

        if (Object.keys(clientErrors).length > 0) {
            setError(clientErrors as Record<keyof FormValues, string>);

            return;
        }

        clearErrors();

        const url = isEdit
            ? route('grievance-categories.update', grievanceCategory!.id)
            : route('grievance-categories.store');

        transform((formData) => ({
            ...formData,
            ...(isEdit ? { _method: 'put' } : {}),
        }));

        post(url, { preserveScroll: true });
    };

    // Preview component for whichever icon is currently selected
    const SelectedIcon = data.icon
        ? LucideIcons[data.icon as keyof typeof LucideIcons]
        : null;

    return (
        <FormLayout
            title={
                isEdit
                    ? t('Edit Grievance Category')
                    : t('Create Grievance Category')
            }
            description={
                isEdit
                    ? t("Update :name's details.", {
                          name:
                              grievanceCategory!.name_en ||
                              grievanceCategory!.name_st,
                      })
                    : t('Add a new Grievance Category to the system.')
            }
            breadcrumbs={[
                {
                    label: t('Grievance Categories'),
                    icon: Users,
                    href: route('grievance-categories.index'),
                },
                {
                    label: isEdit
                        ? t('Edit Grievance Category')
                        : t('Create Grievance Category'),
                },
            ]}
            onSubmit={submit}
            processing={processing}
            submitLabel={
                isEdit ? t('Save changes') : t('Create Grievance Category')
            }
        >
            <TextField
                id="code"
                required
                label={t('Code')}
                value={data.code}
                onChange={(v) => setData('code', v.toUpperCase())}
                error={errors.code}
                placeholder={t('e.g. LAND, WATER, ROAD')}
            />
            <TextField
                id="name_en"
                required
                label={t('Name')}
                value={data.name_en}
                onChange={(v) => setData('name_en', v)}
                error={errors.name_en}
            />
            <TextField
                id="name_st"
                required
                label={t('Name St')}
                value={data.name_st}
                onChange={(v) => setData('name_st', v)}
                error={errors.name_st}
            />
            <TextField
                id="slug"
                required
                label={t('Slug')}
                value={data.slug}
                onChange={(v) => setData('slug', v)}
                error={errors.slug}
            />

            {/* Icon picker — searchable list of lucide-react icon names */}
            <div className="space-y-1.5">
                <Select2Field
                    id="icon"
                    label={t('Icon')}
                    value={data.icon}
                    onChange={(v) => setData('icon', v)}
                    options={iconOptions}
                    placeholder={t('Select an icon')}
                    error={errors.icon}
                />
                <div className="flex items-center gap-2 text-sm text-muted-foreground">
                    {SelectedIcon && <SelectedIcon className="h-4 w-4" />}
                    <Link
                        href="https://lucide.dev/icons"
                        target="_blank"
                        rel="noreferrer"
                        className="underline underline-offset-2 hover:text-foreground"
                    >
                        {t('Browse all icons on lucide.dev')}
                    </Link>
                </div>
            </div>

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
                id="is_sensitive"
                label={t('Is Sensitive')}
                value={data.is_sensitive}
                onChange={(v) => setData('is_sensitive', v)}
                error={errors.is_sensitive}
                activeLabel={t('Sensitive')}
                inactiveLabel={t('Standard')}
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
