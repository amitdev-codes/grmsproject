// resources/js/Pages/Settings/ApplicationSettings/Form.tsx
import { FileDropzone } from '@/components/file-dropzone';
import {
    NumberField,
    Select2Field,
    StatusField,
    TextareaField,
    TextField,
} from '@/components/form-fields';
import { FormLayout } from '@/components/form-layout';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { useTranslation } from '@/hooks/use-translation';
import { cn } from '@/lib/utils';
import { rules, validateForm } from '@/lib/validation';
import { useForm } from '@inertiajs/react';
import { Settings as SettingsIcon } from 'lucide-react';
import { useState } from 'react';
import { route } from 'ziggy-js';

interface LookupOption {
    id: number;
    name: string;
}

interface ApplicationSettingsModel {
    id: number;
    project_name: string;
    project_slug: string;
    short_name: string | null;
    tagline: string | null;
    description: string | null;
    logo_path: string | null;
    favicon_path: string | null;
    primary_color: string | null;
    secondary_color: string | null;
    address_line: string | null;
    district_id: number | null;
    latitude: string | null;
    longitude: string | null;
    email: string | null;
    phone: string | null;
    whatsapp: string | null;
    social_links: Record<string, string> | null;
    seo_title: string | null;
    seo_description: string | null;
    seo_keywords: string | null;
    og_image_path: string | null;
    seo_meta: Record<string, string> | null;
    sla_level1_hours: number;
    sla_level2_days: number;
    sla_level3_days: number;
    default_locale: 'en' | 'st';
    support_hours: string | null;
    anonymous_submissions_enabled: boolean;
    maintenance_mode: boolean;
    footer_text: string | null;
    privacy_policy_url: string | null;
    terms_url: string | null;
}

interface Props {
    settings: ApplicationSettingsModel;
    districts: LookupOption[];
}

interface FormValues {
    project_name: string;
    project_slug: string;
    short_name: string;
    tagline: string;
    description: string;
    logo: File[];
    favicon: File[];
    primary_color: string;
    secondary_color: string;
    address_line: string;
    district_id: string;
    latitude: string;
    longitude: string;
    email: string;
    phone: string;
    whatsapp: string;
    facebook: string;
    linkedin: string;
    youtube: string;
    twitter: string;
    instagram: string;
    seo_title: string;
    seo_description: string;
    seo_keywords: string;
    og_image: File[];
    robots: string;
    twitter_card: string;
    canonical_url: string;
    sla_level1_hours: string;
    sla_level2_days: string;
    sla_level3_days: string;
    default_locale: 'en' | 'st';
    support_hours: string;
    anonymous_submissions_enabled: '1' | '0';
    maintenance_mode: '1' | '0';
    footer_text: string;
    privacy_policy_url: string;
    terms_url: string;
}

const STEPS = [
    'Identity',
    'Branding',
    'Location',
    'Contact & Socials',
    'SEO',
    'GRMS Config',
    'Legal & Footer',
] as const;

