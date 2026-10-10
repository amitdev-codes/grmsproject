import {
    NumberField,
    Select2Field,
    TextField,
    TextareaField,
} from '@/components/form-fields';
import { FormLayout } from '@/components/form-layout';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { useForm } from '@inertiajs/react';
import { ShieldCheck } from 'lucide-react';
import { route } from 'ziggy-js';

interface Settings {
    lodging_requests_per_minute: number;
    duplicate_window_hours: number;
    whitelisted_ip_addresses: string[];
    blacklisted_ip_addresses: string[];
    captcha_provider: 'local' | 'cloudflare_turnstile';
    cloudflare_site_key: string | null;
    has_cloudflare_secret_key: boolean;
}

interface Props {
    settings: Settings;
}

interface FormValues {
    lodging_requests_per_minute: string;
    duplicate_window_hours: string;
    whitelisted_ip_addresses: string;
    blacklisted_ip_addresses: string;
    captcha_provider: 'local' | 'cloudflare_turnstile';
    cloudflare_site_key: string;
    cloudflare_secret_key: string;
}

export default function GrievanceIntakeSecuritySettings({ settings }: Props) {
    const { data, setData, put, processing, errors } = useForm<FormValues>({
        lodging_requests_per_minute: String(
            settings.lodging_requests_per_minute,
        ),
        duplicate_window_hours: String(settings.duplicate_window_hours),
        whitelisted_ip_addresses: settings.whitelisted_ip_addresses.join('\n'),
        blacklisted_ip_addresses: settings.blacklisted_ip_addresses.join('\n'),
        captcha_provider: settings.captcha_provider,
        cloudflare_site_key: settings.cloudflare_site_key ?? '',
        cloudflare_secret_key: '',
    });

    const submit = (event: React.SyntheticEvent) => {
        event.preventDefault();
        put(route('settings.security.update'), { preserveScroll: true });
    };

    return (
        <FormLayout
            title="Grievance Intake Security"
            description="Configure public grievance submission limits, IP controls, duplicate detection, and bot verification."
            breadcrumbs={[
                { label: 'Settings' },
                { label: 'Security' },
                { label: 'Grievance Intake' },
            ]}
            onSubmit={submit}
            processing={processing}
            submitLabel="Save security settings"
        >
            <NumberField
                id="lodging_requests_per_minute"
                label="Submissions per IP per minute"
                required
                min={1}
                max={1000}
                value={data.lodging_requests_per_minute}
                onChange={(value) =>
                    setData('lodging_requests_per_minute', value)
                }
                error={errors.lodging_requests_per_minute}
            />
            <NumberField
                id="duplicate_window_hours"
                label="Duplicate detection window (hours)"
                required
                min={0}
                max={8760}
                value={data.duplicate_window_hours}
                onChange={(value) => setData('duplicate_window_hours', value)}
                error={errors.duplicate_window_hours}
            />
            <TextareaField
                id="whitelisted_ip_addresses"
                label="Whitelisted IP addresses"
                value={data.whitelisted_ip_addresses}
                onChange={(value) => setData('whitelisted_ip_addresses', value)}
                placeholder={
                    'One IP address per line\nWhitelisted addresses bypass the submission rate limit.'
                }
                error={errors.whitelisted_ip_addresses}
            />
            <TextareaField
                id="blacklisted_ip_addresses"
                label="Blacklisted IP addresses"
                value={data.blacklisted_ip_addresses}
                onChange={(value) => setData('blacklisted_ip_addresses', value)}
                placeholder="One IP address per line. Blocked addresses cannot lodge grievances."
                error={errors.blacklisted_ip_addresses}
            />
            <Select2Field
                id="captcha_provider"
                label="Bot verification provider"
                required
                value={data.captcha_provider}
                onChange={(value) => {
                    if (value === 'local' || value === 'cloudflare_turnstile') {
                        setData('captcha_provider', value);
                    }
                }}
                options={[
                    {
                        value: 'local',
                        label: 'Built-in math challenge (default)',
                    },
                    {
                        value: 'cloudflare_turnstile',
                        label: 'Cloudflare Turnstile',
                    },
                ]}
                error={errors.captcha_provider}
            />
            {data.captcha_provider === 'cloudflare_turnstile' && (
                <>
                    <Alert className="sm:col-span-2">
                        <ShieldCheck className="size-4" />
                        <AlertTitle>Cloudflare Turnstile</AlertTitle>
                        <AlertDescription>
                            Create a Turnstile widget in Cloudflare, then enter
                            its site key and secret key. The built-in challenge
                            remains active until Cloudflare is selected and
                            configured.
                        </AlertDescription>
                    </Alert>
                    <TextField
                        id="cloudflare_site_key"
                        label="Cloudflare site key"
                        required
                        value={data.cloudflare_site_key}
                        onChange={(value) =>
                            setData('cloudflare_site_key', value)
                        }
                        error={errors.cloudflare_site_key}
                    />
                    <TextField
                        id="cloudflare_secret_key"
                        label="Cloudflare secret key"
                        type="password"
                        value={data.cloudflare_secret_key}
                        onChange={(value) =>
                            setData('cloudflare_secret_key', value)
                        }
                        placeholder={
                            settings.has_cloudflare_secret_key
                                ? 'Saved securely; leave blank to keep current key'
                                : 'Enter the Turnstile secret key'
                        }
                        error={errors.cloudflare_secret_key}
                    />
                </>
            )}
        </FormLayout>
    );
}
