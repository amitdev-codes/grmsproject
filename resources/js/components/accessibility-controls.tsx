import { Contrast, Minus, Plus, RotateCcw } from 'lucide-react';
import { useEffect, useState } from 'react';

const FONT_SCALE_KEY = 'accessibility-font-scale';
const CONTRAST_KEY = 'accessibility-contrast';
const MIN_FONT_SCALE = 0.8;
const MAX_FONT_SCALE = 1.3;
const FONT_SCALE_STEP = 0.1;

const clampFontScale = (value: number) => {
    return Math.min(MAX_FONT_SCALE, Math.max(MIN_FONT_SCALE, value));
};

const readFontScale = () => {
    if (typeof window === 'undefined') {
        return 1;
    }

    const storedScale = Number.parseFloat(
        window.localStorage.getItem(FONT_SCALE_KEY) ?? '',
    );

    return Number.isFinite(storedScale) ? clampFontScale(storedScale) : 1;
};

const readContrastPreference = () => {
    if (typeof window === 'undefined') {
        return false;
    }

    return window.localStorage.getItem(CONTRAST_KEY) === 'true';
};

export function AccessibilityControls() {
    const [fontScale, setFontScale] = useState(readFontScale);
    const [highContrast, setHighContrast] = useState(readContrastPreference);

    useEffect(() => {
        const root = document.documentElement;

        root.style.setProperty('--user-font-scale', String(fontScale));
        root.classList.toggle('high-contrast', highContrast);
    }, [fontScale, highContrast]);

    const updateFontScale = (nextScale: number) => {
        const scale = clampFontScale(nextScale);

        setFontScale(scale);
        window.localStorage.setItem(FONT_SCALE_KEY, String(scale));
        document.documentElement.style.setProperty(
            '--user-font-scale',
            String(scale),
        );
    };

    const toggleContrast = () => {
        const nextContrast = !highContrast;

        setHighContrast(nextContrast);
        window.localStorage.setItem(CONTRAST_KEY, String(nextContrast));
        document.documentElement.classList.toggle(
            'high-contrast',
            nextContrast,
        );
    };

    const resetPreferences = () => {
        setFontScale(1);
        setHighContrast(false);
        window.localStorage.removeItem(FONT_SCALE_KEY);
        window.localStorage.removeItem(CONTRAST_KEY);
        document.documentElement.style.setProperty('--user-font-scale', '1');
        document.documentElement.classList.remove('high-contrast');
    };

    return (
        <div
            className="accessibility-controls"
            aria-label="Accessibility controls"
        >
            <button
                type="button"
                aria-label="Decrease text size"
                disabled={fontScale <= MIN_FONT_SCALE}
                onClick={() => updateFontScale(fontScale - FONT_SCALE_STEP)}
            >
                <Minus className="h-4 w-4" />
            </button>
            <output aria-live="polite" aria-label="Text size">
                {Math.round(fontScale * 100)}%
            </output>
            <button
                type="button"
                aria-label="Increase text size"
                disabled={fontScale >= MAX_FONT_SCALE}
                onClick={() => updateFontScale(fontScale + FONT_SCALE_STEP)}
            >
                <Plus className="h-4 w-4" />
            </button>
            <button
                type="button"
                aria-label="Toggle high contrast"
                aria-pressed={highContrast}
                onClick={toggleContrast}
            >
                <Contrast className="h-4 w-4" />
            </button>
            <button
                type="button"
                aria-label="Reset accessibility settings"
                onClick={resetPreferences}
            >
                <RotateCcw className="h-4 w-4" />
            </button>
        </div>
    );
}
