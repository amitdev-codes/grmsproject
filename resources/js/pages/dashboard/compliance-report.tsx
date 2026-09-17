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
                <CardTitle>{t('menu.monthly_compliance') || 'Monthly Compliance Reports'}</CardTitle>
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
                                <CartesianGrid strokeDasharray="3 3" stroke="hsl(var(--border))" />
                                <XAxis dataKey="month" stroke="#888888" fontSize={11} tickLine={false} />
                                <YAxis stroke="#888888" fontSize={11} tickLine={false} />
                                <Tooltip
                                    contentStyle={{
                                        backgroundColor: 'hsl(var(--card))',
                                        border: '1px solid hsl(var(--border))',
                                        borderRadius: '6px',
                                    }}
                                />
                                <Bar dataKey="resolved" fill="hsl(var(--success))" radius={[4, 4, 0, 0]} />
                                <Bar dataKey="total" fill="hsl(var(--primary))" radius={[4, 4, 0, 0]} />
                            </BarChart>
                        </ResponsiveContainer>
                    </div>
                    <div>
                        <h4 className="mb-2 text-sm font-medium">
                            {t('menu.compliance_rate') || 'Compliance Rate (%)'}
                        </h4>
                        <ResponsiveContainer width="100%" height={250}>
                            <LineChart data={chartData}>
                                <CartesianGrid strokeDasharray="3 3" stroke="hsl(var(--border))" />
                                <XAxis dataKey="month" stroke="#888888" fontSize={11} tickLine={false} />
                                <YAxis stroke="#888888" fontSize={11} tickLine={false} domain={[0, 100]} />
                                <Tooltip
                                    contentStyle={{
                                        backgroundColor: 'hsl(var(--card))',
                                        border: '1px solid hsl(var(--border))',
                                        borderRadius: '6px',
                                    }}
                                />
                                <Line
                                    type="monotone"
                                    dataKey="rate"
                                    stroke="hsl(var(--primary))"
                                    strokeWidth={2}
                                    dot={{ r: 4 }}
                                />
                            </LineChart>
                        </ResponsiveContainer>
                    </div>
                </div>
            </CardContent>
        </Card>
    );
}