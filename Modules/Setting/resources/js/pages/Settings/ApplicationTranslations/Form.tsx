import { TextField, TextareaField } from '@/components/form-fields';
import { FormLayout } from '@/components/form-layout';
import { useForm } from '@inertiajs/react';
import { Languages } from 'lucide-react';
import { route } from 'ziggy-js';

interface Translation {
    id: number;
    translation_key: string;
    english_text: string;
    sesotho_text: string | null;
}

interface TranslationFormProps {
    translation: Translation | null;
}

export default function Form({ translation }: TranslationFormProps) {
    const edit = Boolean(translation);
    const { data, setData, post, transform, processing, errors } = useForm({
        translation_key: translation?.translation_key ?? '',
        english_text: translation?.english_text ?? '',
        sesotho_text: translation?.sesotho_text ?? '',
    });

    const submit = (event: React.SyntheticEvent) => {
        event.preventDefault();
        transform((values) => ({
            ...values,
            ...(edit ? { _method: 'put' } : {}),
        }));
        post(
            edit
                ? route('settings.translations.update', translation!.id)
                : route('settings.translations.store'),
            { preserveScroll: true },
        );
    };

    return (
        <FormLayout
            title={edit ? 'Edit Translation' : 'Create Translation'}
            description="Manage the English and Sesotho text shown for this application label."
            breadcrumbs={[
                {
                    label: 'Language translations',
                    icon: Languages,
                    href: route('settings.translations.index'),
                },
                { label: edit ? 'Edit Translation' : 'Create Translation' },
            ]}
            onSubmit={submit}
            processing={processing}
            submitLabel={edit ? 'Save changes' : 'Create Translation'}
        >
            <TextField
                id="translation_key"
                required
                label="Translation key"
                value={data.translation_key}
                onChange={(value) => setData('translation_key', value)}
                error={errors.translation_key}
                placeholder="menu.reports"
            />
            <TextareaField
                id="english_text"
                required
                label="English text"
                value={data.english_text}
                onChange={(value) => setData('english_text', value)}
                error={errors.english_text}
            />
            <TextareaField
                id="sesotho_text"
                label="Sesotho translation"
                value={data.sesotho_text}
                onChange={(value) => setData('sesotho_text', value)}
                error={errors.sesotho_text}
            />
        </FormLayout>
    );
}
