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
            className="flex h-9 w-9 items-center justify-center rounded-lg border border-sidebar-border bg-sidebar text-sidebar-foreground transition-all hover:bg-sidebar-accent hover:text-sidebar-accent-foreground focus-visible:ring-2 focus-visible:ring-sidebar-ring focus-visible:ring-offset-2 focus-visible:outline-none focus-visible:ring-offset-sidebar"
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
