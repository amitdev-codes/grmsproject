import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { FieldWrapper } from './field-wrapper';

export interface SelectFieldOption {
    value: string;
    label: string;
}

interface SelectFieldProps {
    id: string;
    label: string;
    value: string;
    onChange: (value: string) => void;
    options: SelectFieldOption[];
    error?: string;
    required?: boolean;
    placeholder?: string;
    disabled?: boolean;
}

/** Plain (non-searchable) shadcn Select — for short, fixed option lists. */
export function SelectField({
    id,
    label,
    value,
    onChange,
    options,
    error,
    required,
    placeholder,
    disabled,
}: SelectFieldProps) {
    return (
        <FieldWrapper id={id} label={label} error={error} required={required}>
            <Select value={value} onValueChange={onChange} disabled={disabled}>
                <SelectTrigger id={id} aria-invalid={!!error}>
                    <SelectValue placeholder={placeholder} />
                </SelectTrigger>
                <SelectContent>
                    {options.map((option) => (
                        <SelectItem key={option.value} value={option.value}>
                            {option.label}
                        </SelectItem>
                    ))}
                </SelectContent>
            </Select>
        </FieldWrapper>
    );
}
