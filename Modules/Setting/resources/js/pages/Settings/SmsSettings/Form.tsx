import { FormLayout } from '@/components/form-layout';
import { StatusField, TextField } from '@/components/form-fields';
import { useForm } from '@inertiajs/react';
import { MessageSquareText } from 'lucide-react';
import { route } from 'ziggy-js';
import type { SmsSetting } from './Columns';

export default function Form({ setting }: { setting: SmsSetting | null }) {
    const edit = Boolean(setting);
    const { data, setData, post, transform, processing, errors } = useForm({
        name: setting?.name ?? '',
        provider: setting?.provider ?? '',
        base_url: setting?.base_url ?? '',
        api_key: '',
        api_secret: '',
        sender_id: setting?.sender_id ?? '',
        default_country_code: setting?.default_country_code ?? '+266',
        is_active: setting?.is_active ? '1' : '0',
    });
    const submit = (event: React.SyntheticEvent) => {
        event.preventDefault();
        transform((values) => ({
            ...values,
            ...(edit ? { _method: 'put' } : {}),
        }));
        post(
            edit
                ? route('settings.sms.update', setting!.id)
                : route('settings.sms.store'),
            { preserveScroll: true },
        );
    };
    return (
        <FormLayout
            title={edit ? 'Edit SMS Setting' : 'Create SMS Setting'}
            description="Configure an SMS gateway provider."
            breadcrumbs={[
                {
                    label: 'SMS Settings',
                    icon: MessageSquareText,
                    href: route('settings.sms.index'),
                },
            ]}
            onSubmit={submit}
            processing={processing}
            submitLabel={edit ? 'Save changes' : 'Create SMS Setting'}
        >
            <TextField
                id="name"
                required
                label="Name"
                value={data.name}
                onChange={(v) => setData('name', v)}
                error={errors.name}
            />
            <TextField
                id="provider"
                required
                label="Provider"
                value={data.provider}
                onChange={(v) => setData('provider', v)}
                error={errors.provider}
                placeholder="e.g. Africa's Talking"
            />
            <TextField
                id="base_url"
                label="API base URL"
                value={data.base_url}
                onChange={(v) => setData('base_url', v)}
                error={errors.base_url}
            />
            <TextField
                id="api_key"
                label="API key"
                value={data.api_key}
                onChange={(v) => setData('api_key', v)}
                error={errors.api_key}
            />
            <TextField
                id="api_secret"
                type="password"
                label={edit ? 'API secret (leave blank to keep)' : 'API secret'}
                value={data.api_secret}
                onChange={(v) => setData('api_secret', v)}
                error={errors.api_secret}
            />
            <TextField
                id="sender_id"
                label="Sender ID"
                value={data.sender_id}
                onChange={(v) => setData('sender_id', v)}
                error={errors.sender_id}
            />
            <TextField
                id="default_country_code"
                required
                label="Default country code"
                value={data.default_country_code}
                onChange={(v) => setData('default_country_code', v)}
                error={errors.default_country_code}
            />
            <StatusField
                id="is_active"
                label="Status"
                value={data.is_active as '1' | '0'}
                onChange={(v) => setData('is_active', v as '1' | '0')}
                activeLabel="Active"
                inactiveLabel="Inactive"
                error={errors.is_active}
            />
        </FormLayout>
    );
}
