import { Combobox } from '@/components/ui/combobox';
import { useTranslation } from '@/hooks/use-translation';
import { FieldWrapper } from './field-wrapper';

export interface Select2Option {
    value: string;
    label: string;
}

interface Select2FieldProps {
    id: string;
    label: string;
    value: string;
    onChange: (value: string) => void;
    options: Select2Option[];
    error?: string;
    required?: boolean;
    placeholder?: string;
    disabled?: boolean;
    loading?: boolean;
}

/**
 * Searchable "select2-style" field, built on the existing Combobox —
 * use for long/lookup option lists (roles, districts, divisions, etc).
 */
export function Select2Field({
    id,
    label,
    value,
    onChange,
    options,
    error,
    required,
    placeholder,
    disabled,
    loading,
}: Select2FieldProps) {
    const { t } = useTranslation();

    return (
        <FieldWrapper id={id} label={label} error={error} required={required}>
            <Combobox
                id={id}
                options={options}
                value={value}
                onChange={onChange}
                placeholder={placeholder ?? t('Select…')}
                searchPlaceholder={t('Search…')}
                emptyText={t('No results found.')}
                disabled={disabled}
                loading={loading}
            />
        </FieldWrapper>
    );
}
