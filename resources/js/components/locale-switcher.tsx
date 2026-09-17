import { router } from '@inertiajs/react';
import { useTranslation } from '@/hooks/use-translation';
import { cn } from '@/lib/utils';

export function LocaleSwitcher() {
    const { locale } = useTranslation();

    const toggleLocale = () => {
        const nextLocale = locale === 'en' ? 'st' : 'en';

        router.get(
            `/lang/${nextLocale}`,
            {},
            {
                preserveScroll: true,
                preserveState: false,
            },
        );
    };

    return (
        <button
            type="button"
            role="switch"
            aria-checked={locale === 'st'}
            aria-label={
                locale === 'en'
                    ? 'Switch language to Sesotho'
                    : 'Switch language to English'
            }
            onClick={toggleLocale}
            className="relative flex h-9 w-17 items-center rounded-full border border-white/30 bg-white/15 px-0.75 transition-colors hover:bg-white/25 focus-visible:ring-2 focus-visible:ring-white/60 focus-visible:ring-offset-2 focus-visible:outline-none focus-visible:ring-offset-transparent"
        >
            <span className="pointer-events-none absolute inset-0 flex items-center justify-between px-2 text-[10px] font-semibold tracking-wide text-white">
                <span
                    className={cn(
                        'transition-opacity',
                        locale === 'en' ? 'opacity-100' : 'opacity-40',
                    )}
                >
                    EN
                </span>
                <span
                    className={cn(
                        'transition-opacity',
                        locale === 'st' ? 'opacity-100' : 'opacity-40',
                    )}
                >
                    ST
                </span>
            </span>

            <span
                className={cn(
                    'z-10 flex h-7 w-7 items-center justify-center rounded-full bg-white text-sm shadow-sm transition-transform duration-200 ease-out',
                    locale === 'st' ? 'translate-x-8' : 'translate-x-0',
                )}
            >
                <span aria-hidden="true">{locale === 'en' ? '🇬🇧' : '🇱🇸'}</span>
            </span>
        </button>
    );
}