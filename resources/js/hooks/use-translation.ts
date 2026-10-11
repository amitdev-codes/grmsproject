// hooks/use-translation.ts
import { usePage } from '@inertiajs/react';

type Translations = Record<string, any>;

export function useTranslation() {
    const { translations, dynamicTranslations, locale } = usePage()
        .props as unknown as {
        translations: Translations;
        dynamicTranslations: Record<string, Record<string, string>>;
        locale: string;
    };

    function t(
        key: string,
        replacements: Record<string, string | number> = {},
    ): string {
        const value = key
            .split('.')
            .reduce<any>(
                (acc, part) =>
                    acc && typeof acc === 'object' ? acc[part] : undefined,
                translations,
            );

        const dynamicValue = dynamicTranslations?.[key]?.[locale];
        let result =
            typeof dynamicValue === 'string' && dynamicValue.length > 0
                ? dynamicValue
                : typeof value === 'string'
                  ? value
                  : key;

        for (const [search, replace] of Object.entries(replacements)) {
            result = result.replace(`:${search}`, String(replace));
        }

        return result;
    }

    function tLabel(label: string): string {
        if (label.startsWith('menu.')) {
            const translated = t(label);

            return translated === label ? label : translated;
        }

        const normalized = label
            .trim()
            .toLowerCase()
            .replace(/&/g, ' and ')
            .replace(/[^a-z0-9]+/g, '_')
            .replace(/^_+|_+$/g, '');
        const kebab = normalized.replace(/_/g, '-');

        for (const key of new Set([`menu.${normalized}`, `menu.${kebab}`])) {
            const translated = t(key);

            if (translated !== key) {
                return translated;
            }
        }

        return label;
    }

    return { t, tLabel, locale };
}
