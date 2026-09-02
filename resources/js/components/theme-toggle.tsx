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
            className="flex h-9 w-9 items-center justify-center rounded-lg border transition-all hover:border-primary/40 hover:bg-accent focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none dark:border-neutral-700 dark:hover:border-neutral-600"
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
