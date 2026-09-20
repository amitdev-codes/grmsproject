import type { ReactNode } from 'react';
import { Badge } from '@/components/ui/badge';

type BadgeVariant = 'default' | 'secondary' | 'destructive' | 'outline' | 'ghost';

export const LEVEL_VARIANT: Record<string, BadgeVariant> = {
    EMERGENCY: 'destructive',
    ALERT: 'destructive',
    CRITICAL: 'destructive',
    ERROR: 'destructive',
    WARNING: 'outline',
    NOTICE: 'secondary',
    INFO: 'default',
    DEBUG: 'secondary',
};

export function levelVariant(level: string): BadgeVariant {
    return LEVEL_VARIANT[level.toUpperCase()] ?? 'secondary';
}

export function jsonPreview(value: unknown): string {
    if (value === null || value === undefined) {
        return '—';
    }

    if (typeof value === 'string') {
        try {
            return JSON.stringify(JSON.parse(value), null, 2);
        } catch {
            return value;
        }
    }

    try {
        return JSON.stringify(value, null, 2);
    } catch {
        return String(value);
    }
}

export function JsonCell({ value }: { value: unknown }): ReactNode {
    const text = jsonPreview(value);

    return (
        <code
            title={text}
            className="block max-h-24 w-64 max-w-xs overflow-auto whitespace-pre-wrap break-all text-xs"
        >
            {text.length > 200 ? `${text.slice(0, 200)}…` : text}
        </code>
    );
}

export function formatDateTime(value: string | Date | null | undefined): string {
    if (!value) {
        return '—';
    }

    const dt = new Date(value);

    if (Number.isNaN(dt.getTime())) {
        return String(value);
    }

    return dt.toLocaleString();
}

export function LevelBadge({ level }: { level: string }) {
    return (
        <Badge variant={levelVariant(level)} className="font-mono uppercase">
            {level}
        </Badge>
    );
}
