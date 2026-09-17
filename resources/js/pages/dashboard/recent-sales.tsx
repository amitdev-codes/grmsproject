import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { BarChart, Bar, XAxis, YAxis, ResponsiveContainer, Tooltip } from 'recharts';
import { useTranslation } from '@/hooks/use-translation';

interface ChannelData {
    name: string;
    count: number;
}

export function RecentSales({ data }: { data: ChannelData[] }) {
    const { t } = useTranslation();
    const chartData = (data ?? []).map((c) => ({
        name: c.name,
        count: c.count,
    }));

    return (
        <Card>
            <CardHeader>
                <CardTitle>{t('menu.intake_channels') || 'Intake Channels'}</CardTitle>
                <CardDescription>
                    {t('menu.intake_channels_desc') || 'Complaints by channel'}
                </CardDescription>
            </CardHeader>
            <CardContent>
                <ResponsiveContainer width="100%" height={250}>
                    <BarChart data={chartData} layout="vertical">
                        <XAxis type="number" stroke="#888888" fontSize={11} />
                        <YAxis
                            type="category"
                            dataKey="name"
                            stroke="#888888"
                            fontSize={11}
                            tickLine={false}
                            axisLine={false}
                        />
                        <Tooltip
                            contentStyle={{
                                backgroundColor: 'hsl(var(--card))',
                                border: '1px solid hsl(var(--border))',
                                borderRadius: '6px',
                            }}
                        />
                        <Bar dataKey="count" fill="hsl(var(--primary))" radius={[0, 4, 4, 0]} />
                    </BarChart>
                </ResponsiveContainer>
            </CardContent>
        </Card>
    );
}