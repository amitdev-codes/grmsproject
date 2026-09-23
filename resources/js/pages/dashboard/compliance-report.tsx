import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import {
    BarChart,
    Bar,
    XAxis,
    YAxis,
    ResponsiveContainer,
    Tooltip,
    LineChart,
    Line,
    CartesianGrid,
    Legend,
} from 'recharts';
import { useTranslation } from '@/hooks/use-translation';

interface ComplianceRow {
    month: string;
    resolved: number;
    total: number;
    rate: number;
}

export function ComplianceReport({ data }: { data: ComplianceRow[] }) {
    const { t } = useTranslation();
    const chartData = (data ?? []).map((d) => ({
        month: d.month,
        resolved: d.resolved,
        total: d.total,
        rate: d.rate,
    }));

    return (
        <Card className="col-span-1">
            <CardHeader>
                <CardTitle>
                    {t('menu.monthly_compliance') ||
                        'Monthly Compliance Reports'}
                </CardTitle>
                <CardDescription>
                    {t('menu.compliance_desc') || 'Resolution rate per month'}
                </CardDescription>
            </CardHeader>
            <CardContent>
                <div className="space-y-6">
                    <div>
                        <h4 className="mb-2 text-sm font-medium">
                            {t('menu.resolved_vs_total') || 'Resolved vs Total'}
                        </h4>
                        <ResponsiveContainer width="100%" height={250}>
                            <BarChart data={chartData}>
                                <CartesianGrid
                                    strokeDasharray="3 3"
                                    stroke="#e2e8f0"
                                />
                                <XAxis
                                    dataKey="month"
                                    stroke="#64748b"
                                    fontSize={11}
                                    tickLine={false}
                                />
                                <YAxis
                                    stroke="#64748b"
                                    fontSize={11}
                                    tickLine={false}
                                />
                                <Tooltip
                                    contentStyle={{
                                        backgroundColor: '#ffffff',
                                        border: '1px solid #e2e8f0',
                                        borderRadius: '8px',
                                        color: '#0f172a',
                                    }}
                                />
                                <Legend
                                    verticalAlign="top"
                                    height={30}
                                    iconType="circle"
                                    wrapperStyle={{
                                        fontSize: 12,
                                        color: '#475569',
                                    }}
                                />
                                <Bar
                                    dataKey="resolved"
                                    name="Resolved"
                                    fill="#22c55e"
                                    radius={[4, 4, 0, 0]}
                                />
                                <Bar
                                    dataKey="total"
                                    name="Total"
                                    fill="#38bdf8"
                                    radius={[4, 4, 0, 0]}
                                />
                            </BarChart>
                        </ResponsiveContainer>
                    </div>
                    <div>
                        <h4 className="mb-2 text-sm font-medium">
                            {t('menu.compliance_rate') || 'Compliance Rate (%)'}
                        </h4>
                        <ResponsiveContainer width="100%" height={250}>
                            <LineChart data={chartData}>
                                <CartesianGrid
                                    strokeDasharray="3 3"
                                    stroke="#e2e8f0"
                                />
                                <XAxis
                                    dataKey="month"
                                    stroke="#64748b"
                                    fontSize={11}
                                    tickLine={false}
                                />
                                <YAxis
                                    stroke="#64748b"
                                    fontSize={11}
                                    tickLine={false}
                                    domain={[0, 100]}
                                />
                                <Tooltip
                                    contentStyle={{
                                        backgroundColor: '#ffffff',
                                        border: '1px solid #e2e8f0',
                                        borderRadius: '8px',
                                        color: '#0f172a',
                                    }}
                                />
                                <Line
                                    type="monotone"
                                    dataKey="rate"
                                    name="Compliance rate"
                                    stroke="#f97316"
                                    strokeWidth={3}
                                    dot={{
                                        r: 4,
                                        fill: '#f97316',
                                        stroke: '#ffffff',
                                        strokeWidth: 2,
                                    }}
                                />
                            </LineChart>
                        </ResponsiveContainer>
                    </div>
                </div>
            </CardContent>
        </Card>
    );
}
