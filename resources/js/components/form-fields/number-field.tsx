import type { FocusEvent, InputHTMLAttributes } from 'react';
import { Input } from '@/components/ui/input';
import { FieldWrapper } from './field-wrapper';

interface NumberFieldProps extends Omit<
    InputHTMLAttributes<HTMLInputElement>,
    'onChange' | 'value' | 'id' | 'type' | 'onBlur'
> {
    id: string;
    label: string;
    /** Kept as a string so the form's `data` stays JSON/FormData friendly. */
    value: string;
    onChange: (value: string) => void;
    error?: string;
    required?: boolean;
    min?: number;
    max?: number;
    step?: number;
}

/**
 * Whole-number input. Blocks decimal keystrokes while typing, and on blur
 * clamps the value into [min, max] and drops anything unparsable — so an
 * out-of-range or malformed number never silently reaches `data`.
 */
export function NumberField({
    id,
    label,
    value,
    onChange,
    error,
    required,
    min,
    max,
    step = 1,
    ...inputProps
}: NumberFieldProps) {
    const handleBlur = (e: FocusEvent<HTMLInputElement>) => {
        const raw = e.target.value;

        if (raw === '') {
            return;
        }

        const parsed = parseInt(raw, 10);

        if (Number.isNaN(parsed)) {
            onChange('');

            return;
        }

        let clamped = parsed;

        if (min !== undefined) {
            clamped = Math.max(min, clamped);
        }

        if (max !== undefined) {
            clamped = Math.min(max, clamped);
        }

        onChange(String(clamped));
    };

    return (
        <FieldWrapper id={id} label={label} error={error} required={required}>
            <Input
                id={id}
                type="number"
                inputMode="numeric"
                required={required}
                min={min}
                max={max}
                step={step}
                {...inputProps}
                value={value}
                onChange={(e) => onChange(e.target.value)}
                onBlur={handleBlur}
                onKeyDown={(e) => {
                    // Block '.', ',', 'e' so this truly stays whole-number only
                    if (['.', ',', 'e', 'E'].includes(e.key)) {
                        e.preventDefault();
                    }
                }}
                aria-invalid={!!error}
            />
        </FieldWrapper>
    );
}
