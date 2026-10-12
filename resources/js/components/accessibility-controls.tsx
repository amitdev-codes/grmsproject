import {
    Accessibility,
    Contrast,
    Eye,
    Minus,
    Moon,
    Plus,
    RotateCcw,
    Sun,
    Volume2,
    VolumeX,
} from 'lucide-react';
import { useEffect, useState } from 'react';
import { createPortal } from 'react-dom';
import { useAppearance } from '@/hooks/use-appearance';

const FONT_SCALE_KEY = 'accessibility-font-scale';
const COLOR_FILTER_KEY = 'accessibility-color-filter';
const ACCESSIBILITY_MODE_KEY = 'accessibility-mode';
const LARGE_CURSOR_KEY = 'accessibility-large-cursor';
const BANDWIDTH_KEY = 'accessibility-bandwidth';
const MIN_FONT_SCALE = 0.8;
const MAX_FONT_SCALE = 1.3;
const FONT_SCALE_STEP = 0.1;

type ColorFilter =
    | 'none'
    | 'monochrome'
    | 'high-contrast'
    | 'protanopia'
    | 'deuteranopia'
    | 'tritanopia';
type BandwidthMode = 'normal' | 'low';

const colorFilters: { value: ColorFilter; label: string }[] = [
    { value: 'none', label: 'No color filter' },
    { value: 'monochrome', label: 'Monochrome' },
    { value: 'high-contrast', label: 'High contrast' },
    { value: 'protanopia', label: 'Protanopia simulation' },
    { value: 'deuteranopia', label: 'Deuteranopia simulation' },
    { value: 'tritanopia', label: 'Tritanopia simulation' },
];

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

const readColorFilter = (): ColorFilter => {
    if (typeof window === 'undefined') {
        return 'none';
    }

    const storedFilter = window.localStorage.getItem(COLOR_FILTER_KEY);
    const validFilter = colorFilters.find(
        ({ value }) => value === storedFilter,
    );

    if (validFilter) {
        return validFilter.value;
    }

    return window.localStorage.getItem('accessibility-contrast') === 'true'
        ? 'high-contrast'
        : 'none';
};

const readBooleanPreference = (key: string) => {
    return (
        typeof window !== 'undefined' &&
        window.localStorage.getItem(key) === 'true'
    );
};

const readBandwidthMode = (): BandwidthMode => {
    return typeof window !== 'undefined' &&
        window.localStorage.getItem(BANDWIDTH_KEY) === 'low'
        ? 'low'
        : 'normal';
};

