import { Switch } from '@/components/ui/switch';
import { FieldWrapper } from './field-wrapper';

interface SwitchFieldProps {
    id: string;
    label: string;
    checked: boolean;
    onChange: (checked: boolean) => void;
    error?: string;
    required?: boolean;
    disabled?: boolean;
    /** Small helper text shown next to the switch, e.g. "Enabled" / "Notify by email". */
    description?: string;
}

export function SwitchField({
    id,
    label,
    checked,
    onChange,
    error,
    required,
    disabled,
    description,
}: SwitchFieldProps) {
    return (
        <FieldWrapper id={id} label={label} error={error} required={required}>
            <div className="flex items-center gap-2 pt-1">
                <Switch
                    id={id}
                    checked={checked}
                    onCheckedChange={onChange}
                    disabled={disabled}
                    aria-invalid={!!error}
                />
                {description && (
                    <span className="text-sm text-muted-foreground">
                        {description}
                    </span>
                )}
            </div>
        </FieldWrapper>
    );
}
