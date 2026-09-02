import { Eye, EyeOff } from 'lucide-react';
import { useState } from 'react';
import type { InputHTMLAttributes } from 'react';
import { Input } from '@/components/ui/input';
import { FieldWrapper } from './field-wrapper';

interface ConfirmPasswordFieldProps extends Omit<
    InputHTMLAttributes<HTMLInputElement>,
    'onChange' | 'value' | 'id' | 'type'
> {
    id: string;
    label: string;
    value: string;
    onChange: (value: string) => void;
    /** The primary password field's current value, for a live match check. */
    passwordValue: string;
    error?: string;
    required?: boolean;
}

/**
 * Same eye-toggle UX as PasswordField, plus an inline "passwords don't
 * match" hint — shown only once the user has typed something here, and
 * only when there's no server-side error already displayed for it.
 */
export function ConfirmPasswordField({
    id,
    label,
    value,
    onChange,
    passwordValue,
    error,
    required,
    ...inputProps
}: ConfirmPasswordFieldProps) {
    const [visible, setVisible] = useState(false);

    const mismatch =
        !error && value.length > 0 && value !== passwordValue
            ? 'Passwords do not match.'
            : undefined;

    return (
        <FieldWrapper
            id={id}
            label={label}
            error={error ?? mismatch}
            required={required}
        >
            <div className="relative">
                <Input
                    id={id}
                    type={visible ? 'text' : 'password'}
                    required={required}
                    {...inputProps}
                    value={value}
                    onChange={(e) => onChange(e.target.value)}
                    aria-invalid={!!(error ?? mismatch)}
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
