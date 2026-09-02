import { createInertiaApp } from '@inertiajs/react';
import React from 'react';
import { Toaster } from '@/components/ui/sonner';
import { TooltipProvider } from '@/components/ui/tooltip';
import { initializeTheme } from '@/hooks/use-appearance';
import AppLayout from '@/layouts/app-layout';
import AuthLayout from '@/layouts/auth-layout';
import { route } from 'ziggy-js';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';
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

        let page = null;


        for (const path of pathFormats) {
            if (modulePages[path]) {
                page = modulePages[path];
                break;
            }
        }

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
    withApp(app) {
        return (
            <TooltipProvider delayDuration={0}>
                {app}
                <Toaster />
            </TooltipProvider>
        );
    },
    progress: {
        color: '#4B5563',
    },
}).then( );

// This will set light / dark mode on load...
initializeTheme();