export default function Form({ settings, districts }: Props) {
    const { t } = useTranslation();
    const [step, setStep] = useState(0);
    const isLastStep = step === STEPS.length - 1;

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
        project_name: settings.project_name ?? '',
        project_slug: settings.project_slug ?? '',
        short_name: settings.short_name ?? '',
        tagline: settings.tagline ?? '',
        description: settings.description ?? '',
        logo: [],
        favicon: [],
        primary_color: settings.primary_color ?? '',
        secondary_color: settings.secondary_color ?? '',
        address_line: settings.address_line ?? '',
        district_id: settings.district_id ? String(settings.district_id) : '',
        latitude: settings.latitude ?? '',
        longitude: settings.longitude ?? '',
        email: settings.email ?? '',
        phone: settings.phone ?? '',
        whatsapp: settings.whatsapp ?? '',
        facebook: settings.social_links?.facebook ?? '',
        linkedin: settings.social_links?.linkedin ?? '',
        youtube: settings.social_links?.youtube ?? '',
        twitter: settings.social_links?.twitter ?? '',
        instagram: settings.social_links?.instagram ?? '',
        seo_title: settings.seo_title ?? '',
        seo_description: settings.seo_description ?? '',
        seo_keywords: settings.seo_keywords ?? '',
        og_image: [],
        robots: settings.seo_meta?.robots ?? '',
        twitter_card: settings.seo_meta?.twitter_card ?? '',
        canonical_url: settings.seo_meta?.canonical_url ?? '',
        sla_level1_hours: String(settings.sla_level1_hours ?? 48),
        sla_level2_days: String(settings.sla_level2_days ?? 5),
        sla_level3_days: String(settings.sla_level3_days ?? 7),
        default_locale: settings.default_locale ?? 'en',
        support_hours: settings.support_hours ?? '',
        anonymous_submissions_enabled: settings.anonymous_submissions_enabled
            ? '1'
            : '0',
        maintenance_mode: settings.maintenance_mode ? '1' : '0',
        footer_text: settings.footer_text ?? '',
        privacy_policy_url: settings.privacy_policy_url ?? '',
        terms_url: settings.terms_url ?? '',
    });

    const goNext = () => setStep((s) => Math.min(s + 1, STEPS.length - 1));
    const goBack = () => setStep((s) => Math.max(s - 1, 0));

    const submit = (e: React.SyntheticEvent) => {
        e.preventDefault();

        const clientErrors = validateForm(data, {
            project_name: [rules.required()],
            project_slug: [rules.required()],
            sla_level1_hours: [rules.required()],
            sla_level2_days: [rules.required()],
            sla_level3_days: [rules.required()],
            default_locale: [rules.required()],
        });

        if (Object.keys(clientErrors).length > 0) {
            setError(clientErrors as Record<keyof FormValues, string>);

            return;
        }

        clearErrors();

        transform((formData) => ({
            project_name: formData.project_name,
            project_slug: formData.project_slug,
            short_name: formData.short_name || null,
            tagline: formData.tagline || null,
            description: formData.description || null,
            logo: formData.logo[0] ?? null,
            favicon: formData.favicon[0] ?? null,
            primary_color: formData.primary_color || null,
            secondary_color: formData.secondary_color || null,
            address_line: formData.address_line || null,
            district_id: formData.district_id || null,
            latitude: formData.latitude || null,
            longitude: formData.longitude || null,
            email: formData.email || null,
            phone: formData.phone || null,
            whatsapp: formData.whatsapp || null,
            social_links: {
                facebook: formData.facebook || null,
                linkedin: formData.linkedin || null,
                youtube: formData.youtube || null,
                twitter: formData.twitter || null,
                instagram: formData.instagram || null,
            },
            seo_title: formData.seo_title || null,
            seo_description: formData.seo_description || null,
            seo_keywords: formData.seo_keywords || null,
            og_image: formData.og_image[0] ?? null,
            seo_meta: {
                robots: formData.robots || null,
                twitter_card: formData.twitter_card || null,
                canonical_url: formData.canonical_url || null,
            },
            sla_level1_hours: formData.sla_level1_hours,
            sla_level2_days: formData.sla_level2_days,
            sla_level3_days: formData.sla_level3_days,
            default_locale: formData.default_locale,
            support_hours: formData.support_hours || null,
            anonymous_submissions_enabled:
                formData.anonymous_submissions_enabled === '1',
            maintenance_mode: formData.maintenance_mode === '1',
            footer_text: formData.footer_text || null,
            privacy_policy_url: formData.privacy_policy_url || null,
            terms_url: formData.terms_url || null,
            _method: 'patch',
        }));

        post(route('settings.application.update'), {
            forceFormData: true,
            preserveScroll: true,
        });
    };

    const districtOptions = districts.map((d) => ({
        value: String(d.id),
        label: d.name,
    }));
    // near the top of Form.tsx, after the useForm() call
    const fieldErrors = errors as Record<string, string | undefined>;

    return (
        <FormLayout
            title={t('Application Settings')}
            description={t(
                'Configure organization identity, contact channels, and site-wide behavior.',
            )}
            breadcrumbs={[
                { label: t('Application Settings'), icon: SettingsIcon },
            ]}
            onSubmit={submit}
            processing={processing}
            hideFooter
        >
            {/* Step indicator */}
            <div className="col-span-full flex flex-wrap gap-2">
                {STEPS.map((label, i) => (
                    <button
                        key={label}
                        type="button"
                        onClick={() => setStep(i)}
                        className="focus:outline-none"
                    >
                        <Badge
                            variant={i === step ? 'default' : 'outline'}
                            className={cn(
                                'cursor-pointer',
                                i < step && 'opacity-70',
                            )}
                        >
                            {i + 1}. {t(label)}
                        </Badge>
                    </button>
                ))}
            </div>

            {step === 0 && (
                <>
                    <TextField
                        id="project_name"
                        required
                        label={t('Project Name')}
                        value={data.project_name}
                        onChange={(v) => setData('project_name', v)}
                        error={errors.project_name}
                    />
                    <TextField
                        id="project_slug"
                        required
                        label={t('Slug')}
                        value={data.project_slug}
                        onChange={(v) => setData('project_slug', v)}
                        error={errors.project_slug}
                        placeholder="grms-lesotho"
                    />
                    <TextField
                        id="short_name"
                        label={t('Short Name')}
                        value={data.short_name}
                        onChange={(v) => setData('short_name', v)}
                        error={errors.short_name}
                        placeholder="GRMS"
                    />
                    <TextField
                        id="tagline"
                        label={t('Tagline')}
                        value={data.tagline}
                        onChange={(v) => setData('tagline', v)}
                        error={errors.tagline}
                    />
                    <TextareaField
                        id="description"
                        label={t('Description')}
                        value={data.description}
                        onChange={(v) => setData('description', v)}
                        error={errors.description}
                    />
                </>
            )}

            {step === 1 && (
                <>
                    <div className="flex flex-col gap-1.5 sm:col-span-2">
                        <Label>{t('Logo')}</Label>
                        <FileDropzone
                            value={data.logo}
                            onChange={(files) => setData('logo', files)}
                            existingPreviewUrl={settings.logo_path}
                            accept={{
                                'image/*': ['.png', '.jpg', '.jpeg', '.svg'],
                            }}
                            maxSizeMB={2}
                            helperText={t('PNG, JPG or SVG, up to 2MB')}
                        />
                        {errors.logo && (
                            <p className="text-sm text-destructive">
                                {errors.logo}
                            </p>
                        )}
                    </div>
                    <div className="flex flex-col gap-1.5 sm:col-span-2">
                        <Label>{t('Favicon')}</Label>
                        <FileDropzone
                            value={data.favicon}
                            onChange={(files) => setData('favicon', files)}
                            existingPreviewUrl={settings.favicon_path}
                            accept={{ 'image/*': ['.png', '.ico'] }}
                            maxSizeMB={0.5}
                            helperText={t('PNG or ICO, up to 512KB')}
                        />
                        {errors.favicon && (
                            <p className="text-sm text-destructive">
                                {errors.favicon}
                            </p>
                        )}
                    </div>
                    <TextField
                        id="primary_color"
                        label={t('Primary Color')}
                        value={data.primary_color}
                        onChange={(v) => setData('primary_color', v)}
                        error={errors.primary_color}
                        placeholder="#1D4ED8"
                    />
                    <TextField
                        id="secondary_color"
                        label={t('Secondary Color')}
                        value={data.secondary_color}
                        onChange={(v) => setData('secondary_color', v)}
                        error={errors.secondary_color}
                        placeholder="#64748B"
                    />
                </>
            )}

            {step === 2 && (
                <>
                    <TextField
                        id="address_line"
                        label={t('Address')}
                        value={data.address_line}
                        onChange={(v) => setData('address_line', v)}
                        error={errors.address_line}
                    />
                    <Select2Field
                        id="district_id"
                        label={t('District')}
                        value={data.district_id}
                        onChange={(v) => setData('district_id', v)}
                        options={districtOptions}
                        placeholder={t('Select a district')}
                        error={errors.district_id}
                    />
                    <div className="grid grid-cols-2 gap-3 sm:col-span-2">
                        <TextField
                            id="latitude"
                            label={t('Latitude')}
                            value={data.latitude}
                            onChange={(v) => setData('latitude', v)}
                            error={errors.latitude}
                            placeholder="-29.3167"
                        />
                        <TextField
                            id="longitude"
                            label={t('Longitude')}
                            value={data.longitude}
                            onChange={(v) => setData('longitude', v)}
                            error={errors.longitude}
                            placeholder="27.4833"
                        />
                    </div>
                </>
            )}

            {step === 3 && (
                <>
                    <TextField
                        id="email"
                        type="email"
                        label={t('Email')}
                        value={data.email}
                        onChange={(v) => setData('email', v)}
                        error={errors.email}
                    />
                    <TextField
                        id="phone"
                        label={t('Phone')}
                        value={data.phone}
                        onChange={(v) => setData('phone', v)}
                        error={errors.phone}
                    />
                    <TextField
                        id="whatsapp"
                        label={t('WhatsApp')}
                        value={data.whatsapp}
                        onChange={(v) => setData('whatsapp', v)}
                        error={errors.whatsapp}
                    />
                    <TextField
                        id="facebook"
                        label={t('Facebook URL')}
                        value={data.facebook}
                        onChange={(v) => setData('facebook', v)}
                        error={fieldErrors['social_links.facebook']}
                    />
                    <TextField
                        id="linkedin"
                        label={t('LinkedIn URL')}
                        value={data.linkedin}
                        onChange={(v) => setData('linkedin', v)}
                        error={fieldErrors['social_links.linkedin']}
                    />
                    <TextField
                        id="youtube"
                        label={t('YouTube URL')}
                        value={data.youtube}
                        onChange={(v) => setData('youtube', v)}
                        error={fieldErrors['social_links.youtube']}
                    />
                    <TextField
                        id="twitter"
                        label={t('X / Twitter URL')}
                        value={data.twitter}
                        onChange={(v) => setData('twitter', v)}
                        error={fieldErrors['social_links.twitter']}
                    />
                    <TextField
                        id="instagram"
                        label={t('Instagram URL')}
                        value={data.instagram}
                        onChange={(v) => setData('instagram', v)}
                        error={fieldErrors['social_links.instagram']}
                    />
                </>
            )}

            {step === 4 && (
                <>
                    <TextField
                        id="seo_title"
                        label={t('SEO Title')}
                        value={data.seo_title}
                        onChange={(v) => setData('seo_title', v)}
                        error={errors.seo_title}
                    />
                    <TextField
                        id="seo_keywords"
                        label={t('SEO Keywords')}
                        value={data.seo_keywords}
                        onChange={(v) => setData('seo_keywords', v)}
                        error={errors.seo_keywords}
                        placeholder={t('comma, separated, keywords')}
                    />
                    <TextareaField
                        id="seo_description"
                        label={t('SEO Description')}
                        value={data.seo_description}
                        onChange={(v) => setData('seo_description', v)}
                        error={errors.seo_description}
                    />
                    <div className="flex flex-col gap-1.5 sm:col-span-2">
                        <Label>{t('OG Image')}</Label>
                        <FileDropzone
                            value={data.og_image}
                            onChange={(files) => setData('og_image', files)}
                            existingPreviewUrl={settings.og_image_path}
                            accept={{ 'image/*': ['.png', '.jpg', '.jpeg'] }}
                            maxSizeMB={2}
                            helperText={t(
                                'Used when the site is shared on social media',
                            )}
                        />
                        {errors.og_image && (
                            <p className="text-sm text-destructive">
                                {errors.og_image}
                            </p>
                        )}
                    </div>
                    <TextField
                        id="robots"
                        label={t('Robots Meta')}
                        value={data.robots}
                        onChange={(v) => setData('robots', v)}
                        error={fieldErrors['seo_meta.robots']}
                        placeholder="index, follow"
                    />
                    <TextField
                        id="twitter_card"
                        label={t('Twitter Card Type')}
                        value={data.twitter_card}
                        onChange={(v) => setData('twitter_card', v)}
                        error={fieldErrors['seo_meta.twitter_card']}
                        placeholder="summary_large_image"
                    />
                    <TextField
                        id="canonical_url"
                        label={t('Canonical URL')}
                        value={data.canonical_url}
                        onChange={(v) => setData('canonical_url', v)}
                        error={fieldErrors['seo_meta.canonical_url']}
                    />
                </>
            )}

            {step === 5 && (
                <>
                    <NumberField
                        id="sla_level1_hours"

                        required
                        label={t('Level 1 SLA (hours)')}
                        value={data.sla_level1_hours}
                        onChange={(v) => setData('sla_level1_hours', v)}
                        error={errors.sla_level1_hours}
                    />
                    <NumberField
                        id="sla_level2_days"
                        required
                        label={t('Level 2 SLA (days)')}
                        value={data.sla_level2_days}
                        onChange={(v) => setData('sla_level2_days', v)}
                        error={errors.sla_level2_days}
                    />
                    <NumberField
                        id="sla_level3_days"
                        required
                        label={t('Level 3 SLA (days)')}
                        value={data.sla_level3_days}
                        onChange={(v) => setData('sla_level3_days', v)}
                        error={errors.sla_level3_days}
                    />
                    <Select2Field
                        id="default_locale"
                        required
                        label={t('Default Language')}
                        value={data.default_locale}
                        onChange={(v) =>
                            setData(
                                'default_locale',
                                v as FormValues['default_locale'],
                            )
                        }
                        options={[
                            { value: 'en', label: t('English') },
                            { value: 'st', label: t('Sesotho') },
                        ]}
                        error={errors.default_locale}
                    />
                    <TextField
                        id="support_hours"
                        label={t('Support Hours')}
                        value={data.support_hours}
                        onChange={(v) => setData('support_hours', v)}
                        error={errors.support_hours}
                        placeholder="Mon–Fri, 8am–4:30pm"
                    />
                    <StatusField
                        id="anonymous_submissions_enabled"
                        label={t('Anonymous Submissions')}
                        value={data.anonymous_submissions_enabled}
                        onChange={(v) =>
                            setData('anonymous_submissions_enabled', v)
                        }
                        error={errors.anonymous_submissions_enabled}
                        activeLabel={t('Enabled')}
                        inactiveLabel={t('Disabled')}
                    />
                    <StatusField
                        id="maintenance_mode"
                        label={t('Maintenance Mode')}
                        value={data.maintenance_mode}
                        onChange={(v) => setData('maintenance_mode', v)}
                        error={errors.maintenance_mode}
                        activeLabel={t('On')}
                        inactiveLabel={t('Off')}
                    />
                </>
            )}

            {step === 6 && (
                <>
                    <TextareaField
                        id="footer_text"
                        label={t('Footer Text')}
                        value={data.footer_text}
                        onChange={(v) => setData('footer_text', v)}
                        error={errors.footer_text}
                    />
                    <TextField
                        id="privacy_policy_url"
                        label={t('Privacy Policy URL')}
                        value={data.privacy_policy_url}
                        onChange={(v) => setData('privacy_policy_url', v)}
                        error={errors.privacy_policy_url}
                    />
                    <TextField
                        id="terms_url"
                        label={t('Terms URL')}
                        value={data.terms_url}
                        onChange={(v) => setData('terms_url', v)}
                        error={errors.terms_url}
                    />
                </>
            )}

            <div className="col-span-full flex items-center justify-between border-t pt-4">
                <Button
                    type="button"
                    variant="outline"
                    onClick={goBack}
                    disabled={step === 0}
                >
                    {t('Back')}
                </Button>
                {isLastStep ? (
                    <Button type="submit" disabled={processing}>
                        {processing ? t('Saving…') : t('Save changes')}
                    </Button>
                ) : (
                    <Button type="button" onClick={goNext}>
                        {t('Next')}
                    </Button>
                )}
            </div>
        </FormLayout>
    );
}
