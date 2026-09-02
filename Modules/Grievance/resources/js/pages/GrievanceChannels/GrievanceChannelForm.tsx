import { Select2Field, StatusField, TextField } from '@/components/form-fields';
import { FormLayout } from '@/components/form-layout';
import { useTranslation } from '@/hooks/use-translation';
import { rules, validateForm } from '@/lib/validation';
import { useForm } from '@inertiajs/react';
import { Radio } from 'lucide-react';
import { route } from 'ziggy-js';
import type { GrievanceChannel } from './columns';

interface GrievanceChannelFormProps {
    grievanceChannel: GrievanceChannel | null;
}

interface FormValues {
    code: string;
    name: string;
    is_active: '1' | '0';
}

/** Known channel codes — matches the values used across the grievance intake system */
const codeOptions = [
    { value: 'web', label: 'Web' },
    { value: 'mobile_app', label: 'Mobile App' },
    { value: 'sms', label: 'SMS' },
    { value: 'ussd', label: 'USSD' },
    { value: 'whatsapp', label: 'WhatsApp' },
    { value: 'helpdesk', label: 'Helpdesk' },
    { value: 'box', label: 'Suggestion Box' },
    { value: 'grc', label: 'GRC' },
    { value: 'chief', label: "Chief's Office" },
    { value: 'social_media', label: 'Social Media' },
];

export default function Form({ grievanceChannel }: GrievanceChannelFormProps) {
    const isEdit = !!grievanceChannel;
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
        code: grievanceChannel?.code ?? '',
        name: grievanceChannel?.name ?? '',
        is_active: grievanceChannel
            ? grievanceChannel.is_active
                ? '1'
                : '0'
            : '1',
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
            ? route('grievance-channels.update', grievanceChannel!.id)
            : route('grievance-channels.store');

        transform((formData) => ({
            ...formData,
            ...(isEdit ? { _method: 'put' } : {}),
        }));

        post(url, { preserveScroll: true });
    };

    return (
        <FormLayout
            title={
                isEdit
                    ? t('Edit Grievance Channel')
                    : t('Create Grievance Channel')
            }
            description={
                isEdit
                    ? t("Update :name's details.", {
                          name: grievanceChannel!.name,
                      })
                    : t('Add a new Grievance Channel to the system.')
            }
            breadcrumbs={[
                {
                    label: t('Grievance Channels'),
                    icon: Radio,
                    href: route('grievance-channels.index'),
                },
                {
                    label: isEdit
                        ? t('Edit Grievance Channel')
                        : t('Create Grievance Channel'),
                },
            ]}
            onSubmit={submit}
            processing={processing}
            submitLabel={
                isEdit ? t('Save changes') : t('Create Grievance Channel')
            }
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
