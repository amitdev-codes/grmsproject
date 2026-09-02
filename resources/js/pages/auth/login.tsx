import { Form, Head } from '@inertiajs/react';
import { MapPinned } from 'lucide-react';
import { useState } from 'react';
import InputError from '@/components/input-error';
import PasswordInput from '@/components/password-input';
import TextLink from '@/components/text-link';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { register } from '@/routes';
import { store } from '@/routes/login';
import { request } from '@/routes/password';

import {
    LanguageContext,
    translations,
    useI18n,
    LanguageToggle,
    PRODUCT_NAME

} from '@modules/Frontend/pages/site-shared';
import type {Lang} from '@modules/Frontend/pages/site-shared';

type Props = {
    status?: string;
    canResetPassword: boolean;
    appLogoUrl?: string | null;
};

function LoginContent({ status, canResetPassword, appLogoUrl = null }: Props) {
    const { t } = useI18n();

    return (
        <>
            <Head title={t.auth.login.headTitle} />

            <div className="relative">
                {/* Language toggle — pinned to the top-right corner */}
                <div className="absolute top-0 right-0">
                    <LanguageToggle />
                </div>

                {/* Icon + GRMS — same line, like the navbar lockup */}
                <div className="flex flex-col items-center gap-2">
                    <div className="flex items-center gap-2.5">
                        {appLogoUrl ? (
                            <img
                                src={appLogoUrl}
                                alt={PRODUCT_NAME}
                                className="h-9 w-9 rounded-sm object-contain"
                            />
                        ) : (
                            <div
                                className="flex h-9 w-9 shrink-0 items-center justify-center rounded-sm"
                                style={{ background: '#14213D' }}
                            >
                                <MapPinned
                                    className="h-4.5 w-4.5"
                                    style={{ color: '#EFEFE6' }}
                                />
                            </div>
                        )}
                        <span className="font-display text-lg font-semibold tracking-tight text-foreground">
                            {PRODUCT_NAME}
                        </span>
                    </div>
                </div>

                <div className="mt-4 space-y-1 text-center">
                    <h1 className="text-card-title font-semibold text-foreground">
                        {t.auth.login.title}
                    </h1>
                    <p className="text-card-description text-muted-foreground">
                        {t.auth.login.subtitle}
                    </p>
                </div>
            </div>

            <Form
                {...store.form()}
                resetOnSuccess={['password']}
                className="mt-6 flex flex-col gap-6"
            >
                {({ processing, errors }) => (
                    <>
                        <div className="grid gap-6">
                            <div className="grid gap-2">
                                <Label htmlFor="email">
                                    {t.auth.login.emailLabel}
                                </Label>
                                <Input
                                    id="email"
                                    type="email"
                                    name="email"
                                    required
                                    autoFocus
                                    tabIndex={1}
                                    autoComplete="email"
                                    placeholder="email@example.com"
                                />
                                <InputError message={errors.email} />
                            </div>

                            <div className="grid gap-2">
                                <div className="flex items-center">
                                    <Label htmlFor="password">
                                        {t.auth.login.passwordLabel}
                                    </Label>
                                    {canResetPassword && (
                                        <TextLink
                                            href={request()}
                                            className="ml-auto text-sm"
                                            tabIndex={5}
                                        >
                                            {t.auth.login.forgotPassword}
                                        </TextLink>
                                    )}
                                </div>
                                <PasswordInput
                                    id="password"
                                    name="password"
                                    required
                                    tabIndex={2}
                                    autoComplete="current-password"
                                    placeholder={t.auth.login.passwordLabel}
                                />
                                <InputError message={errors.password} />
                            </div>

                            <div className="flex items-center space-x-3">
                                <Checkbox
                                    id="remember"
                                    name="remember"
                                    tabIndex={3}
                                />
                                <Label htmlFor="remember">
                                    {t.auth.login.rememberMe}
                                </Label>
                            </div>

                            <Button
                                type="submit"
                                className="mt-4 w-full bg-primary text-primary-foreground hover:bg-primary/90"
                                tabIndex={4}
                                disabled={processing}
                                data-test="login-button"
                            >
                                {processing && <Spinner />}
                                {t.auth.login.submit}
                            </Button>
                        </div>

                        <div className="text-center text-sm text-muted-foreground">
                            {t.auth.login.noAccount}{' '}
                            <TextLink href={register()} tabIndex={5}>
                                {t.auth.login.signUp}
                            </TextLink>
                        </div>
                    </>
                )}
            </Form>

            {status && (
                <div
                    className="mt-4 text-center text-sm font-medium"
                    style={{ color: 'var(--success)' }}
                >
                    {status}
                </div>
            )}
        </>
    );
}

export default function Login(props: Props) {
    const [lang, setLang] = useState<Lang>('en');

    const langValue = {
        lang,
        t: translations[lang],
        toggle: () => setLang((v) => (v === 'en' ? 'st' : 'en')),
    };

    return (
        <LanguageContext.Provider value={langValue}>
            <LoginContent {...props} />
        </LanguageContext.Provider>
    );
}
