// components/layouts/IndexLayout.tsx

import { Head } from '@inertiajs/react';
import Breadcrumb, { type BreadcrumbItem } from '@/components/Breadcrumb';

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
    return (
        <>
            <Head title={headTitle ?? title} />

            <div className="flex flex-col gap-2 p-2">
                <Breadcrumb items={breadcrumbs} />
                {children}
            </div>
        </>
    );
}
