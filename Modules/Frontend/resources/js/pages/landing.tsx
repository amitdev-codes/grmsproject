import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import { Tabs, TabsList, TabsTrigger, TabsContent } from '@/components/ui/tabs';
import { Link } from '@inertiajs/react';
import {
    Route,
    Clock,
    ShieldCheck,
    BarChart3,
    Bell,
    FileText,
    Lock,
    ArrowRight,
    CheckCircle2,
    Users,
    Home,
    HardHat,
    ClipboardCheck,
    MessageSquareText,
    UserCheck,
    Send,
    Search,
} from 'lucide-react';
import React, { useEffect, useState } from 'react';
import {
    BarChart,
    Bar,
    AreaChart,
    Area,
    PieChart,
    Pie,
    Cell,
    XAxis,
    YAxis,
    CartesianGrid,
    Tooltip,
    Legend,
    ResponsiveContainer,
} from 'recharts';
import type {
    IconType} from './site-shared';
import {
    PageShell,
    NavBar,
    Footer,
    MountainRoadBackdrop,
    RoadWatermark,
    RoadDivider,
    useI18n,
    FILE_GRIEVANCE_URL,
} from './site-shared';

// Sample data — wire this up to your real case records (e.g. a /api/grievances/monthly endpoint).
const MONTHLY_DATA = [
    { month: 'Jan', received: 42, resolved: 35 },
    { month: 'Feb', received: 51, resolved: 44 },
    { month: 'Mar', received: 38, resolved: 36 },
    { month: 'Apr', received: 60, resolved: 52 },
    { month: 'May', received: 47, resolved: 45 },
    { month: 'Jun', received: 55, resolved: 50 },
];

// Running total across the year — feeds the area chart.
const CUMULATIVE_DATA = MONTHLY_DATA.reduce<
    { month: string; received: number; resolved: number }[]
>((acc, m, i) => {
    const prev = acc[i - 1] ?? { received: 0, resolved: 0 };
    acc.push({
        month: m.month,
        received: prev.received + m.received,
        resolved: prev.resolved + m.resolved,
    });

    return acc;
}, []);

// Sample data — wire up to your live case table.
const STATUS_PIE_DATA = [
    { key: 'statusResolved', value: 148 },
    { key: 'statusInProgress', value: 34 },
    { key: 'statusPending', value: 21 },
];
const STATUS_COLORS = [
    'var(--resolved)',
    'var(--accent)',
    'var(--text-secondary)',
];

const RESOLUTION_RATE = 82; // percent, current year to date
const RATIO_DONUT_DATA = [
    { key: 'resolvedLabel', value: RESOLUTION_RATE },
    { key: 'unresolved', value: 100 - RESOLUTION_RATE },
];
const RATIO_COLORS = ['var(--resolved)', 'var(--border)'];

const TICKET_ICONS: IconType[] = [
    FileText,
    ClipboardCheck,
    UserCheck,
    CheckCircle2,
];

function useTicketCycle(intervalMs = 2200) {
    const [stage, setStage] = useState(0);
    useEffect(() => {
        const id = setInterval(
            () => setStage((s) => (s + 1) % TICKET_ICONS.length),
            intervalMs,
        );

        return () => clearInterval(id);
    }, [intervalMs]);

    return stage;
}

