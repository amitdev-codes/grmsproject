import {
    Bar,
    BarChart,
    Cell,
    ResponsiveContainer,
    XAxis,
    YAxis,
    Tooltip,
} from 'recharts';

interface TrendData {
    month?: string;
    name?: string;
    count?: number;
    total?: number;
}

export function Overview({ data }: { data: TrendData[] }) {
    const chartData = (data ?? []).map((d) => ({
        name: d.month ?? d.name ?? '',
        total: d.count ?? d.total ?? 0,
    }));

    return (
        <ResponsiveContainer width="100%" height={350}>
            <BarChart data={chartData}>
                <XAxis
                    dataKey="name"
                    stroke="#888888"
                    fontSize={12}
                    tickLine={false}
                    axisLine={false}
                />
                <YAxis
                    stroke="#888888"
                    fontSize={12}
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
                <Bar dataKey="total" radius={[4, 4, 0, 0]}>
                    {chartData.map((entry, index) => (
                        <Cell
                            key={`bar-${entry.name}-${index}`}
                            fill={
                                [
                                    '#38bdf8',
                                    '#60a5fa',
                                    '#818cf8',
                                    '#a78bfa',
                                    '#34d399',
                                ][index % 5]
                            }
                        />
                    ))}
                </Bar>
            </BarChart>
        </ResponsiveContainer>
    );
}
