import { Form, Head } from '@inertiajs/react';
import { useState } from 'react';
import InputError from '@/components/input-error';
import PasswordInput from '@/components/password-input';
import TextLink from '@/components/text-link';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { login } from '@/routes';
import { store } from '@/routes/register';

import {
    LanguageContext,
    translations,
    useI18n,
    LanguageToggle,
} from '@modules/Frontend/pages/site-shared';
import type { Lang } from '@modules/Frontend/pages/site-shared';

function RegisterContent() {
    const { t } = useI18n();

    return (
        <>
            <Head title={t.auth.register.headTitle} />

            <div className="relative">
                <div className="absolute top-0 right-0">
                    <LanguageToggle />
                </div>

                <div className="space-y-1 pt-1 text-center">
                    <h1 className="text-card-title font-semibold text-foreground">
                        {t.auth.register.title}
                    </h1>
                    <p className="text-card-description text-muted-foreground">
                        {t.auth.register.subtitle}
                    </p>
                </div>
            </div>

            <Form
                {...store.form()}
                resetOnSuccess={['password', 'password_confirmation']}
                disableWhileProcessing
                className="mt-6 flex flex-col gap-6"
            >
                {({ processing, errors }) => (
                    <>
                        <div className="grid gap-6">
                            <div className="grid gap-2">
                                <Label htmlFor="name">
                                    {t.auth.register.nameLabel}
                                </Label>
                                <Input
                                    id="name"
                                    type="text"
                                    required
                                    autoFocus
                                    tabIndex={1}
                                    autoComplete="name"
                                    name="name"
                                    placeholder={t.auth.register.nameLabel}
                                />
                                <InputError message={errors.name} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="email">
                                    {t.auth.register.emailLabel}
                                </Label>
                                <Input
                                    id="email"
                                    type="email"
                                    required
                                    tabIndex={2}
                                    autoComplete="email"
                                    name="email"
                                    placeholder="email@example.com"
                                />
                                <InputError message={errors.email} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="password">
                                    {t.auth.register.passwordLabel}
                                </Label>
                                <PasswordInput
                                    id="password"
                                    required
                                    tabIndex={3}
                                    autoComplete="new-password"
                                    name="password"
                                    placeholder={t.auth.register.passwordLabel}
                                />
                                <InputError message={errors.password} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="password_confirmation">
                                    {t.auth.register.confirmPasswordLabel}
                                </Label>
                                <PasswordInput
                                    id="password_confirmation"
                                    required
                                    tabIndex={4}
                                    autoComplete="new-password"
                                    name="password_confirmation"
                                    placeholder={
                                        t.auth.register.confirmPasswordLabel
                                    }
                                />
                                <InputError
                                    message={errors.password_confirmation}
                                />
                            </div>

                            <Button
                                type="submit"
                                className="mt-2 w-full bg-primary text-primary-foreground hover:bg-primary/90"
                                tabIndex={5}
                                disabled={processing}
                                data-test="register-user-button"
                            >
                                {processing && <Spinner />}
                                {t.auth.register.submit}
                            </Button>
                        </div>

                        <div className="text-center text-sm text-muted-foreground">
                            {t.auth.register.haveAccount}{' '}
                            <TextLink href={login()} tabIndex={6}>
                                {t.auth.register.signIn}
                            </TextLink>
                        </div>
                    </>
                )}
            </Form>
        </>
    );
}

export default function Register() {
    const [lang, setLang] = useState<Lang>('en');

    const langValue = {
        lang,
        t: translations[lang],
        toggle: () => setLang((v) => (v === 'en' ? 'st' : 'en')),
    };

    return (
        <LanguageContext.Provider value={langValue}>
            <RegisterContent />
        </LanguageContext.Provider>
    );
}