function TicketMockup() {
    const stage = useTicketCycle();
    const { t } = useI18n();
    const resolved = stage === t.ticket.stages.length - 1;

    return (
        <div
            className="relative rounded-md border p-6 shadow-sm"
            style={{
                borderColor: 'var(--border)',
                background: 'var(--bg-raised)',
            }}
        >
            <div className="mb-4 flex items-center justify-between">
                <span
                    className="font-mono text-xs"
                    style={{ color: 'var(--text-secondary)' }}
                >
                    CASE #GRM-2026-01147
                </span>
                <Badge
                    variant="outline"
                    className="font-mono text-xs"
                    style={{
                        borderColor: 'var(--border)',
                        color: 'var(--text-secondary)',
                    }}
                >
                    {t.ticket.dept}
                </Badge>
            </div>
            <h4 className="font-display mb-1 text-lg font-semibold">
                {t.ticket.title}
            </h4>
            <p
                className="mb-5 text-sm"
                style={{ color: 'var(--text-secondary)' }}
            >
                {t.ticket.meta}
            </p>
            <div className="mb-5 space-y-3">
                {t.ticket.stages.map((label, i) => {
                    const Icon = TICKET_ICONS[i];
                    const active = i <= stage;
                    const isCurrent = i === stage;
                    const isLast = i === t.ticket.stages.length - 1;

                    return (
                        <div key={label} className="flex items-center gap-3">
                            <div
                                className="flex h-7 w-7 shrink-0 items-center justify-center rounded-full transition-colors duration-500"
                                style={{
                                    background: active
                                        ? isLast
                                            ? 'var(--resolved)'
                                            : 'var(--accent)'
                                        : 'transparent',
                                    border: `1.5px solid ${active ? 'transparent' : 'var(--border)'}`,
                                }}
                            >
                                <Icon
                                    className="h-3.5 w-3.5"
                                    style={{
                                        color: active
                                            ? '#fff'
                                            : 'var(--text-secondary)',
                                    }}
                                />
                            </div>
                            <span
                                className="text-sm transition-colors duration-500"
                                style={{
                                    color: active
                                        ? 'var(--text-primary)'
                                        : 'var(--text-secondary)',
                                    fontWeight: isCurrent ? 600 : 400,
                                }}
                            >
                                {label}
                            </span>
                            {i < t.ticket.stages.length - 1 && (
                                <div
                                    className="h-px flex-1"
                                    style={{
                                        background:
                                            active && i < stage
                                                ? 'var(--accent)'
                                                : 'var(--border)',
                                    }}
                                />
                            )}
                        </div>
                    );
                })}
            </div>
            <Separator style={{ background: 'var(--border)' }} />
            <div className="flex items-center justify-between pt-4">
                <span
                    className="text-xs"
                    style={{ color: 'var(--text-secondary)' }}
                >
                    {t.ticket.sla}
                </span>
                <span
                    className="font-mono text-xs"
                    style={{ color: 'var(--text-secondary)' }}
                >
                    {t.ticket.day}
                </span>
            </div>
            <div
                className="font-display absolute -top-4 -right-4 flex h-20 w-20 items-center justify-center rounded-full text-center text-xs font-semibold tracking-wide uppercase transition-all duration-500"
                style={{
                    border: '2px solid var(--resolved)',
                    color: 'var(--resolved)',
                    transform: resolved
                        ? 'rotate(-12deg) scale(1)'
                        : 'rotate(-12deg) scale(0)',
                    opacity: resolved ? 1 : 0,
                    background: 'var(--resolved-bg)',
                }}
            >
                {t.ticket.resolvedStamp}
            </div>
        </div>
    );
}

function Hero() {
    const { t } = useI18n();

    return (
        <section className="relative overflow-hidden">
            <MountainRoadBackdrop />
            <div className="relative mx-auto grid max-w-6xl items-center gap-12 px-6 pt-16 pb-24 md:grid-cols-2">
                <div>
                    <Badge
                        variant="outline"
                        className="mb-5 font-mono text-xs"
                        style={{
                            borderColor: 'var(--accent)',
                            color: 'var(--accent-dark)',
                            background: 'var(--bg-raised)',
                        }}
                    >
                        {t.hero.eyebrow}
                    </Badge>
                    <h1 className="font-display mb-6 text-4xl leading-[1.1] font-semibold md:text-5xl">
                        {t.hero.headline[0]}
                        <br />
                        {t.hero.headline[1]}
                    </h1>
                    <p
                        className="mb-8 max-w-lg text-lg"
                        style={{ color: 'var(--text-secondary)' }}
                    >
                        {t.hero.sub}
                    </p>
                    <div className="mb-8 flex flex-wrap gap-3">
                        <Button
                            asChild
                            size="lg"
                            style={{
                                background: 'var(--accent)',
                                color: '#14213D',
                            }}
                        >
                            <Link href={FILE_GRIEVANCE_URL}>
                                {t.hero.ctaPrimary}
                                <ArrowRight className="ml-1.5 h-4 w-4" />
                            </Link>
                        </Button>

                        <Button
                            size="lg"
                            variant="outline"
                            style={{
                                borderColor: 'var(--text-primary)',
                                color: 'var(--text-primary)',
                            }}
                            onClick={() =>
                                document
                                    .getElementById('how-it-works')
                                    ?.scrollIntoView({
                                        behavior: 'smooth',
                                    })
                            }
                        >
                            {t.hero.ctaSecondary}
                        </Button>
                    </div>
                    <p
                        className="text-sm"
                        style={{ color: 'var(--text-secondary)' }}
                    >
                        {t.hero.trust}
                    </p>
                </div>
                <TicketMockup />
            </div>
        </section>
    );
}

