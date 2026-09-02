export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at?: string | null;
    locale?: string;
    role_names?: string;
}

export interface Auth {
    user: User | null;
}

export interface SharedData {
    auth: Auth;
    locale: string;
    locales: Record<string, string>;
    translations: Record<string, string>;
    flash: {
        success?: string | null;
        error?: string | null;
        import_failures?: unknown;
    };
    [key: string]: unknown;
}

import '@inertiajs/core';

declare module '@inertiajs/core' {
    interface PageProps extends SharedData {}
}