export function AccessibilityControls() {
    const [isOpen, setIsOpen] = useState(false);
    const [fontScale, setFontScale] = useState(readFontScale);
    const [colorFilter, setColorFilter] = useState(readColorFilter);
    const [accessibilityMode, setAccessibilityMode] = useState(() =>
        readBooleanPreference(ACCESSIBILITY_MODE_KEY),
    );
    const [largeCursor, setLargeCursor] = useState(() =>
        readBooleanPreference(LARGE_CURSOR_KEY),
    );
    const [bandwidthMode, setBandwidthMode] =
        useState<BandwidthMode>(readBandwidthMode);
    const [isSpeaking, setIsSpeaking] = useState(false);
    const { resolvedAppearance, updateAppearance } = useAppearance();

    useEffect(() => {
        const root = document.documentElement;

        root.style.setProperty('--user-font-scale', String(fontScale));
        root.dataset.colorFilter = colorFilter;
        root.classList.toggle('high-contrast', colorFilter === 'high-contrast');
        root.classList.toggle('accessibility-mode', accessibilityMode);
        root.classList.toggle('large-cursor', largeCursor);
    }, [fontScale, colorFilter, accessibilityMode, largeCursor]);

    useEffect(() => {
        document.documentElement.dataset.bandwidth = bandwidthMode;
        const deferredImages = new Set<HTMLImageElement>();
        const deferImages = (container: ParentNode) => {
            const images = [
                ...(container instanceof HTMLImageElement ? [container] : []),
                ...container.querySelectorAll('img'),
            ];

            images.forEach((image) => {
                if (!image.hasAttribute('loading')) {
                    image.setAttribute('loading', 'lazy');
                    deferredImages.add(image);
                }
            });
        };

        if (bandwidthMode === 'low') {
            deferImages(document);
        }

        const observer =
            bandwidthMode === 'low'
                ? new MutationObserver((mutations) => {
                      mutations.forEach(({ addedNodes }) => {
                          addedNodes.forEach((node) => {
                              if (node instanceof Element) {
                                  deferImages(node);
                              }
                          });
                      });
                  })
                : null;

        observer?.observe(document.body, { childList: true, subtree: true });

        return () => {
            observer?.disconnect();
            deferredImages.forEach((image) => image.removeAttribute('loading'));
        };
    }, [bandwidthMode]);

    useEffect(() => {
        return () => window.speechSynthesis?.cancel();
    }, []);

    const updateFontScale = (nextScale: number) => {
        const scale = clampFontScale(nextScale);

        setFontScale(scale);
        window.localStorage.setItem(FONT_SCALE_KEY, String(scale));
        document.documentElement.style.setProperty(
            '--user-font-scale',
            String(scale),
        );
    };

    const updateColorFilter = (nextFilter: ColorFilter) => {
        setColorFilter(nextFilter);
        window.localStorage.setItem(COLOR_FILTER_KEY, nextFilter);
        window.localStorage.removeItem('accessibility-contrast');
        document.documentElement.dataset.colorFilter = nextFilter;
    };

    const updateAccessibilityMode = (enabled: boolean) => {
        setAccessibilityMode(enabled);
        window.localStorage.setItem(ACCESSIBILITY_MODE_KEY, String(enabled));
        document.documentElement.classList.toggle(
            'accessibility-mode',
            enabled,
        );
    };

    const updateLargeCursor = (enabled: boolean) => {
        setLargeCursor(enabled);
        window.localStorage.setItem(LARGE_CURSOR_KEY, String(enabled));
        document.documentElement.classList.toggle('large-cursor', enabled);
    };

    const updateBandwidthMode = (mode: BandwidthMode) => {
        setBandwidthMode(mode);
        window.localStorage.setItem(BANDWIDTH_KEY, mode);
        document.documentElement.dataset.bandwidth = mode;
    };

    const toggleDarkMode = () => {
        updateAppearance(resolvedAppearance === 'dark' ? 'light' : 'dark');
    };

    const togglePageSpeech = () => {
        if (isSpeaking) {
            window.speechSynthesis.cancel();
            setIsSpeaking(false);

            return;
        }

        if (!('speechSynthesis' in window)) {
            return;
        }

        const pageContent =
            document.querySelector('main') ??
            document.querySelector('.grms-root') ??
            document.querySelector('#app');
        const text = pageContent?.textContent?.replace(/\s+/g, ' ').trim();

        if (!text) {
            return;
        }

        const utterance = new SpeechSynthesisUtterance(text);
        utterance.lang = document.documentElement.lang || 'en';
        utterance.onend = () => setIsSpeaking(false);
        utterance.onerror = () => setIsSpeaking(false);

        window.speechSynthesis.cancel();
        window.speechSynthesis.speak(utterance);
        setIsSpeaking(true);
    };

    const resetPreferences = () => {
        updateFontScale(1);
        updateColorFilter('none');
        updateAccessibilityMode(false);
        updateLargeCursor(false);
        updateBandwidthMode('normal');
        updateAppearance('system');
        window.localStorage.removeItem(FONT_SCALE_KEY);
        window.localStorage.removeItem(COLOR_FILTER_KEY);
        window.localStorage.removeItem(ACCESSIBILITY_MODE_KEY);
        window.localStorage.removeItem(LARGE_CURSOR_KEY);
        window.localStorage.removeItem(BANDWIDTH_KEY);
        window.localStorage.removeItem('accessibility-contrast');

        if ('speechSynthesis' in window) {
            window.speechSynthesis.cancel();
        }

        setIsSpeaking(false);
    };

    const panel = (
        <>
            <svg
                className="accessibility-svg-filters"
                aria-hidden="true"
                focusable="false"
            >
                <filter id="accessibility-protanopia">
                    <feColorMatrix
                        type="matrix"
                        values="0.567 0.433 0 0 0 0.558 0.442 0 0 0 0 0.242 0.758 0 0 0 0 0 1 0"
                    />
                </filter>
                <filter id="accessibility-deuteranopia">
                    <feColorMatrix
                        type="matrix"
                        values="0.625 0.375 0 0 0 0.7 0.3 0 0 0 0 0.3 0.7 0 0 0 0 0 1 0"
                    />
                </filter>
                <filter id="accessibility-tritanopia">
                    <feColorMatrix
                        type="matrix"
                        values="0.95 0.05 0 0 0 0 0.433 0.567 0 0 0 0.475 0.525 0 0 0 0 0 1 0"
                    />
                </filter>
            </svg>
            <div className="accessibility-controls">
                <button
                    type="button"
                    className="accessibility-trigger"
                    aria-label={
                        isOpen
                            ? 'Close accessibility tools'
                            : 'Open accessibility tools'
                    }
                    aria-expanded={isOpen}
                    aria-controls="accessibility-panel"
                    onClick={() => setIsOpen((open) => !open)}
                >
                    <Accessibility className="h-5 w-5" />
                </button>
                {isOpen && (
                    <section
                        id="accessibility-panel"
                        className="accessibility-panel"
                        aria-labelledby="accessibility-title"
                    >
                        <div className="accessibility-panel-heading">
                            <h2 id="accessibility-title">
                                Accessibility tools
                            </h2>
                            <button
                                type="button"
                                className="accessibility-panel-close"
                                aria-label="Close accessibility tools"
                                onClick={() => setIsOpen(false)}
                            >
                                ×
                            </button>
                        </div>

                        <fieldset className="accessibility-group">
                            <legend>Text size</legend>
                            <div className="accessibility-button-row">
                                <button
                                    type="button"
                                    aria-label="Decrease text size"
                                    disabled={fontScale <= MIN_FONT_SCALE}
                                    onClick={() =>
                                        updateFontScale(
                                            fontScale - FONT_SCALE_STEP,
                                        )
                                    }
                                >
                                    <Minus className="h-4 w-4" />
                                </button>
                                <output
                                    aria-live="polite"
                                    aria-label="Text size"
                                >
                                    {Math.round(fontScale * 100)}%
                                </output>
                                <button
                                    type="button"
                                    aria-label="Increase text size"
                                    disabled={fontScale >= MAX_FONT_SCALE}
                                    onClick={() =>
                                        updateFontScale(
                                            fontScale + FONT_SCALE_STEP,
                                        )
                                    }
                                >
                                    <Plus className="h-4 w-4" />
                                </button>
                                <button
                                    type="button"
                                    className="accessibility-reset"
                                    onClick={() => updateFontScale(1)}
                                >
                                    Reset text
                                </button>
                            </div>
                        </fieldset>

                        <div className="accessibility-group">
                            <label htmlFor="accessibility-color-filter">
                                Color mode or filter
                            </label>
                            <select
                                id="accessibility-color-filter"
                                value={colorFilter}
                                onChange={(event) =>
                                    updateColorFilter(
                                        event.target.value as ColorFilter,
                                    )
                                }
                            >
                                {colorFilters.map(({ value, label }) => (
                                    <option key={value} value={value}>
                                        {label}
                                    </option>
                                ))}
                            </select>
                            <div
                                className="accessibility-color-preview"
                                aria-label="Color preview"
                            >
                                <span className="preview-red" />
                                <span className="preview-green" />
                                <span className="preview-blue" />
                                <span className="preview-yellow" />
                                <span className="preview-neutral" />
                                <span className="sr-only">
                                    Color preview: red, green, blue, yellow, and
                                    neutral.
                                </span>
                            </div>
                        </div>

                        <div className="accessibility-group">
                            <span className="accessibility-group-label">
                                Display
                            </span>
                            <button
                                type="button"
                                className="accessibility-option"
                                aria-pressed={resolvedAppearance === 'dark'}
                                onClick={toggleDarkMode}
                            >
                                {resolvedAppearance === 'dark' ? (
                                    <Sun className="h-4 w-4" />
                                ) : (
                                    <Moon className="h-4 w-4" />
                                )}
                                {resolvedAppearance === 'dark'
                                    ? 'Dark mode on'
                                    : 'Dark mode off'}
                            </button>
                            <button
                                type="button"
                                className="accessibility-option"
                                aria-pressed={largeCursor}
                                onClick={() => updateLargeCursor(!largeCursor)}
                            >
                                <Eye className="h-4 w-4" />
                                Large cursor {largeCursor ? 'on' : 'off'}
                            </button>
                            <button
                                type="button"
                                className="accessibility-option"
                                aria-pressed={accessibilityMode}
                                onClick={() =>
                                    updateAccessibilityMode(!accessibilityMode)
                                }
                            >
                                <Contrast className="h-4 w-4" />
                                {accessibilityMode
                                    ? 'Exit accessibility mode'
                                    : 'Accessibility mode'}
                            </button>
                        </div>

                        <div className="accessibility-group">
                            <label htmlFor="accessibility-bandwidth">
                                Bandwidth
                            </label>
                            <select
                                id="accessibility-bandwidth"
                                value={bandwidthMode}
                                onChange={(event) =>
                                    updateBandwidthMode(
                                        event.target.value as BandwidthMode,
                                    )
                                }
                            >
                                <option value="normal">Normal bandwidth</option>
                                <option value="low">Low bandwidth</option>
                            </select>
                            {bandwidthMode === 'low' && (
                                <p className="accessibility-hint">
                                    Images are deferred and decorative
                                    animations are reduced.
                                </p>
                            )}
                        </div>

                        <div className="accessibility-group">
                            <span className="accessibility-group-label">
                                Read aloud
                            </span>
                            <button
                                type="button"
                                className="accessibility-option"
                                disabled={!('speechSynthesis' in window)}
                                aria-pressed={isSpeaking}
                                onClick={togglePageSpeech}
                            >
                                {isSpeaking ? (
                                    <VolumeX className="h-4 w-4" />
                                ) : (
                                    <Volume2 className="h-4 w-4" />
                                )}
                                {isSpeaking
                                    ? 'Stop reading'
                                    : 'Read page aloud'}
                            </button>
                            {!('speechSynthesis' in window) && (
                                <p className="accessibility-hint">
                                    Read aloud is not supported by this browser.
                                </p>
                            )}
                        </div>

                        <button
                            type="button"
                            className="accessibility-reset-all"
                            onClick={resetPreferences}
                        >
                            <RotateCcw className="h-4 w-4" />
                            Reset all accessibility settings
                        </button>
                    </section>
                )}
            </div>
        </>
    );

    return createPortal(panel, document.body);
}
