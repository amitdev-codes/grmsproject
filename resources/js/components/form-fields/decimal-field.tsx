import type { FocusEvent, InputHTMLAttributes } from 'react';
import { Input } from '@/components/ui/input';
import { FieldWrapper } from './field-wrapper';

interface DecimalFieldProps extends Omit<
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
    /** Decimal precision step, e.g. 0.01 for currency. Defaults to 0.01. */
    step?: number;
    /** Decimal places to round to on blur. Derived from `step` if omitted. */
    precision?: number;
}

function derivePrecision(step: number, override?: number) {
    if (override !== undefined) {
        return override;
    }

    const str = String(step);

    return str.includes('.') ? str.split('.')[1].length : 0;
}

/**
 * Decimal input. On blur, clamps into [min, max] and rounds to `precision`
 * (or a precision derived from `step`) — so "12.4567" with step 0.01 becomes
 * "12.46", and anything unparsable is cleared instead of silently kept.
 */
export function DecimalField({
    id,
    label,
    value,
    onChange,
    error,
    required,
    min,
    max,
    step = 0.01,
    precision,
    ...inputProps
}: DecimalFieldProps) {
    const decimalPlaces = derivePrecision(step, precision);

    const handleBlur = (e: FocusEvent<HTMLInputElement>) => {
        const raw = e.target.value;

        if (raw === '') {
            return;
        }

        const parsed = parseFloat(raw);

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

        onChange(clamped.toFixed(decimalPlaces));
    };

    return (
        <FieldWrapper id={id} label={label} error={error} required={required}>
            <Input
                id={id}
                type="number"
                inputMode="decimal"
                required={required}
                min={min}
                max={max}
                step={step}
                {...inputProps}
                value={value}
                onChange={(e) => onChange(e.target.value)}
                onBlur={handleBlur}
                aria-invalid={!!error}
            />
        </FieldWrapper>
    );
}