function ChartCard({
    title,
    children,
}: {
    title: string;
    children: React.ReactNode;
}) {
    return (
        <div
            className="rounded-md border p-5"
            style={{
                borderColor: 'var(--border)',
                background: 'var(--bg-page)',
            }}
        >
            <h3 className="mb-4 text-sm font-semibold">{title}</h3>
            {children}
        </div>
    );
}

function ResolutionChart() {
    const { t } = useI18n();
    const tooltipStyle = {
        background: 'var(--bg-page)',
        border: '1px solid var(--border)',
        borderRadius: 6,
        color: 'var(--text-primary)',
        fontSize: 13,
    };
    const statusData = STATUS_PIE_DATA.map((d) => ({
        name: t.chart[
            d.key as 'statusResolved' | 'statusInProgress' | 'statusPending'
        ],
        value: d.value,
    }));
    const ratioData = RATIO_DONUT_DATA.map((d) => ({
        name: t.chart[d.key as 'resolvedLabel' | 'unresolved'],
        value: d.value,
    }));

    return (
        <section
            className="border-y"
            style={{
                borderColor: 'var(--border)',
                background: 'var(--bg-raised)',
            }}
        >
            <div className="mx-auto max-w-6xl px-6 py-14">
                <h2 className="font-display mb-1 text-center text-2xl font-semibold md:text-3xl">
                    {t.chart.heading}
                </h2>
                <p
                    className="mb-8 text-center text-sm"
                    style={{ color: 'var(--text-secondary)' }}
                >
                    {t.chart.sub}
                </p>

                {/* Monthly received vs resolved */}
                <ChartCard
                    title={`${t.chart.legendReceived} / ${t.chart.legendResolved} — month by month`}
                >
                    <div style={{ width: '100%', height: 280 }}>
                        <ResponsiveContainer>
                            <BarChart data={MONTHLY_DATA} barGap={6}>
                                <CartesianGrid
                                    strokeDasharray="3 3"
                                    stroke="var(--border)"
                                    vertical={false}
                                />
                                <XAxis
                                    dataKey="month"
                                    stroke="var(--text-secondary)"
                                    fontSize={12}
                                    tickLine={false}
                                    axisLine={{ stroke: 'var(--border)' }}
                                />
                                <YAxis
                                    stroke="var(--text-secondary)"
                                    fontSize={12}
                                    tickLine={false}
                                    axisLine={false}
                                />
                                <Tooltip contentStyle={tooltipStyle} />
                                <Legend
                                    wrapperStyle={{
                                        fontSize: 13,
                                        color: 'var(--text-secondary)',
                                    }}
                                />
                                <Bar
                                    dataKey="received"
                                    name={t.chart.legendReceived}
                                    fill="var(--text-secondary)"
                                    radius={[4, 4, 0, 0]}
                                />
                                <Bar
                                    dataKey="resolved"
                                    name={t.chart.legendResolved}
                                    fill="var(--resolved)"
                                    radius={[4, 4, 0, 0]}
                                />
                            </BarChart>
                        </ResponsiveContainer>
                    </div>
                </ChartCard>

                <div className="mt-5 grid gap-5 md:grid-cols-3">
                    {/* Cumulative year-to-date trend */}
                    <ChartCard title={t.chart.trendHeading}>
                        <div style={{ width: '100%', height: 220 }}>
                            <ResponsiveContainer>
                                <AreaChart data={CUMULATIVE_DATA}>
                                    <defs>
                                        <linearGradient
                                            id="receivedFill"
                                            x1="0"
                                            y1="0"
                                            x2="0"
                                            y2="1"
                                        >
                                            <stop
                                                offset="0%"
                                                stopColor="var(--text-secondary)"
                                                stopOpacity={0.35}
                                            />
                                            <stop
                                                offset="100%"
                                                stopColor="var(--text-secondary)"
                                                stopOpacity={0}
                                            />
                                        </linearGradient>
                                        <linearGradient
                                            id="resolvedFill"
                                            x1="0"
                                            y1="0"
                                            x2="0"
                                            y2="1"
                                        >
                                            <stop
                                                offset="0%"
                                                stopColor="var(--resolved)"
                                                stopOpacity={0.4}
                                            />
                                            <stop
                                                offset="100%"
                                                stopColor="var(--resolved)"
                                                stopOpacity={0}
                                            />
                                        </linearGradient>
                                    </defs>
                                    <CartesianGrid
                                        strokeDasharray="3 3"
                                        stroke="var(--border)"
                                        vertical={false}
                                    />
                                    <XAxis
                                        dataKey="month"
                                        stroke="var(--text-secondary)"
                                        fontSize={11}
                                        tickLine={false}
                                        axisLine={{ stroke: 'var(--border)' }}
                                    />
                                    <YAxis
                                        stroke="var(--text-secondary)"
                                        fontSize={11}
                                        tickLine={false}
                                        axisLine={false}
                                    />
                                    <Tooltip contentStyle={tooltipStyle} />
                                    <Area
                                        type="monotone"
                                        dataKey="received"
                                        name={t.chart.legendReceived}
                                        stroke="var(--text-secondary)"
                                        fill="url(#receivedFill)"
                                        strokeWidth={2}
                                    />
                                    <Area
                                        type="monotone"
                                        dataKey="resolved"
                                        name={t.chart.legendResolved}
                                        stroke="var(--resolved)"
                                        fill="url(#resolvedFill)"
                                        strokeWidth={2}
                                    />
                                </AreaChart>
                            </ResponsiveContainer>
                        </div>
                    </ChartCard>

                    {/* Status breakdown */}
                    <ChartCard title={t.chart.statusHeading}>
                        <div style={{ width: '100%', height: 220 }}>
                            <ResponsiveContainer>
                                <PieChart>
                                    <Pie
                                        data={statusData}
                                        dataKey="value"
                                        nameKey="name"
                                        cx="50%"
                                        cy="50%"
                                        outerRadius={75}
                                        label={{
                                            fontSize: 11,
                                            fill: 'var(--text-secondary)',
                                        }}
                                    >
                                        {statusData.map((_, i) => (
                                            <Cell
                                                key={i}
                                                fill={STATUS_COLORS[i]}
                                            />
                                        ))}
                                    </Pie>
                                    <Tooltip contentStyle={tooltipStyle} />
                                    <Legend
                                        wrapperStyle={{
                                            fontSize: 11,
                                            color: 'var(--text-secondary)',
                                        }}
                                    />
                                </PieChart>
                            </ResponsiveContainer>
                        </div>
                    </ChartCard>

                    {/* Resolution rate donut with center % */}
                    <ChartCard title={t.chart.ratioHeading}>
                        <div
                            className="relative"
                            style={{ width: '100%', height: 220 }}
                        >
                            <ResponsiveContainer>
                                <PieChart>
                                    <Pie
                                        data={ratioData}
                                        dataKey="value"
                                        nameKey="name"
                                        cx="50%"
                                        cy="50%"
                                        innerRadius={55}
                                        outerRadius={75}
                                        startAngle={90}
                                        endAngle={-270}
                                    >
                                        {ratioData.map((_, i) => (
                                            <Cell
                                                key={i}
                                                fill={RATIO_COLORS[i]}
                                            />
                                        ))}
                                    </Pie>
                                    <Tooltip contentStyle={tooltipStyle} />
                                </PieChart>
                            </ResponsiveContainer>
                            <div className="pointer-events-none absolute inset-0 flex flex-col items-center justify-center">
                                <span
                                    className="font-display text-2xl font-semibold"
                                    style={{ color: 'var(--resolved)' }}
                                >
                                    {RESOLUTION_RATE}%
                                </span>
                                <span
                                    className="text-[11px]"
                                    style={{ color: 'var(--text-secondary)' }}
                                >
                                    {t.chart.resolvedLabel}
                                </span>
                            </div>
                        </div>
                    </ChartCard>
                </div>

                <p
                    className="mt-6 text-center text-xs"
                    style={{ color: 'var(--text-secondary)' }}
                >
                    {t.chart.note}
                </p>
            </div>
        </section>
    );
}

