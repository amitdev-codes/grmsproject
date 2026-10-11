// components/layouts/IndexLayout.tsx

import { Head } from '@inertiajs/react';
import Breadcrumb from '@/components/Breadcrumb';
import type { BreadcrumbItem } from '@/components/Breadcrumb';
import { useTranslation } from '@/hooks/use-translation';

interface IndexLayoutProps {
    title: string;
    headTitle?: string;
    breadcrumbs?: BreadcrumbItem[];
    children: React.ReactNode;
}

export default function IndexLayout({
    title,
    headTitle,
    breadcrumbs = [],
    children,
}: IndexLayoutProps) {
    const { tLabel } = useTranslation();
    const translatedTitle = tLabel(title);

    return (
        <>
            <Head title={headTitle ?? translatedTitle} />

            <div className="flex flex-col gap-3 p-3 sm:gap-4 sm:p-4 lg:p-6">
                <Breadcrumb items={breadcrumbs} />
                {children}
            </div>
        </>
    );
}
