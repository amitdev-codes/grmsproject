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
    notifications: {
        count: number;
        latest: {
            id: string;
            title: string;
            message: string;
            action_url: string | null;
            read_at: string | null;
            created_at: string | null;
        }[];
    };
    pendingGrievances: {
        count: number;
        href: string;
        label: string;
        visible: boolean;
    };
    locale: string;
    locales: Record<string, string>;
    translations: Record<string, string>;
    dynamicTranslations: Record<
        string,
        {
            en: string;
            st: string;
        }
    >;
    name: string;
    app_author: string;
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
