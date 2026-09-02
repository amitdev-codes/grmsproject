import { FileDropzone } from '@/components/file-dropzone';
import {
    ConfirmPasswordField,
    PasswordField,
    Select2Field,
    TextField,
    TextareaField,
    StatusField,
} from '@/components/form-fields';
import { PhoneField } from '@/components/form-fields/phone-field';
import { FormLayout } from '@/components/form-layout';
import { Label } from '@/components/ui/label';
import { useTranslation } from '@/hooks/use-translation';
import { rules, validateForm } from '@/lib/validation';
import { useForm } from '@inertiajs/react';
import axios from 'axios';
import { Users } from 'lucide-react';
import { useState } from 'react';
import { route } from 'ziggy-js';
import type { User } from './columns';


interface LookupOption {
    id: number;
    name: string;
}

interface UserFormProps {
    user: User | null;
    roles: string[];
    districts: LookupOption[];
    divisions: LookupOption[];
    sections: LookupOption[];
}

interface FormValues {
    name: string;
    username: string;
    email: string;
    phone: string;
    status: '1' | '0';
    password: string;
    password_confirmation: string;
    bio: string;
    district_id: string;
    division_id: string;
    section_id: string;
    role: string;
    avatar: File[];
    remove_avatar: boolean;
}

/** Local helper: LookupOption[] -> ComboboxOption[] */
function toOptions(items: LookupOption[]) {
    return items.map((item) => ({ value: String(item.id), label: item.name }));
}

export default function Form({
    user,
    roles,
    districts,
    divisions,
    sections: initialSections,
}: UserFormProps) {
    const isEdit = !!user;
    const { t } = useTranslation();
    const [sections, setSections] = useState<LookupOption[]>(initialSections);
    const [loadingSections, setLoadingSections] = useState(false);

    const {
        data,
        setData,
        post,
        transform,
        processing,
        errors,
        setError,
        clearErrors
    } = useForm<FormValues>({
        name: user?.name ?? '',
        username: user?.username ?? '',
        email: user?.email ?? '',
        phone: user?.phone ?? '',
        status: user ? (user.status ? '1' : '0') : '1',
        password: '',
        password_confirmation: '',
        bio: user?.bio ?? '',
        district_id: user?.district_id ? String(user.district_id) : '',
        division_id: user?.division_id ? String(user.division_id) : '',
        section_id: user?.section_id ? String(user.section_id) : '',
        role: user?.roles?.[0]?.name ?? '',
        avatar: [],
        remove_avatar: false,
    });



    const handleDivisionChange = async (value: string) => {
        setData((prev) => ({ ...prev, division_id: value, section_id: '' }));
        setSections([]);

        if (!value) {
            return;
        }

        setLoadingSections(true);

        try {
            const res = await axios.get<LookupOption[]>(
                route('locations.sections'),
                { params: { division_id: value } },
            );
            setSections(res.data);
        } finally {
            setLoadingSections(false);
        }
    };

    const submit = (e: React.SyntheticEvent) => {
        e.preventDefault();

        const clientErrors = validateForm(data, {
            name: [rules.required()],
            username: [rules.required()],
            email: [rules.required()],
            phone: [rules.required(), rules.phoneDigits(10)],
            role: [rules.required()],
            district_id: [rules.required()],
            division_id: [rules.required()],
            section_id: [rules.required()],
            ...(!isEdit ? { password: [rules.required()] } : {}),
        });

        if (Object.keys(clientErrors).length > 0) {
            setError(clientErrors as Record<keyof FormValues, string>);

            return;
        }

        clearErrors();

        const url = isEdit
            ? route('users.update', user!.id)
            : route('users.store');

        transform((formData) => ({
            ...formData,
            avatar: formData.avatar[0] ?? null,
            district_id: formData.district_id || null,
            division_id: formData.division_id || null,
            section_id: formData.section_id || null,
            ...(isEdit ? { _method: 'put' } : {}),
        }));

        post(url, { forceFormData: true, preserveScroll: true });
    };

    const roleOptions = roles.map((role) => ({ value: role, label: role }));


    return (
        <FormLayout
            title={isEdit ? t('Edit User') : t('Create User')}
            description={
                isEdit
                    ? t("Update :name's details.", {
                          name: user!.username || user!.name,
                      })
                    : t('Add a new user to the system.')
            }
            breadcrumbs={[
                { label: t('Users'), icon: Users, href: route('users.index') },
                { label: isEdit ? t('Edit User') : t('Create User') },
            ]}
            onSubmit={submit}
            processing={processing}
            submitLabel={isEdit ? t('Save changes') : t('Create user')}
        >
            {/* Avatar — full width row at the top of the body */}
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
                        data.remove_avatar ? null : user?.avatar_url
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
                id="name"
                required
                label={t('Name')}
                value={data.name}
                onChange={(v) => setData('name', v)}
                error={errors.name}
            />

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

            <PhoneField
                id="phone"
                required
                label={t('Phone')}
                value={data.phone}
                onChange={(v) => setData('phone', v)}
                error={errors.phone}
                defaultCountry="LS" // Lesotho
            />
            <Select2Field
                id="role"
                required
                label={t('Role')}
                value={data.role}
                onChange={(v) => setData('role', v)}
                options={roleOptions}
                placeholder={t('Select a role')}
                error={errors.role}
            />
            {/* Cascading district -> division -> section, all searchable */}
            <Select2Field
                id="district"
                label={t('District')}
                value={data.district_id}
                onChange={(v) => setData('district_id', v)}
                options={toOptions(districts)}
                placeholder={t('Select a district')}
                error={errors.district_id}
            />

            <Select2Field
                id="division"
                label={t('Division')}
                value={data.division_id}
                onChange={handleDivisionChange}
                options={toOptions(divisions)}
                placeholder={t('Select a division')}
                error={errors.division_id}
            />

            <Select2Field
                id="section"
                label={t('Section')}
                value={data.section_id}
                onChange={(v) => setData('section_id', v)}
                options={toOptions(sections)}
                placeholder={t('Select a section')}
                disabled={!data.division_id}
                loading={loadingSections}
                error={errors.section_id}
            />

            <PasswordField
                id="password"
                label={isEdit ? t('New password (optional)') : t('Password')}
                value={data.password}
                onChange={(v) => setData('password', v)}
                placeholder={
                    isEdit
                        ? t('Leave blank to keep current password')
                        : undefined
                }
                error={errors.password}
            />

            <ConfirmPasswordField
                id="password_confirmation"
                label={t('Confirm Password')}
                value={data.password_confirmation}
                onChange={(v) => setData('password_confirmation', v)}
                passwordValue={data.password}
                error={errors.password_confirmation}
            />

            <TextareaField
                required
                id="bio"
                label={t('Bio')}
                value={data.bio}
                onChange={(v) => setData('bio', v)}
                error={errors.bio}
            />
            <StatusField
                id="status"
                label={t('Status')}
                value={data.status}
                onChange={(v) => setData('status', v)}
                error={errors.status}
                activeLabel={t('Active')}
                inactiveLabel={t('Inactive')}
            />
        </FormLayout>
    );
}
