export type Validator = (value: unknown) => string | undefined;

/**
 * Reusable validator builders. Each returns `undefined` when the value is
 * valid, or an error message string when it isn't. Empty/undefined values
 * are treated as valid by everything except `required` — chain
 * `rules.required()` first if a field must be filled in.
 */
export const rules = {
    required:
        (message = 'This field is required.'): Validator =>
        (value) => {
            if (value === null || value === undefined) {
                return message;
            }

            if (typeof value === 'string' && value.trim() === '') {
                    return message;
                }

            if (Array.isArray(value) && value.length === 0) {
                return message;
            }

            return undefined;
        },

    /** Validates the *local* digit count of a "+<dialCode> <digits>" phone value. */
    phoneDigits:
        (count: number, message?: string): Validator =>
        (value) => {
            const str = String(value ?? '');

            if (str.trim() === '') {
                return undefined;
            } // pair with rules.required()

            const localPart = str.includes(' ')
                ? str.split(' ').slice(1).join('')
                : str;
            const digits = localPart.replace(/\D/g, '');

            if (digits.length !== count) {
                return (
                    message ?? `Phone number must be exactly ${count} digits.`
                );
            }

            return undefined;
        },

    numberRange:
        (min?: number, max?: number, message?: string): Validator =>
        (value) => {
            if (value === '' || value === null || value === undefined) {
                    return undefined;
                }

            const num = Number(value);

            if (Number.isNaN(num)) {
                return 'Must be a valid number.';
            }

            if (!Number.isInteger(num)) {
                return 'Must be a whole number.';
            }

            if (min !== undefined && num < min) {
                return message ?? `Must be at least ${min}.`;
            }

            if (max !== undefined && num > max) {
                    return message ?? `Must be at most ${max}.`;
                }

            return undefined;
        },

    decimalRange:
        (min?: number, max?: number, message?: string): Validator =>
        (value) => {
            if (value === '' || value === null || value === undefined) {
                return undefined;
            }

            const num = Number(value);

            if (Number.isNaN(num)) {
                return 'Must be a valid number.';
            }

            if (min !== undefined && num < min) {
                    return message ?? `Must be at least ${min}.`;
                }

            if (max !== undefined && num > max) {
                    return message ?? `Must be at most ${max}.`;
                }

            return undefined;
        },
};

/**
 * Runs a schema of validators against `data` and returns an errors map
 * (only failing fields are included). First failing validator per field wins.
 */
export function validateForm<T extends object>(
    data: T,
    schema: Partial<Record<keyof T, Validator[]>>,
): Partial<Record<keyof T, string>> {
    const errors: Partial<Record<keyof T, string>> = {};

    for (const field in schema) {
        const key = field as keyof T;
        const validators = schema[key] ?? [];

        for (const validate of validators) {
            const message = validate(data[key]);

            if (message) {
                errors[key] = message;
                break;
            }
        }
    }

    return errors;
}
