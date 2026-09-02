import { Head } from '@inertiajs/react';
import type { ComponentProps, ReactNode, SyntheticEvent } from 'react';
import Breadcrumb from '@/components/Breadcrumb';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { useTranslation } from '@/hooks/use-translation';
import { cn } from '@/lib/utils';

type BreadcrumbItems = ComponentProps<typeof Breadcrumb>['items'];

interface FormLayoutProps {
    headTitle?: string;
    title: string;
    description?: string;
    breadcrumbs: BreadcrumbItems;
    onSubmit: (e: SyntheticEvent) => void;
    processing?: boolean;
    submitLabel?: string;
    cancelLabel?: string;
    onCancel?: () => void;
    children: ReactNode;
    contentClassName?: string;
    /** Suppress the default Cancel/Submit footer — e.g. a multi-step form
     * that renders its own Back/Next/Save controls per step instead. */
    hideFooter?: boolean;
}

export function FormLayout({
    headTitle,
    title,
    description,
    breadcrumbs,
    onSubmit,
    processing = false,
    submitLabel,
    cancelLabel,
    onCancel,
    children,
    contentClassName,
    hideFooter = false,
}: FormLayoutProps) {
    const { t } = useTranslation();

    return (
        <>
            <Head title={headTitle ?? title} />
            <div className="flex flex-col gap-6 p-6">
                <Breadcrumb items={breadcrumbs} />
                <form onSubmit={onSubmit}>
                    <Card className="px-2">
                        <CardHeader>
                            <CardTitle>{title}</CardTitle>
                            {/*{description && (*/}
                            {/*    <CardDescription>{description}</CardDescription>*/}
                            {/*)}*/}
                        </CardHeader>
                        <CardContent
                            className={cn(
                                'grid grid-cols-1 gap-4 sm:grid-cols-2',
                                contentClassName,
                            )}
                        >
                            {children}
                        </CardContent>
                        {!hideFooter && (
                            <CardFooter className="flex justify-end gap-2 border-t pt-4">
                                <Button
                                    type="button"
                                    variant="outline"
                                    onClick={
                                        onCancel ??
                                        (() => window.history.back())
                                    }
                                >
                                    {cancelLabel ?? t('Cancel')}
                                </Button>
                                <Button type="submit" disabled={processing}>
                                    {processing ? t('Saving…') : submitLabel}
                                </Button>
                            </CardFooter>
                        )}
                    </Card>
                </form>
            </div>
        </>
    );
}
