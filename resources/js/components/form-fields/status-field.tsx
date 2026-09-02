import { Switch } from '@/components/ui/switch';
import { FieldWrapper } from './field-wrapper';

interface StatusFieldProps {
    id: string;
    label?: string;
    value: '1' | '0';
    onChange: (value: '1' | '0') => void;
    error?: string;
    disabled?: boolean;
    activeLabel?: string;
    inactiveLabel?: string;
}

/**
 * Active/Inactive toggle for a "status" field. Emits the same '1' | '0'
 * strings your <Select> version used, so it's a drop-in swap in `data`.
 */
export function StatusField({
    id,
    label = 'Status',
    value,
    onChange,
    error,
    disabled,
    activeLabel = 'Active',
    inactiveLabel = 'Inactive',
}: StatusFieldProps) {
    const checked = value === '1';

    return (
        <FieldWrapper id={id} label={label} error={error}>
            <div className="flex items-center gap-2 pt-1">
                <Switch
                    id={id}
                    checked={checked}
                    onCheckedChange={(next) => onChange(next ? '1' : '0')}
                    disabled={disabled}
                    aria-invalid={!!error}
                />
                <span
                    className={
                        checked
                            ? 'text-sm font-medium text-foreground'
                            : 'text-sm text-muted-foreground'
                    }
                >
                    {checked ? activeLabel : inactiveLabel}
                </span>
            </div>
        </FieldWrapper>
    );
}
