import type { TextareaHTMLAttributes } from 'react';
import { Textarea } from '@/components/ui/textarea';
import { FieldWrapper } from './field-wrapper';

interface TextareaFieldProps extends Omit<
    TextareaHTMLAttributes<HTMLTextAreaElement>,
    'onChange' | 'value' | 'id'
> {
    id: string;
    label: string;
    value: string;
    onChange: (value: string) => void;
    error?: string;
    required?: boolean;
    /** Full width row inside a 2-col grid. Defaults to true. */
    fullWidth?: boolean;
}

export function TextareaField({
    id,
    label,
    value,
    onChange,
    error,
    required,
    fullWidth = true,
    rows = 4,
    ...textareaProps
}: TextareaFieldProps) {
    return (
        <FieldWrapper
            id={id}
            label={label}
            error={error}
            required={required}
            className={
                fullWidth
                    ? 'flex flex-col gap-1.5 sm:col-span-2'
                    : 'flex flex-col gap-1.5'
            }
        >
            <Textarea
                id={id}
                rows={rows}
                required={required}
                {...textareaProps}
                value={value}
                onChange={(e) => onChange(e.target.value)}
                aria-invalid={!!error}
            />
        </FieldWrapper>
    );
}