function ProblemSolution() {
    const { t } = useI18n();

    return (
        <section className="mx-auto max-w-6xl px-6 py-20">
            <h2 className="font-display mb-10 text-center text-3xl font-semibold">
                {t.problem.heading}
            </h2>
            <div
                className="grid gap-px overflow-hidden rounded-md border md:grid-cols-2"
                style={{ borderColor: 'var(--border)' }}
            >
                <div className="p-6" style={{ background: 'var(--bg-raised)' }}>
                    <h3
                        className="mb-6 text-sm font-semibold tracking-wide uppercase"
                        style={{ color: 'var(--text-secondary)' }}
                    >
                        {t.problem.withoutTitle}
                    </h3>
                    <div className="space-y-6">
                        {t.problem.rows.map((r) => (
                            <p
                                key={r.old}
                                className="text-sm leading-relaxed"
                                style={{ color: 'var(--text-secondary)' }}
                            >
                                {r.old}
                            </p>
                        ))}
                    </div>
                </div>
                <div className="p-6" style={{ background: 'var(--bg-page)' }}>
                    <h3
                        className="mb-6 text-sm font-semibold tracking-wide uppercase"
                        style={{ color: 'var(--accent-dark)' }}
                    >
                        {t.problem.withTitle}
                    </h3>
                    <div className="space-y-6">
                        {t.problem.rows.map((r) => (
                            <p
                                key={r.grms}
                                className="flex gap-2 text-sm leading-relaxed"
                            >
                                <CheckCircle2
                                    className="mt-0.5 h-4 w-4 shrink-0"
                                    style={{ color: 'var(--resolved)' }}
                                />
                                <span>{r.grms}</span>
                            </p>
                        ))}
                    </div>
                </div>
            </div>
        </section>
    );
}

