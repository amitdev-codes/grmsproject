import { Label } from '@/components/ui/label';
import type { ReactNode } from 'react';

interface FieldWrapperProps {
    id: string;
    label: string;
    error?: string;
    required?: boolean;
    className?: string;
    children: ReactNode;
}

/**
 * Common label + control + error layout shared by all field components.
 * Keeps every field's markup consistent without repeating the same
 * `flex flex-col gap-1.5` / error `<p>` boilerplate everywhere.
 */
export function FieldWrapper({
    id,
    label,
    error,
    required,
    className,
    children,
}: FieldWrapperProps) {
    return (
        <div className={className ?? 'flex flex-col gap-1.5'}>
            <Label htmlFor={id}>
                {label}
                {required && <span className="text-destructive"> *</span>}
            </Label>
            {children}
            {error && <p className="text-sm text-destructive">{error}</p>}
        </div>
    );
}
