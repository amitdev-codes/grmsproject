// resources/js/Pages/settings/Profile.tsx
import { FileDropzone } from '@/components/file-dropzone';
import {
    ConfirmPasswordField,
    PasswordField,
    TextField,
} from '@/components/form-fields';
import { FormLayout } from '@/components/form-layout';
import { Label } from '@/components/ui/label';
import { useTranslation } from '@/hooks/use-translation';
import { rules, validateForm } from '@/lib/validation';
import { edit } from '@/routes/profile';
import { useForm, usePage } from '@inertiajs/react';
import { UserCog } from 'lucide-react';
import { route } from 'ziggy-js';

interface AuthUser {
    id: number;
    name: string;
    username?: string | null;
    email: string;
    avatar_url?: string | null;
}

interface FormValues {
    username: string;
    email: string;
    current_password: string;
    password: string;
    password_confirmation: string;
    avatar: File[];
    remove_avatar: boolean;
}

export default function Profile() {
    const { auth } = usePage().props;
    const user = auth.user as AuthUser;
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
        reset,
    } = useForm<FormValues>({
        username: user.username ?? '',
        email: user.email ?? '',
        current_password: '',
        password: '',
        password_confirmation: '',
        avatar: [],
        remove_avatar: false,
    });

    const isChangingPassword = data.password.length > 0;

    const submit = (e: React.SyntheticEvent) => {
        e.preventDefault();

        const clientErrors = validateForm(data, {
            username: [rules.required()],
            email: [rules.required()],
            // Current password is only required when actually setting a new one.
            ...(isChangingPassword
                ? { current_password: [rules.required()] }
                : {}),
        });

        if (
            isChangingPassword &&
            data.password !== data.password_confirmation
        ) {
            clientErrors.password_confirmation = t('Passwords do not match.');
        }

        if (Object.keys(clientErrors).length > 0) {
            setError(clientErrors as Record<keyof FormValues, string>);

            return;
        }

        clearErrors();

        transform((formData) => ({
            ...formData,
            avatar: formData.avatar[0] ?? null,
            // Don't send password fields at all if the user isn't changing it —
            // keeps the backend from ever seeing an empty password attempt.
            ...(isChangingPassword
                ? {}
                : {
                      current_password: undefined,
                      password: undefined,
                      password_confirmation: undefined,
                  }),
            _method: 'patch',
        }));

        post(route('profile.update'), {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => {
                reset('current_password', 'password', 'password_confirmation');
            },
        });
    };

    return (
        <FormLayout
            title={t('Profile')}
            description={t('Update your username, email, and password.')}
            breadcrumbs={[{ label: t('Profile'), icon: UserCog, href: edit() }]}
            onSubmit={submit}
            processing={processing}
            submitLabel={t('Save changes')}
        >
            <div className="flex flex-col gap-1.5 sm:col-span-2">
                <Label>{t('Avatar')}</Label>
                <FileDropzone
                    value={data.avatar}
                    onChange={(files) => {
                        setData('avatar', files);

                        if (files.length > 0) {
                            setData('remove_avatar', false);
                        }
                    }}
                    existingPreviewUrl={
                        data.remove_avatar ? null : user.avatar_url
                    }
                    onRemoveExisting={() => setData('remove_avatar', true)}
                    accept={{ 'image/*': ['.png', '.jpg', '.jpeg'] }}
                    maxSizeMB={2}
                    helperText={t(
                        'PNG or JPG, up to 2MB — drag & drop or click to upload',
                    )}
                />
                {errors.avatar && (
                    <p className="text-sm text-destructive">{errors.avatar}</p>
                )}
            </div>

            <TextField
                id="username"
                required
                label={t('Username')}
                value={data.username}
                onChange={(v) => setData('username', v)}
                error={errors.username}
            />

            <TextField
                id="email"
                type="email"
                required
                label={t('Email')}
                value={data.email}
                onChange={(v) => setData('email', v)}
                error={errors.email}
            />

            <PasswordField
                id="current_password"
                label={t('Current password')}
                value={data.current_password}
                onChange={(v) => setData('current_password', v)}
                placeholder={t('Required only if changing your password')}
                error={errors.current_password}
                autoComplete="current-password"
            />

            <PasswordField
                id="password"
                label={t('New password (optional)')}
                value={data.password}
                onChange={(v) => setData('password', v)}
                placeholder={t('Leave blank to keep current password')}
                error={errors.password}
                autoComplete="new-password"
            />

            <ConfirmPasswordField
                id="password_confirmation"
                label={t('Confirm new password')}
                value={data.password_confirmation}
                onChange={(v) => setData('password_confirmation', v)}
                passwordValue={data.password}
                error={errors.password_confirmation}
                autoComplete="new-password"
            />
        </FormLayout>
    );
}

Profile.layout = {
    breadcrumbs: [{ title: 'Profile settings', href: edit() }],
};