const FEATURE_ICONS: IconType[] = [
    Send,
    Route,
    Clock,
    Lock,
    FileText,
    BarChart3,
];

function Features() {
    const { t } = useI18n();

    return (
        <section id="features" className="mx-auto max-w-6xl px-6 py-20">
            <h2 className="font-display mb-2 text-center text-3xl font-semibold">
                {t.features.heading}
            </h2>
            <p
                className="mb-12 text-center"
                style={{ color: 'var(--text-secondary)' }}
            >
                {t.features.sub}
            </p>
            <div className="grid gap-5 md:grid-cols-3">
                {t.features.items.map((f, i) => {
                    const Icon = FEATURE_ICONS[i];

                    return (
                        <Card
                            key={f.title}
                            className="border"
                            style={{
                                borderColor: 'var(--border)',
                                background: 'var(--bg-raised)',
                            }}
                        >
                            <CardContent className="p-6">
                                <div
                                    className="mb-4 flex h-9 w-9 items-center justify-center rounded-sm"
                                    style={{
                                        background: 'var(--text-primary)',
                                    }}
                                >
                                    <Icon
                                        className="h-4 w-4"
                                        style={{ color: 'var(--bg-page)' }}
                                    />
                                </div>
                                <h3 className="mb-2 font-semibold">
                                    {f.title}
                                </h3>
                                <p
                                    className="text-sm leading-relaxed"
                                    style={{ color: 'var(--text-secondary)' }}
                                >
                                    {f.desc}
                                </p>
                            </CardContent>
                        </Card>
                    );
                })}
            </div>
        </section>
    );
}

const HOW_ICONS: IconType[] = [Send, Bell, Route, Search, CheckCircle2];

function HowItWorks() {
    const { t } = useI18n();

    return (
        <section
            id="how-it-works"
            className="py-20"
            style={{ background: 'var(--bg-raised)' }}
        >
            <div className="mx-auto max-w-6xl px-6">
                <h2 className="font-display mb-8 text-center text-3xl font-semibold">
                    {t.how.heading}
                </h2>
                <RoadDivider />
                <div className="mt-4 grid gap-6 md:grid-cols-5">
                    {t.how.steps.map((s, i) => {
                        const Icon = HOW_ICONS[i];
                        const isLast = i === t.how.steps.length - 1;

                        return (
                            <div key={s.title}>
                                <div
                                    className="mb-3 flex h-10 w-10 items-center justify-center rounded-full"
                                    style={{
                                        background: isLast
                                            ? 'var(--resolved)'
                                            : 'var(--accent)',
                                    }}
                                >
                                    <Icon
                                        className="h-4.5 w-4.5"
                                        style={{ color: '#fff' }}
                                    />
                                </div>
                                <span
                                    className="font-mono text-xs"
                                    style={{ color: 'var(--accent)' }}
                                >
                                    {String(i + 1).padStart(2, '0')}
                                </span>
                                <h3 className="mt-1 mb-2 font-semibold">
                                    {s.title}
                                </h3>
                                <p
                                    className="text-sm leading-relaxed"
                                    style={{ color: 'var(--text-secondary)' }}
                                >
                                    {s.desc}
                                </p>
                            </div>
                        );
                    })}
                </div>
            </div>
        </section>
    );
}

const AUDIENCE_ICONS: IconType[] = [Users, Home, HardHat, ClipboardCheck];

