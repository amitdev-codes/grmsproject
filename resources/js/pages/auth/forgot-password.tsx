import { Form, Head } from '@inertiajs/react';
import { useState } from 'react';
import InputError from '@/components/input-error';
import TextLink from '@/components/text-link';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { login } from '@/routes';
import { email } from '@/routes/password';

import {
    LanguageContext,
    translations,
    useI18n,
    LanguageToggle,
    PRODUCT_NAME,
    useApplicationSettings,
} from '@modules/Frontend/pages/site-shared';
import type { Lang } from '@modules/Frontend/pages/site-shared';

type Props = {
    status?: string;
};

function ForgotPasswordContent({ status }: Props) {
    const { t } = useI18n();
    const settings = useApplicationSettings();

    return (
        <>
            <Head title={t.auth.forgotPassword.headTitle} />

            <div className="relative">
                <div className="absolute top-0 right-0">
                    <LanguageToggle />
                </div>

                <div className="flex flex-col items-center gap-2">
                    <div className="flex items-center gap-2.5">
                        <img
                            src="/logo.png"
                            alt={settings.short_name ?? PRODUCT_NAME}
                            className="h-9 w-9 rounded-sm object-contain"
                        />
                        <span className="font-display text-lg font-semibold tracking-tight text-foreground">
                            {settings.short_name ??
                                settings.project_name ??
                                PRODUCT_NAME}
                        </span>
                    </div>
                </div>

                <div className="mt-4 space-y-1 text-center">
                    <h1 className="text-card-title font-semibold text-foreground">
                        {t.auth.forgotPassword.title}
                    </h1>
                    <p className="text-card-description text-muted-foreground">
                        {t.auth.forgotPassword.subtitle}
                    </p>
                </div>
            </div>

            {status && (
                <div
                    className="mt-4 text-center text-sm font-medium"
                    style={{ color: 'var(--success)' }}
                >
                    {status}
                </div>
            )}

            <Form
                {...email.form()}
                className="mt-6 flex flex-col gap-6"
            >
                {({ processing, errors }) => (
                    <div className="grid gap-6">
                        <div className="grid gap-2">
                            <Label htmlFor="email">
                                {t.auth.forgotPassword.emailLabel}
                            </Label>
                            <Input
                                id="email"
                                type="email"
                                name="email"
                                autoComplete="off"
                                autoFocus
                                tabIndex={1}
                                placeholder="email@example.com"
                            />
                            <InputError message={errors.email} />
                        </div>

                        <Button
                            type="submit"
                            className="mt-4 w-full bg-primary text-primary-foreground hover:bg-primary/90"
                            tabIndex={2}
                            disabled={processing}
                            data-test="email-password-reset-link-button"
                        >
                            {processing && <Spinner />}
                            {t.auth.forgotPassword.submit}
                        </Button>
                    </div>
                )}
            </Form>

            <div className="mt-6 text-center text-sm text-muted-foreground">
                {t.auth.forgotPassword.backToLogin}{' '}
                <TextLink href={login()} tabIndex={3}>
                    {t.auth.login.submit}
                </TextLink>
            </div>
        </>
    );
}

export default function ForgotPassword(props: Props) {
    const [lang, setLang] = useState<Lang>('en');

    const langValue = {
        lang,
        t: translations[lang],
        toggle: () => setLang((v) => (v === 'en' ? 'st' : 'en')),
    };

    return (
        <LanguageContext.Provider value={langValue}>
            <ForgotPasswordContent {...props} />
        </LanguageContext.Provider>
    );
}

ForgotPassword.layout = {
    title: 'Forgot password',
    description: 'Enter your email to receive a password reset link',
};
