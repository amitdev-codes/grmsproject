export interface User {
    id: number;
    name: string;
    email: string;
    avatar_url?: string | null;
    email_verified_at?: string | null;
    locale?: string;
    role_names?: string;
}

export interface Auth {
    user: User | null;
}

export interface SharedData {
    auth: Auth;
    applicationSettings?: {
        project_name?: string | null;
        short_name?: string | null;
        tagline?: string | null;
        logo_url?: string | null;
        favicon_url?: string | null;
        [key: string]: unknown;
    };
    notifications: {
        count: number;
        latest: {
            id: string;
            title: string;
            message: string;
            action_url: string | null;
            open_url: string;
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