function Audiences() {
    const { t } = useI18n();
    const [active, setActive] = useState('0');

    return (
        <section id="audiences" className="mx-auto max-w-6xl px-6 py-20">
            <h2 className="font-display mb-10 text-center text-3xl font-semibold">
                {t.audiences.heading}
            </h2>
            <Tabs value={active} onValueChange={setActive} className="w-full">
                <TabsList
                    className="mb-8 grid grid-cols-2 md:grid-cols-4"
                    style={{ background: 'var(--bg-raised)' }}
                >
                    {t.audiences.tabs.map((tab, i) => (
                        <TabsTrigger
                            key={tab.label}
                            value={String(i)}
                            className="text-sm"
                        >
                            {tab.label}
                        </TabsTrigger>
                    ))}
                </TabsList>
                {t.audiences.tabs.map((tab, i) => {
                    const Icon = AUDIENCE_ICONS[i];

                    return (
                        <TabsContent key={tab.label} value={String(i)}>
                            <div
                                className="rounded-md border p-8"
                                style={{
                                    borderColor: 'var(--border)',
                                    background: 'var(--bg-raised)',
                                }}
                            >
                                <Icon
                                    className="mb-4 h-6 w-6"
                                    style={{ color: 'var(--accent)' }}
                                />
                                <ul className="space-y-3">
                                    {tab.points.map((p) => (
                                        <li
                                            key={p}
                                            className="flex gap-2 text-sm"
                                            style={{
                                                color: 'var(--text-secondary)',
                                            }}
                                        >
                                            <CheckCircle2
                                                className="mt-0.5 h-4 w-4 shrink-0"
                                                style={{
                                                    color: 'var(--resolved)',
                                                }}
                                            />
                                            <span>{p}</span>
                                        </li>
                                    ))}
                                </ul>
                            </div>
                        </TabsContent>
                    );
                })}
            </Tabs>
        </section>
    );
}

const SECURITY_ICONS: IconType[] = [ShieldCheck, Lock, FileText];

function Security() {
    const { t } = useI18n();

    return (
        <section
            id="security"
            className="py-20"
            style={{ background: 'var(--bg-inverse)' }}
        >
            <div className="mx-auto max-w-6xl px-6">
                <h2
                    className="font-display mb-10 text-center text-3xl font-semibold"
                    style={{ color: 'var(--text-on-inverse)' }}
                >
                    {t.security.heading}
                </h2>
                <div className="grid gap-6 md:grid-cols-3">
                    {t.security.items.map((it, i) => {
                        const Icon = SECURITY_ICONS[i];

                        return (
                            <div
                                key={it.title}
                                className="rounded-md p-6"
                                style={{ background: 'rgba(255,255,255,0.05)' }}
                            >
                                <Icon
                                    className="mb-3 h-5 w-5"
                                    style={{ color: 'var(--accent)' }}
                                />
                                <h3
                                    className="mb-2 font-semibold"
                                    style={{ color: 'var(--text-on-inverse)' }}
                                >
                                    {it.title}
                                </h3>
                                <p
                                    className="text-sm leading-relaxed"
                                    style={{
                                        color: 'var(--text-on-inverse-secondary)',
                                    }}
                                >
                                    {it.desc}
                                </p>
                            </div>
                        );
                    })}
                </div>
            </div>
        </section>
    );
}

function CTA() {
    const { t } = useI18n();

    return (
        <section className="relative overflow-hidden">
            <RoadWatermark />
            <div className="relative mx-auto max-w-6xl px-6 py-20 text-center">
                <MessageSquareText
                    className="mx-auto mb-6 h-8 w-8"
                    style={{ color: 'var(--accent)' }}
                />
                <h2 className="font-display mb-4 text-3xl font-semibold md:text-4xl">
                    {t.cta.heading}
                </h2>
                <p
                    className="mx-auto mb-8 max-w-md"
                    style={{ color: 'var(--text-secondary)' }}
                >
                    {t.cta.sub}
                </p>
                <Link href={FILE_GRIEVANCE_URL}>
                    <Button
                        size="lg"
                        style={{
                            background: 'var(--accent)',
                            color: '#14213D',
                        }}
                    >
                        {t.cta.button} <ArrowRight className="ml-1 h-4 w-4" />
                    </Button>
                </Link>
            </div>
        </section>
    );
}

function LandingContent() {
    return (
        <>
            <NavBar />
            <Hero />
            <ResolutionChart />
            <ProblemSolution />
            <Features />
            <HowItWorks />
            <Audiences />
            <Security />
            <CTA />
            <Footer />
        </>
    );
}

export default function LandingPage() {
    return (
        <PageShell>
            <LandingContent />
        </PageShell>
    );
}
