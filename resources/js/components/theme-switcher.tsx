import { Moon, Palette, Route, Sun, Monitor } from 'lucide-react';
import {
    useAppearance,
    type Appearance,
    type ColorTheme,
} from '@/hooks/use-appearance';

const appearanceOptions: {
    value: Appearance;
    label: string;
    icon: typeof Sun;
}[] = [
    { value: 'light', label: 'Light', icon: Sun },
    { value: 'dark', label: 'Dark', icon: Moon },
    { value: 'system', label: 'System', icon: Monitor },
];

const themeOptions: {
    value: ColorTheme;
    label: string;
    icon: typeof Palette;
}[] = [
    { value: 'default', label: 'Default', icon: Palette },
    { value: 'roads', label: 'Laterite Road', icon: Route },
];

export function ThemeSwitcher() {
    const { appearance, updateAppearance, colorTheme, updateColorTheme } =
        useAppearance();

    return (
        <div className="flex flex-col gap-4">
            <div>
                <p className="form-label mb-1.5">Appearance</p>
                <div className="inline-flex rounded-lg border border-border p-0.5">
                    {appearanceOptions.map(({ value, label, icon: Icon }) => (
                        <button
                            key={value}
                            type="button"
                            onClick={() => updateAppearance(value)}
                            aria-pressed={appearance === value}
                            className={`btn-sm rounded-md ${
                                appearance === value
                                    ? 'bg-primary text-primary-foreground'
                                    : 'text-muted-foreground hover:bg-accent hover:text-accent-foreground'
                            }`}
                        >
                            <Icon className="h-3.5 w-3.5" />
                            {label}
                        </button>
                    ))}
                </div>
            </div>

            <div>
                <p className="form-label mb-1.5">Theme</p>
                <div className="inline-flex rounded-lg border border-border p-0.5">
                    {themeOptions.map(({ value, label, icon: Icon }) => (
                        <button
                            key={value}
                            type="button"
                            onClick={() => updateColorTheme(value)}
                            aria-pressed={colorTheme === value}
                            className={`btn-sm rounded-md ${
                                colorTheme === value
                                    ? 'bg-primary text-primary-foreground'
                                    : 'text-muted-foreground hover:bg-accent hover:text-accent-foreground'
                            }`}
                        >
                            <Icon className="h-3.5 w-3.5" />
                            {label}
                        </button>
                    ))}
                </div>
            </div>
        </div>
    );
}
