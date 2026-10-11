import { createInertiaApp, router } from '@inertiajs/react';
import React from 'react';
import { createRoot } from 'react-dom/client';
import { AccessibilityControls } from '@/components/accessibility-controls';
import { Toaster } from '@/components/ui/sonner';
import { TooltipProvider } from '@/components/ui/tooltip';
import { initializeTheme } from '@/hooks/use-appearance';
import AppLayout from '@/layouts/app-layout';
import AuthLayout from '@/layouts/auth-layout';
import { route } from 'ziggy-js';

let appName =
    import.meta.env.VITE_APP_NAME ||
    'Grievance Redressal Management System';

function updateApplicationFavicon(pageProps: Record<string, unknown>) {
    const applicationSettings = pageProps.applicationSettings as
        | {
              favicon_url?: string | null;
              project_name?: string | null;
          }
        | undefined;
    const faviconUrl = applicationSettings?.favicon_url;
    const projectName = applicationSettings?.project_name;

    if (projectName && projectName !== appName) {
        const previousAppName = appName;
        const pageTitle = document.title.endsWith(` - ${previousAppName}`)
            ? document.title.slice(0, -(previousAppName.length + 3))
            : document.title;

        appName = projectName;
        document.title = pageTitle
            ? `${pageTitle} - ${appName}`
            : appName;
    }

    document
        .querySelectorAll<HTMLLinkElement>(
            'link[rel="icon"], link[rel="apple-touch-icon"]',
        )
        .forEach((link) => {
            const href = faviconUrl || link.dataset.defaultHref;

            if (href) {
                link.href = href;

                if (faviconUrl) {
                    link.type = /\.ico(?:$|[?#])/i.test(href)
                        ? 'image/x-icon'
                        : 'image/png';
                } else if (link.dataset.defaultType) {
                    link.type = link.dataset.defaultType;
                }
            }
        });
}
// Glob patterns for page discovery
const mainPages = import.meta.glob('./pages/**/*.tsx');

if (typeof window !== 'undefined') {
    window.route = route;
}

// Try multiple glob patterns for module pages
const modulePages1 = import.meta.glob(
    '../../Modules/*/resources/js/pages/**/*.tsx',
);
const modulePages2 = import.meta.glob('/Modules/*/resources/js/pages/**/*.tsx');

// Merge all module pages
const modulePages = { ...modulePages1, ...modulePages2 };

async function resolvePageComponent(
    name: string,
): Promise<React.ComponentType> {
    // console.log('Resolving page:', name);
    // Check for namespace syntax (Module::PagePath)
    if (name.includes('::')) {
        const [moduleName, pagePath] = name.split('::');

        // Try different path formats
        const pathFormats = [
            `../../Modules/${moduleName}/resources/js/pages/${pagePath}.tsx`,
            `/Modules/${moduleName}/resources/js/pages/${pagePath}.tsx`,
        ];

        const expectedSuffix = `/Modules/${moduleName}/resources/js/pages/${pagePath}.tsx`;
        const pageEntry = Object.entries(modulePages).find(([path]) => {
            const normalizedPath = path.replaceAll('\\', '/');

            return (
                pathFormats.includes(path) ||
                normalizedPath.endsWith(expectedSuffix)
            );
        });
        const page = pageEntry?.[1];

        if (!page) {
            throw new Error(
                `Module page not found: ${name}\n` +
                    `Expected path: Modules/${moduleName}/resources/js/pages/${pagePath}.tsx\n` +
                    `Tried keys: ${pathFormats.join(', ')}`,
            );
        }

        const module = await page();

        return (module as { default: React.ComponentType }).default;
    }

    // Standard page resolution (main app)
    const pagePath = `./pages/${name}.tsx`;
    const page = mainPages[pagePath];

    if (!page) {
        throw new Error(
            `Page not found: ${name}\n` +
                `Expected path: resources/js/pages/${name}.tsx`,
        );
    }

    const module = await page();

    return (module as { default: React.ComponentType }).default;
}

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: resolvePageComponent, // <-- this line was missing
    layout: (name) => {
        switch (true) {
            case name.startsWith('Frontend::'):
                return null;
            case name.startsWith('auth/'):
                return AuthLayout;
            default:
                return AppLayout;
        }
    },
    strictMode: true,
    setup({ el, App, props }) {
        updateApplicationFavicon(props.initialPage.props);
        router.on('navigate', (event) => {
            updateApplicationFavicon(event.detail.page.props);
        });

        createRoot(el).render(
            <TooltipProvider delayDuration={0}>
                <App {...props} />
                <AccessibilityControls />
                <Toaster />
            </TooltipProvider>,
        );
    },
    progress: {
        color: '#4B5563',
    },
}).then();

// This will set light / dark mode on load...
initializeTheme();
