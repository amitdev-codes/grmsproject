import {
    BarChart3,
    CheckCircle2,
    Clock3,
    House,
    PieChart as PieChartIcon,
    TrendingUp,
} from 'lucide-react';
import IndexLayout from '@/components/index-layout';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Overview } from './overview';
import { RecentSales } from './recent-sales';
import { useTranslation } from '@/hooks/use-translation';
import type { SharedData } from '@/types/shared-data';
import { usePage } from '@inertiajs/react';
import { Heatmap } from './heatmap';
import { ContractorScores } from './contractor-scores';
import { ComplianceReport } from './compliance-report';
import {
    Cell,
    Legend,
    Pie,
    PieChart,
    ResponsiveContainer,
    Tooltip,
} from 'recharts';

interface DashboardData {
    summary: {
        total: number;
        resolved: number;
        pending: number;
        closed: number;
        rejected: number;
        escalated: number;
        in_progress: number;
        resolution_rate: number;
    };
    monthly_trends: { month: string; count: number }[];
    by_issue_type: { name: string; count: number }[];
    by_region: { name: string; count: number }[];
    by_channel: { name: string; count: number }[];
    heatmap: { district: string; category: string; count: number }[];
    contractor_scores: {
        name: string;
        total: number;
        resolved: number;
        score: number;
    }[];
    compliance: {
        month: string;
        resolved: number;
        total: number;
        rate: number;
    }[];
}

export default function Dashboard() {
    const { t } = useTranslation();
    const {
        summary,
        monthly_trends,
        by_issue_type,
        by_region,
        by_channel,
        heatmap,
        contractor_scores,
        compliance,
    } =
        usePage<SharedData & { dashboard: DashboardData }>().props.dashboard ??
        ({} as DashboardData);

    const StatCard = ({
        title,
        value,
        icon: Icon,
        description,
        color,
        background,
    }: {
        title: string;
        value: number | string;
        icon: React.ElementType;
        description?: string;
        color: string;
        background: string;
    }) => (
        <Card className="border-border/70 shadow-sm">
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                <CardTitle className="text-xs font-semibold tracking-wide text-muted-foreground uppercase">
                    {title}
                </CardTitle>
                <span
                    className="flex h-8 w-8 items-center justify-center rounded-lg"
                    style={{ backgroundColor: background, color }}
                >
                    <Icon className="h-4 w-4" />
                </span>
            </CardHeader>
            <CardContent className="pt-0">
                <div className="text-2xl font-bold tracking-tight">{value}</div>
                {description && (
                    <p className="text-xs text-muted-foreground">
                        {description}
                    </p>
                )}
            </CardContent>
        </Card>
    );

    return (
        <IndexLayout
            title="dashboard"
            breadcrumbs={[{ label: t('menu.dashboard'), icon: House }]}
        >
            <div className="space-y-6">
                {/* Summary cards */}
                <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <StatCard
                        title={t('menu.total_grievances') || 'Total Grievances'}
                        value={summary?.total ?? 0}
                        icon={BarChart3}
                        color="#2563eb"
                        background="#dbeafe"
                    />
                    <StatCard
                        title={t('menu.resolved') || 'Resolved'}
                        value={summary?.resolved ?? 0}
                        icon={CheckCircle2}
                        color="#15803d"
                        background="#dcfce7"
                    />
                    <StatCard
                        title={t('menu.pending') || 'Pending'}
                        value={summary?.pending ?? 0}
                        icon={Clock3}
                        color="#c2410c"
                        background="#ffedd5"
                    />
                    <StatCard
                        title={t('menu.in_progress') || 'In progress'}
                        value={summary?.in_progress ?? 0}
                        icon={TrendingUp}
                        color="#7c3aed"
                        background="#ede9fe"
                    />
                </div>

                <Tabs defaultValue="overview" className="space-y-4">
                    <TabsList>
                        <TabsTrigger value="overview">
                            {t('menu.overview') || 'Overview'}
                        </TabsTrigger>
                        <TabsTrigger value="analytics">
                            {t('menu.analytics') || 'Analytics'}
                        </TabsTrigger>
                        <TabsTrigger value="reports">
                            {t('menu.reports') || 'Reports'}
                        </TabsTrigger>
                    </TabsList>

                    <TabsContent value="overview" className="space-y-4">
                        <div className="grid grid-cols-1 gap-4 lg:grid-cols-7">
                            <Card className="col-span-1 lg:col-span-4">
                                <CardHeader>
                                    <CardTitle>
                                        {t('menu.monthly_trends') ||
                                            'Monthly Trends'}
                                    </CardTitle>
                                </CardHeader>
                                <CardContent className="pl-2">
                                    <Overview data={monthly_trends} />
                                </CardContent>
                            </Card>
                            <Card className="col-span-1 lg:col-span-3">
                                <CardHeader>
                                    <CardTitle>
                                        {t('menu.recent_grievances') ||
                                            'Recent Grievances'}
                                    </CardTitle>
                                    <CardDescription>
                                        {t('menu.five_this_month') ||
                                            '5 grievances this month.'}
                                    </CardDescription>
                                </CardHeader>
                                <CardContent>
                                    <RecentSales data={by_channel} />
                                </CardContent>
                            </Card>
                        </div>
                        <div className="grid grid-cols-1 gap-4 lg:grid-cols-5">
                            <Card className="lg:col-span-2">
                                <CardHeader>
                                    <CardTitle className="flex items-center gap-2">
                                        <PieChartIcon className="h-4 w-4 text-blue-600" />
                                        Grievance status
                                    </CardTitle>
                                    <CardDescription>
                                        Current distribution of all grievances
                                    </CardDescription>
                                </CardHeader>
                                <CardContent>
                                    <StatusDonut summary={summary} />
                                </CardContent>
                            </Card>
                            <Card className="lg:col-span-3">
                                <CardHeader>
                                    <CardTitle>Resolution snapshot</CardTitle>
                                    <CardDescription>
                                        Resolved cases compared with the total
                                        workload
                                    </CardDescription>
                                </CardHeader>
                                <CardContent>
                                    <div className="grid gap-4 sm:grid-cols-3">
                                        <SnapshotItem
                                            label="Resolved"
                                            value={summary?.resolved ?? 0}
                                            color="#15803d"
                                            background="#dcfce7"
                                        />
                                        <SnapshotItem
                                            label="In progress"
                                            value={summary?.in_progress ?? 0}
                                            color="#7c3aed"
                                            background="#ede9fe"
                                        />
                                        <SnapshotItem
                                            label="Resolution rate"
                                            value={`${summary?.resolution_rate ?? 0}%`}
                                            color="#2563eb"
                                            background="#dbeafe"
                                        />
                                    </div>
                                </CardContent>
                            </Card>
                        </div>
                    </TabsContent>

                    <TabsContent value="analytics" className="space-y-6">
                        <div className="grid grid-cols-1 gap-6 lg:grid-cols-2">
                            <Heatmap data={heatmap} />
                            <ContractorScores data={contractor_scores} />
                        </div>
                        <div className="grid grid-cols-1 gap-6 lg:grid-cols-2">
                            <Card>
                                <CardHeader>
                                    <CardTitle>
                                        {t('menu.by_issue_type') ||
                                            'By Issue Type'}
                                    </CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <Overview data={by_issue_type} />
                                </CardContent>
                            </Card>
                            <Card>
                                <CardHeader>
                                    <CardTitle>
                                        {t('menu.by_region') || 'By Region'}
                                    </CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <Overview data={by_region} />
                                </CardContent>
                            </Card>
                        </div>
                    </TabsContent>

                    <TabsContent value="reports" className="space-y-6">
                        <ComplianceReport data={compliance} />
                    </TabsContent>
                </Tabs>
            </div>
        </IndexLayout>
    );
}

