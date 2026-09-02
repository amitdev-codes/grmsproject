import { router } from '@inertiajs/react';
import { ChevronRight, Home, ArrowLeft } from 'lucide-react';
import type { LucideIcon } from 'lucide-react';
import { cn } from '@/lib/utils';
import type { RouteDefinition } from '@/wayfinder';

// ─── Types ─────────────────────────────────────────────

export interface BreadcrumbItem {
    label: string;
    href?: string | RouteDefinition<'get'>;
    icon?: LucideIcon;
}

interface BreadcrumbProps {
    items?: BreadcrumbItem[];
    className?: string;
    showHome?: boolean; // kept for API compat, always true now
}

// ─── Component ─────────────────────────────────────────

export default function Breadcrumb({ items = [], className }: BreadcrumbProps) {
    const all: BreadcrumbItem[] = [
        { label: 'Home', href: '/admin/dashboard', icon: Home },
        ...items,
    ];

    return (
        <div className={cn(className)}>
            <div className="flex items-center justify-between rounded-lg border border-border bg-card px-3 py-2 shadow-sm">
                {/* ─── LEFT: Back Button ─── */}
                <button
                    type="button"
                    onClick={() => window.history.back()}
                    className={cn(
                        'flex items-center gap-1.5 rounded-md px-2 py-1 text-xs',
                        'text-muted-foreground hover:text-foreground',
                        'transition-colors hover:bg-accent',
                    )}
                >
                    <ArrowLeft className="h-3.5 w-3.5" />
                    Back
                </button>

                {/* ─── RIGHT: Breadcrumb Trail ─── */}
                <nav aria-label="Breadcrumb">
                    <ol className="flex flex-wrap items-center gap-1 text-xs">
                        {all.map((item, idx) => {
                            const isLast = idx === all.length - 1;
                            const Icon = item.icon;

                            return (
                                <li
                                    key={idx}
                                    className="flex items-center gap-1"
                                >
                                    {idx > 0 && (
                                        <ChevronRight className="h-3 w-3 text-muted-foreground/40" />
                                    )}

                                    {isLast || !item.href ? (
                                        <span
                                            className={cn(
                                                'flex items-center gap-1',
                                                isLast
                                                    ? 'font-medium text-foreground'
                                                    : 'text-muted-foreground',
                                            )}
                                        >
                                            {Icon && (
                                                <Icon className="h-3 w-3" />
                                            )}
                                            {item.label}
                                        </span>
                                    ) : (
                                        <button
                                            type="button"
                                            onClick={() =>
                                                router.visit(item.href!)
                                            }
                                            className={cn(
                                                'flex items-center gap-1 rounded px-1 py-0.5',
                                                'text-muted-foreground hover:text-foreground',
                                                'transition-colors hover:bg-accent',
                                            )}
                                        >
                                            {Icon && (
                                                <Icon className="h-3 w-3" />
                                            )}
                                            {item.label}
                                        </button>
                                    )}
                                </li>
                            );
                        })}
                    </ol>
                </nav>
            </div>
        </div>
    );
}
