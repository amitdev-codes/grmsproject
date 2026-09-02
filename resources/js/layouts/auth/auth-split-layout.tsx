import { Link } from '@inertiajs/react';
import { Landmark, ShieldCheck } from 'lucide-react';
import type { PropsWithChildren } from 'react';
import { Badge } from '@/components/ui/badge';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';

import { home } from '@/routes';
import {
    MountainRoadBackdrop,
    RoadWatermark,
} from '@modules/Frontend/pages/site-shared';

const INVERSE_BG = 'var(--bg-inverse, #10203D)';
const INVERSE_TEXT = 'var(--text-on-inverse, #FFFFFF)';
const INVERSE_TEXT_SECONDARY =
    'var(--text-on-inverse-secondary, rgba(255,255,255,0.72))';
const ACCENT = 'var(--accent, #D4A017)';
const ACCENT_DARK = 'var(--accent-dark, #B8860B)';

const BRAND_EYEBROW = 'Grievance Redress Management System';
const BRAND_HEADLINE =
    'One place to raise, track, and resolve public service concerns.';
const BRAND_FOOTER = 'A service of the Government of Lesotho.';
const BRAND_POINTS = [
    'File a grievance in minutes, with or without giving your name',
    'Track progress any time with a single reference number',
    'Every case is routed straight to the right district office',
];

export default function AuthSplitLayout({
                                            children,
                                            title = '',
                                            description = '',
                                        }: PropsWithChildren<{ title?: string; description?: string }>) {
    return (
        <div
            className="grid min-h-svh md:grid-cols-2"
            style={{ background: 'var(--bg-page, #F7F5F0)' }}
        >
            <div
                className="relative hidden overflow-hidden md:block"
                style={{ background: INVERSE_BG }}
            >
                <div className="absolute inset-0" style={{ opacity: 0.28 }}>
                    <MountainRoadBackdrop />
                </div>
                <div
                    className="absolute inset-0"
                    style={{
                        background: `linear-gradient(165deg, ${INVERSE_BG} 20%, rgba(16,32,61,0.94) 100%)`,
                    }}
                />
                <div className="absolute inset-0" style={{ opacity: 0.12 }}>
                    <RoadWatermark />
                </div>
                <div
                    className="pointer-events-none absolute -top-24 -left-24 h-96 w-96 rounded-full"
                    style={{
                        background: ACCENT,
                        opacity: 0.12,
                        filter: 'blur(80px)',
                    }}
                />

                <div className="relative z-10 flex h-full flex-col justify-between p-12">
                    <Link
                        href={home()}
                        className="flex items-center gap-3 font-medium"
                        style={{ color: INVERSE_TEXT }}
                    >
                        <span
                            className="flex h-10 w-10 items-center justify-center rounded-full"
                            style={{ background: ACCENT }}
                        >
                            <Landmark
                                className="h-5 w-5"
                                style={{ color: '#10203D' }}
                            />
                        </span>
                        <span
                            className="font-display text-lg"
                            style={{ color: INVERSE_TEXT }}
                        >
                            GRMS Lesotho
                        </span>
                    </Link>

                    <div>
                        <Badge
                            variant="outline"
                            className="mb-4 border-none font-mono text-xs font-semibold"
                            style={{ color: '#10203D', background: ACCENT }}
                        >
                            {BRAND_EYEBROW}
                        </Badge>
                        <h2
                            className="font-display mb-6 max-w-sm text-3xl leading-tight font-semibold"
                            style={{ color: INVERSE_TEXT }}
                        >
                            {BRAND_HEADLINE}
                        </h2>
                        <ul className="max-w-sm space-y-3">
                            {BRAND_POINTS.map((point) => (
                                <li
                                    key={point}
                                    className="flex items-start gap-2.5 text-sm"
                                    style={{ color: INVERSE_TEXT_SECONDARY }}
                                >
                                    <ShieldCheck
                                        className="mt-0.5 h-4 w-4 shrink-0"
                                        style={{ color: ACCENT }}
                                    />
                                    <span>{point}</span>
                                </li>
                            ))}
                        </ul>
                    </div>

                    <p
                        className="text-xs"
                        style={{ color: INVERSE_TEXT_SECONDARY }}
                    >
                        {BRAND_FOOTER}
                    </p>
                </div>
            </div>

            {/* Form panel */}
            <div className="relative flex flex-col justify-center px-6 py-10 sm:px-10 md:px-16">
                <div className="mx-auto w-full max-w-md">
                    <Link
                        href={home()}
                        className="mb-8 flex items-center gap-2 font-medium md:hidden"
                    >
                        <span
                            className="flex h-9 w-9 items-center justify-center rounded-full"
                            style={{ background: ACCENT_DARK }}
                        >
                            <Landmark
                                className="h-4.5 w-4.5"
                                style={{ color: '#fff' }}
                            />
                        </span>
                        <span className="font-display text-lg">
                            GRMS Lesotho
                        </span>
                    </Link>

                    <Card
                        className="rounded-xl border shadow-sm"
                        style={{ borderColor: 'var(--border, #E4E1D8)' }}
                    >
                        {(title || description) && (
                            <CardHeader className="px-8 pt-8 pb-0 text-center">
                                {title && (
                                    <CardTitle className="font-display text-xl">
                                        {title}
                                    </CardTitle>
                                )}
                                {description && (
                                    <CardDescription>
                                        {description}
                                    </CardDescription>
                                )}
                            </CardHeader>
                        )}
                        <CardContent className="px-8 py-8">
                            {children}
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    );
}
