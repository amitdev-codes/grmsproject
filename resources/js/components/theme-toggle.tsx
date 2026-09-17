import { Moon, Sun } from 'lucide-react';
import { useAppearance } from '@/hooks/use-appearance';

export function ThemeToggle() {
    const { appearance, updateAppearance } = useAppearance();

    const toggleAppearance = () => {
        updateAppearance(appearance === 'light' ? 'dark' : 'light');
    };

    return (
        <button
            type="button"
            onClick={toggleAppearance}
            className="flex h-9 w-9 items-center justify-center rounded-lg border border-white/30 bg-white/15 text-white transition-all hover:bg-white/25 focus-visible:ring-2 focus-visible:ring-white/60 focus-visible:ring-offset-2 focus-visible:outline-none focus-visible:ring-offset-transparent"
            aria-label={
                appearance === 'light'
                    ? 'Switch to dark mode'
                    : 'Switch to light mode'
            }
        >
            {appearance === 'light' ? (
                <Moon className="h-4 w-4" />
            ) : (
                <Sun className="h-4 w-4" />
            )}
        </button>
    );
}