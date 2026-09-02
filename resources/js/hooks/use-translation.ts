// hooks/use-translation.ts
import { usePage } from '@inertiajs/react';

type Translations = Record<string, any>;

export function useTranslation() {
    const { translations, locale } = usePage().props as unknown as {
        translations: Translations;
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

        let result = typeof value === 'string' ? value : key;

        for (const [search, replace] of Object.entries(replacements)) {
            result = result.replace(`:${search}`, String(replace));
        }

        return result;
    }

    return { t, locale };
}
