import type { InputHTMLAttributes } from 'react';
import { Input } from '@/components/ui/input';
import { FieldWrapper } from './field-wrapper';

interface TextFieldProps extends Omit<
    InputHTMLAttributes<HTMLInputElement>,
    'onChange' | 'value' | 'id' | 'type'
> {
    id: string;
    label: string;
    value: string;
    onChange: (value: string) => void;
    error?: string;
    required?: boolean;
    type?: 'text' | 'email' | 'tel' | 'url';
}

export function TextField({
    id,
    label,
    value,
    onChange,
    error,
    required,
    type = 'text',
    ...inputProps
}: TextFieldProps) {
    return (
        <FieldWrapper id={id} label={label} error={error} required={required}>
            <Input
                id={id}
                type={type}
                required={required}
                {...inputProps}
                value={value}
                onChange={(e) => onChange(e.target.value)}
                aria-invalid={!!error}
            />
        </FieldWrapper>
    );
}
