import { Eye, EyeOff } from 'lucide-react';
import { useState } from 'react';
import type { InputHTMLAttributes } from 'react';
import { Input } from '@/components/ui/input';
import { FieldWrapper } from './field-wrapper';

interface PasswordFieldProps extends Omit<
    InputHTMLAttributes<HTMLInputElement>,
    'onChange' | 'value' | 'id' | 'type'
> {
    id: string;
    label: string;
    value: string;
    onChange: (value: string) => void;
    error?: string;
    required?: boolean;
}

/** Password input with a click-to-toggle eye icon for show/hide. */
export function PasswordField({
    id,
    label,
    value,
    onChange,
    error,
    required,
    ...inputProps
}: PasswordFieldProps) {
    const [visible, setVisible] = useState(false);

    return (
        <FieldWrapper id={id} label={label} error={error} required={required}>
            <div className="relative">
                <Input
                    id={id}
                    type={visible ? 'text' : 'password'}
                    required={required}
                    {...inputProps}
                    value={value}
                    onChange={(e) => onChange(e.target.value)}
                    aria-invalid={!!error}
                    className="pr-10"
                />
                <button
                    type="button"
                    onClick={() => setVisible((prev) => !prev)}
                    tabIndex={-1}
                    className="absolute inset-y-0 right-0 flex items-center px-3 text-muted-foreground hover:text-foreground"
                    aria-label={visible ? 'Hide password' : 'Show password'}
                >
                    {visible ? (
                        <EyeOff className="size-4" />
                    ) : (
                        <Eye className="size-4" />
                    )}
                </button>
            </div>
        </FieldWrapper>
    );
}
