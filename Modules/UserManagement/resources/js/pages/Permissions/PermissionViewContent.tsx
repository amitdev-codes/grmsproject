import {
    DialogHeader,
    DialogTitle,

} from '@/components/ui/dialog';
import type { Permission } from './columns';

interface PermissionViewContentProps {
    permission: Permission;
}

function Field({ label, value }: { label: string; value: React.ReactNode }) {
    return (
        <div className="flex flex-col gap-0.5">
            <span className="text-xs font-medium text-muted-foreground">
                {label}
            </span>
            <span className="text-sm">{value}</span>
        </div>
    );
}

export function PermissionViewContent({ permission }: PermissionViewContentProps) {
    return (
        <>
            <DialogHeader>
                <div className="flex items-center gap-3">
                    <Field
                        label="Name"
                        value={permission.name}
                    />
                </div>
            </DialogHeader>

            <div className="grid grid-cols-2 gap-4 pt-2">
                <Field
                    label="Created"
                    value={new Date(permission.created_at).toLocaleString()}
                />
            </div>
        </>
    );
}