function SnapshotItem({
    label,
    value,
    color,
    background,
}: {
    label: string;
    value: number | string;
    color: string;
    background: string;
}) {
    return (
        <div
            className="rounded-xl border border-border/70 p-4"
            style={{ background }}
        >
            <div
                className="text-xs font-semibold tracking-wide uppercase"
                style={{ color }}
            >
                {label}
            </div>
            <div className="mt-2 text-2xl font-bold" style={{ color }}>
                {value}
            </div>
        </div>
    );
}

function StatusDonut({ summary }: { summary: DashboardData['summary'] }) {
    const data = [
        { name: 'Pending', value: summary?.pending ?? 0, color: '#f97316' },
        { name: 'Resolved', value: summary?.resolved ?? 0, color: '#22c55e' },
        {
            name: 'In progress',
            value: summary?.in_progress ?? 0,
            color: '#8b5cf6',
        },
        { name: 'Closed', value: summary?.closed ?? 0, color: '#2563eb' },
        { name: 'Rejected', value: summary?.rejected ?? 0, color: '#ef4444' },
        { name: 'Escalated', value: summary?.escalated ?? 0, color: '#eab308' },
    ].filter((item) => item.value > 0);

    return (
        <div className="h-[250px] w-full">
            {data.length === 0 ? (
                <div className="flex h-full items-center justify-center text-sm text-muted-foreground">
                    No grievance data available
                </div>
            ) : (
                <ResponsiveContainer width="100%" height="100%">
                    <PieChart>
                        <Pie
                            data={data}
                            dataKey="value"
                            nameKey="name"
                            innerRadius={58}
                            outerRadius={88}
                            paddingAngle={3}
                            stroke="#ffffff"
                            strokeWidth={2}
                        >
                            {data.map((item) => (
                                <Cell key={item.name} fill={item.color} />
                            ))}
                        </Pie>
                        <Tooltip
                            formatter={(value, name) => [value, name]}
                            contentStyle={{
                                borderRadius: 10,
                                border: '1px solid #e2e8f0',
                                backgroundColor: '#ffffff',
                                color: '#0f172a',
                            }}
                        />
                        <Legend
                            verticalAlign="bottom"
                            height={36}
                            iconType="circle"
                            formatter={(value) => (
                                <span className="text-xs text-muted-foreground">
                                    {value}
                                </span>
                            )}
                        />
                    </PieChart>
                </ResponsiveContainer>
            )}
        </div>
    );
}
