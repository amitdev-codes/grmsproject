// resources/js/Pages/GrievanceMessages/Form.tsx
import {
    Select2Field,
    StatusField,
    TextareaField,
} from '@/components/form-fields';
import { FormLayout } from '@/components/form-layout';
import { useTranslation } from '@/hooks/use-translation';
import { rules, validateForm } from '@/lib/validation';
import { useForm } from '@inertiajs/react';
import { MessageSquare } from 'lucide-react';
import { route } from 'ziggy-js';
import type { GrievanceMessage } from './columns';

interface GrievanceLookupOption {
    id: number;
    reference_number: string;
}

interface GrievanceMessageFormProps {
    grievanceMessage: GrievanceMessage | null;
    grievances: GrievanceLookupOption[];
    /** Only officers may mark a message internal — controls whether the field is shown/editable. */
    isOfficer: boolean;
    /** Locks the grievance select when arriving from a specific grievance's page (e.g. via a "New Message" link with ?grievance_id=). */
    lockedGrievanceId?: number | null;
}

interface FormValues {
    grievance_id: string;
    message: string;
    is_internal: '1' | '0';
}

export default function Form({
    grievanceMessage,
    grievances,
    isOfficer,
    lockedGrievanceId = null,
}: GrievanceMessageFormProps) {
    const isEdit = !!grievanceMessage;
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
    } = useForm<FormValues>({
        grievance_id: grievanceMessage?.grievance_id
            ? String(grievanceMessage.grievance_id)
            : lockedGrievanceId
              ? String(lockedGrievanceId)
              : '',
        message: grievanceMessage?.message ?? '',
        is_internal: grievanceMessage?.is_internal ? '1' : '0',
    });

    const submit = (e: React.SyntheticEvent) => {
        e.preventDefault();

        const clientErrors = validateForm(data, {
            grievance_id: [rules.required()],
            message: [rules.required()],
        });

        if (Object.keys(clientErrors).length > 0) {
            setError(clientErrors as Record<keyof FormValues, string>);

            return;
        }

        clearErrors();

        const url = isEdit
            ? route('grievance-messages.update', grievanceMessage!.id)
            : route('grievance-messages.store', data.grievance_id);

        transform((formData) => ({
            ...formData,
            // is_internal only meaningful from officers — backend also
            // re-derives sender_type/is_internal server-side regardless,
            // this just keeps the payload honest on the client.
            is_internal: isOfficer ? formData.is_internal : '0',
            ...(isEdit ? { _method: 'put' } : {}),
        }));

        post(url, { preserveScroll: true });
    };

    const grievanceOptions = grievances.map((g) => ({
        value: String(g.id),
        label: g.reference_number,
    }));

    return (
        <FormLayout
            title={isEdit ? t('Edit Message') : t('New Message')}
            description={
                isEdit
                    ? t('Update message #:id.', { id: grievanceMessage!.id })
                    : t('Send a message on a grievance.')
            }
            breadcrumbs={[
                {
                    label: t('Grievance Messages'),
                    icon: MessageSquare,
                    href: route('grievance-messages.index'),
                },
                {
                    label: isEdit ? t('Edit Message') : t('New Message'),
                },
            ]}
            onSubmit={submit}
            processing={processing}
            submitLabel={isEdit ? t('Save changes') : t('Send')}
        >
            <Select2Field
                id="grievance_id"
                required
                disabled={isEdit || !!lockedGrievanceId}
                label={t('Grievance')}
                value={data.grievance_id}
                onChange={(v) => setData('grievance_id', v)}
                options={grievanceOptions}
                placeholder={t('Select a grievance')}
                error={errors.grievance_id}
            />

            <TextareaField
                id="message"
                required
                label={t('Message')}
                value={data.message}
                onChange={(v) => setData('message', v)}
                error={errors.message}
                placeholder={t('Write a message…')}
            />

            {isOfficer && (
                <StatusField
                    id="is_internal"
                    label={t('Visibility')}
                    value={data.is_internal}
                    onChange={(v) => setData('is_internal', v)}
                    error={errors.is_internal}
                    activeLabel={t('Internal note')}
                    inactiveLabel={t('Visible to complainant')}
                />
            )}
        </FormLayout>
    );
}
