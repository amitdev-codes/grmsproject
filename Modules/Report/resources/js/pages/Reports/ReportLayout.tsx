import IndexLayout from '@/components/index-layout';
import { FileText } from 'lucide-react';
import type { ReactNode } from 'react';

export function ReportLayout({
    title,
    description,
    children,
}: {
    title: string;
    description: string;
    children: ReactNode;
}) {
    return (
        <IndexLayout
            title={title}
            breadcrumbs={[
                { label: 'Reports', icon: FileText },
                { label: title },
            ]}
        >
            <div className="space-y-5">
                <div>
                    <h1 className="text-2xl font-semibold tracking-tight">
                        {title}
                    </h1>
                    <p className="mt-1 text-sm text-muted-foreground">
                        {description}
                    </p>
                </div>
                {children}
            </div>
        </IndexLayout>
    );
}
