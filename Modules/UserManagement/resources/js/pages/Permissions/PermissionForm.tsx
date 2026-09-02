import { TextField } from '@/components/form-fields';
import ModalLayout  from '@/components/modal-layout';
import { useTranslation } from '@/hooks/use-translation';
import { useForm } from '@inertiajs/react';
import { useEffect } from 'react';
import type { Permission } from '@modules/UserManagement/pages/Permissions/columns';

interface Props {
    open: boolean;
    onOpenChange: (value: boolean) => void;
    permission?: Permission | null;
}

export default function PermissionForm({
    open,
    onOpenChange,
    permission,
}: Props) {
    const { t } = useTranslation();
    const isEdit = !!permission;

    const { data, setData,errors, post, put, processing } = useForm({
        name: permission?.name ?? '',
    });

    useEffect(() => {
        setData({
            name: permission?.name ?? '',
        });
    }, [permission]);

    const submit = (e: React.FormEvent) => {
        e.preventDefault();

        if (isEdit) {
            put(route('permissions.update', permission.id), {
                onSuccess: () => onOpenChange(false),
            });
        } else {
            post(route('permissions.store'), {
                onSuccess: () => onOpenChange(false),
            });
        }
    };

    return (
        <ModalLayout
            open={open}
            onOpenChange={onOpenChange}
            title={isEdit ? 'Edit Permission' : 'New Permission'}
            mode={isEdit ? 'edit' : 'create'}
            processing={processing}
            onSubmit={submit}
        >
            <TextField
                id="name"
                required
                label={t('Name')}
                value={data.name}
                onChange={(v) => setData('name', v)}
                error={errors.name}
            />
        </ModalLayout>
    );
}
