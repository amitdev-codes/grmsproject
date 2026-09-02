import { TextField, StatusField } from '@/components/form-fields';
import { FormLayout } from '@/components/form-layout';
import { Badge } from '@/components/ui/badge';
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Label } from '@/components/ui/label';
import { ScrollArea } from '@/components/ui/scroll-area';
import { Switch } from '@/components/ui/switch';
import { useTranslation } from '@/hooks/use-translation';
import { rules, validateForm } from '@/lib/validation';
import { useForm } from '@inertiajs/react';
import { ShieldCheck } from 'lucide-react';
import { route } from 'ziggy-js';
import type { Role } from './columns';

interface GroupedPermission {
    name: string;
    action: string;
}

interface RoleFormProps {
    role: (Role & { permissions?: string[] }) | null;
    permissions: Record<string, GroupedPermission[]>;
}

interface FormValues {
    code: string;
    name: string;
    name_st: string;
    status: '1' | '0';
    permissions: string[];
}

export default function Form({ role, permissions }: RoleFormProps) {
    const isEdit = !!role;
    const { t } = useTranslation();

    const {
        data,
        setData,
        post,
        put,
        transform,
        processing,
        errors,
        setError,
        clearErrors,
    } = useForm<FormValues>({
        code: role?.code ?? '',
        name: role?.name ?? '',
        name_st: role?.name_st ?? '',
        status: role ? (role.status ? '1' : '0') : '1',
        permissions: role?.permissions ?? [],
    });

    const allPermissionNames = Object.values(permissions)
        .flat()
        .map((p) => p.name);
    const allSelected =
        data.permissions.length === allPermissionNames.length &&
        allPermissionNames.length > 0;
    const togglePermission = (name: string) => {
        setData(
            'permissions',
            data.permissions.includes(name)
                ? data.permissions.filter((p) => p !== name)
                : [...data.permissions, name],
        );
    };

    const toggleGroup = (perms: GroupedPermission[]) => {
        const names = perms.map((p) => p.name);
        const allSelected = names.every((n) => data.permissions.includes(n));

        setData(
            'permissions',
            allSelected
                ? data.permissions.filter((p) => !names.includes(p))
                : [...new Set([...data.permissions, ...names])],
        );
    };


    const isGroupFullySelected = (perms: GroupedPermission[]) =>
        perms.every((p) => data.permissions.includes(p.name));

    const isGroupPartiallySelected = (perms: GroupedPermission[]) =>
        perms.some((p) => data.permissions.includes(p.name)) &&
        !isGroupFullySelected(perms);

    const submit = (e: React.SyntheticEvent) => {
        e.preventDefault();

        const clientErrors = validateForm(data, {
            code: [rules.required()],
            name: [rules.required()],
            name_st: [rules.required()],
        });

        if (Object.keys(clientErrors).length > 0) {
            setError(clientErrors as Record<keyof FormValues, string>);

            return;
        }

        clearErrors();

        if (isEdit) {
            put(route('roles.update', role!.id), { preserveScroll: true });
        } else {
            post(route('roles.store'), { preserveScroll: true });
        }
    };

    return (
        <FormLayout
            title={isEdit ? t('Edit Role') : t('Create Role')}
            description={
                isEdit
                    ? t("Update :name's details.", { name: role!.name })
                    : t('Add a new role to the system.')
            }
            breadcrumbs={[
                {
                    label: t('Roles'),
                    icon: ShieldCheck,
                    href: route('roles.index'),
                },
                { label: isEdit ? t('Edit Role') : t('Create Role') },
            ]}
            onSubmit={submit}
            processing={processing}
            submitLabel={isEdit ? t('Save changes') : t('Create role')}
        >
            <TextField
                id="code"
                required
                label={t('Code')}
                value={data.code}
                onChange={(v) => setData('code', v)}
                error={errors.code}
            />

            <TextField
                id="name"
                required
                label={t('Name')}
                value={data.name}
                onChange={(v) => setData('name', v)}
                error={errors.name}
            />

            <TextField
                id="name_st"
                required
                label={t('Name (ST)')}
                value={data.name_st}
                onChange={(v) => setData('name_st', v)}
                error={errors.name_st}
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

            {/* Permissions — full width, spans both form columns */}
            <div className="sm:col-span-2">
                <Card>
                    <CardHeader className="px-3 pb-2">
                        <div className="flex items-center justify-between">
                            <CardTitle className="text-sm">
                                {t('Permissions')}
                            </CardTitle>
                            <div className="flex items-center gap-2">
                                <Label
                                    htmlFor="select-all-permissions"
                                    className="cursor-pointer text-xs font-normal text-muted-foreground"
                                >
                                    {t('Select all')}
                                </Label>
                                <Switch
                                    id="select-all-permissions"
                                    checked={allSelected}
                                    onCheckedChange={(checked) =>
                                        setData(
                                            'permissions',
                                            checked ? allPermissionNames : [],
                                        )
                                    }
                                />
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent>

                            <div className="space-y-4">
                                {Object.entries(permissions).map(
                                    ([group, perms]) => (
                                        <div key={group} className="space-y-3">
                                            <div className="flex items-center space-x-2 border-b pb-2">
                                                <Checkbox
                                                    id={`group-${group}`}
                                                    checked={isGroupFullySelected(
                                                        perms,
                                                    )}
                                                    ref={(ref) => {
                                                        if (ref) {
                                                            (
                                                                ref as HTMLButtonElement & {
                                                                    indeterminate: boolean;
                                                                }
                                                            ).indeterminate =
                                                                isGroupPartiallySelected(
                                                                    perms,
                                                                );
                                                        }
                                                    }}
                                                    onCheckedChange={() =>
                                                        toggleGroup(perms)
                                                    }
                                                />
                                                <Label
                                                    htmlFor={`group-${group}`}
                                                    className="cursor-pointer text-sm font-semibold capitalize"
                                                >
                                                    {group}
                                                </Label>
                                                <Badge
                                                    variant="outline"
                                                    className="font-normal"
                                                >
                                                    {
                                                        perms.filter((p) =>
                                                            data.permissions.includes(
                                                                p.name,
                                                            ),
                                                        ).length
                                                    }
                                                    /{perms.length}
                                                </Badge>
                                            </div>
                                            <div className="grid grid-cols-2 gap-3 pl-6 md:grid-cols-3">
                                                {perms.map((permission) => (
                                                    <div
                                                        key={permission.name}
                                                        className="flex items-center space-x-2"
                                                    >
                                                        <Checkbox
                                                            id={permission.name}
                                                            checked={data.permissions.includes(
                                                                permission.name,
                                                            )}
                                                            onCheckedChange={() =>
                                                                togglePermission(
                                                                    permission.name,
                                                                )
                                                            }
                                                        />
                                                        <Label
                                                            htmlFor={
                                                                permission.name
                                                            }
                                                            className="cursor-pointer text-sm font-normal capitalize"
                                                        >
                                                            {permission.action}
                                                        </Label>
                                                    </div>
                                                ))}
                                            </div>
                                        </div>
                                    ),
                                )}
                            </div>

                        {errors.permissions && (
                            <p className="mt-2 text-sm text-destructive">
                                {errors.permissions}
                            </p>
                        )}
                    </CardContent>
                </Card>
            </div>
        </FormLayout>
    );
}
