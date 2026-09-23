import { Select2Field, StatusField, TextField } from '@/components/form-fields';
import { FormLayout } from '@/components/form-layout';
import { useForm } from '@inertiajs/react';
import { Mail } from 'lucide-react';
import { route } from 'ziggy-js';
import type { EmailSetting } from './Columns';

export default function Form({ setting }: { setting: EmailSetting | null }) {
    const edit = Boolean(setting);
    const { data, setData, post, transform, processing, errors } = useForm({
        name: setting?.name ?? '',
        mailer: setting?.mailer ?? 'smtp',
        host: setting?.host ?? '',
        port: String(setting?.port ?? 2525),
        encryption: setting?.encryption ?? 'tls',
        username: setting?.username ?? '',
        password: '',
        from_address: setting?.from_address ?? '',
        from_name: setting?.from_name ?? '',
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
                ? route('settings.email.update', setting!.id)
                : route('settings.email.store'),
            { preserveScroll: true },
        );
    };

    return (
        <FormLayout
            title={edit ? 'Edit Email Setting' : 'Create Email Setting'}
            description="Configure an email delivery provider."
            breadcrumbs={[
                {
                    label: 'Email Settings',
                    icon: Mail,
                    href: route('settings.email.index'),
                },
            ]}
            onSubmit={submit}
            processing={processing}
            submitLabel={edit ? 'Save changes' : 'Create Email Setting'}
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
                id="mailer"
                required
                label="Mailer"
                value={data.mailer}
                onChange={(v) => setData('mailer', v)}
                error={errors.mailer}
            />
            <TextField
                id="host"
                required
                label="SMTP host"
                value={data.host}
                onChange={(v) => setData('host', v)}
                error={errors.host}
            />
            <TextField
                id="port"
                required
                label="Port"
                value={data.port}
                onChange={(v) => setData('port', v)}
                error={errors.port}
            />
            <Select2Field
                id="encryption"
                label="Encryption"
                value={data.encryption}
                onChange={(v) => setData('encryption', v)}
                options={[
                    { value: 'tls', label: 'TLS' },
                    { value: 'ssl', label: 'SSL' },
                ]}
                error={errors.encryption}
            />
            <TextField
                id="username"
                label="Username"
                value={data.username}
                onChange={(v) => setData('username', v)}
                error={errors.username}
            />
            <TextField
                id="password"
                type="password"
                label={edit ? 'Password (leave blank to keep)' : 'Password'}
                value={data.password}
                onChange={(v) => setData('password', v)}
                error={errors.password}
            />
            <TextField
                id="from_address"
                required
                label="From address"
                value={data.from_address}
                onChange={(v) => setData('from_address', v)}
                error={errors.from_address}
            />
            <TextField
                id="from_name"
                required
                label="From name"
                value={data.from_name}
                onChange={(v) => setData('from_name', v)}
                error={errors.from_name}
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
