import {
    Bar,
    BarChart,
    Cell,
    ResponsiveContainer,
    XAxis,
    YAxis,
    Tooltip,
} from 'recharts';

interface ChannelData {
    name: string;
    count: number;
}

export function RecentSales({ data }: { data: ChannelData[] }) {
    const chartData = (data ?? []).map((c) => ({
        name: c.name,
        count: c.count,
    }));

    return (
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
                        backgroundColor: '#ffffff',
                        border: '1px solid #e2e8f0',
                        borderRadius: '8px',
                        color: '#0f172a',
                    }}
                />
                <Bar dataKey="count" radius={[0, 4, 4, 0]}>
                    {chartData.map((entry, index) => (
                        <Cell
                            key={`channel-${entry.name}-${index}`}
                            fill={
                                ['#2563eb', '#16a34a', '#f97316', '#8b5cf6'][
                                    index % 4
                                ]
                            }
                        />
                    ))}
                </Bar>
            </BarChart>
        </ResponsiveContainer>
    );
}
