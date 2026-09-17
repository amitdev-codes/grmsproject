import { House, BarChart3, FileText, TrendingUp, AlertTriangle } from 'lucide-react';
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
    contractor_scores: { name: string; total: number; resolved: number; score: number }[];
    compliance: { month: string; resolved: number; total: number; rate: number }[];
}

export default function Dashboard() {
    const { t } = useTranslation();
    const { summary, monthly_trends, by_issue_type, by_region, by_channel, heatmap, contractor_scores, compliance } =
        usePage<SharedData & { dashboard: DashboardData }>().props.dashboard ?? ({} as DashboardData);

    const StatCard = ({
        title,
        value,
        icon: Icon,
        description,
    }: {
        title: string;
        value: number | string;
        icon: React.ElementType;
        description?: string;
    }) => (
        <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                <CardTitle className="text-sm font-medium">{title}</CardTitle>
                <Icon className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
                <div className="text-2xl font-bold">{value}</div>
                {description && (
                    <p className="text-xs text-muted-foreground">{description}</p>
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
                    />
                    <StatCard
                        title={t('menu.resolved') || 'Resolved'}
                        value={summary?.resolved ?? 0}
                        icon={TrendingUp}
                    />
                    <StatCard
                        title={t('menu.pending') || 'Pending'}
                        value={summary?.pending ?? 0}
                        icon={AlertTriangle}
                    />
                    <StatCard
                        title={t('menu.resolution_rate') || 'Resolution Rate'}
                        value={`${summary?.resolution_rate ?? 0}%`}
                        icon={FileText}
                    />
                </div>

                <Tabs defaultValue="overview" className="space-y-4">
                    <TabsList>
                        <TabsTrigger value="overview">{t('menu.overview') || 'Overview'}</TabsTrigger>
                        <TabsTrigger value="analytics">{t('menu.analytics') || 'Analytics'}</TabsTrigger>
                        <TabsTrigger value="reports">{t('menu.reports') || 'Reports'}</TabsTrigger>
                    </TabsList>

                    <TabsContent value="overview" className="space-y-4">
                        <div className="grid grid-cols-1 gap-4 lg:grid-cols-7">
                            <Card className="col-span-1 lg:col-span-4">
                                <CardHeader>
                                    <CardTitle>{t('menu.monthly_trends') || 'Monthly Trends'}</CardTitle>
                                </CardHeader>
                                <CardContent className="pl-2">
                                    <Overview data={monthly_trends} />
                                </CardContent>
                            </Card>
                            <Card className="col-span-1 lg:col-span-3">
                                <CardHeader>
                                    <CardTitle>{t('menu.recent_grievances') || 'Recent Grievances'}</CardTitle>
                                    <CardDescription>
                                        {t('menu.five_this_month') || '5 grievances this month.'}
                                    </CardDescription>
                                </CardHeader>
                                <CardContent>
                                    <RecentSales data={by_channel} />
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
                                    <CardTitle>{t('menu.by_issue_type') || 'By Issue Type'}</CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <Overview data={by_issue_type} />
                                </CardContent>
                            </Card>
                            <Card>
                                <CardHeader>
                                    <CardTitle>{t('menu.by_region') || 'By Region'}</CardTitle>
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