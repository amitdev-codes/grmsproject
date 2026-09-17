import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { useTranslation } from '@/hooks/use-translation';

interface HeatmapCell {
    district: string;
    category: string;
    count: number;
}

function intensityColor(value: number, max: number): string {
    if (value === 0 || max === 0) return 'bg-muted/30';
    const ratio = value / max;
    if (ratio > 0.75) return 'bg-primary/80 text-primary-foreground';
    if (ratio > 0.5) return 'bg-primary/55 text-primary-foreground';
    if (ratio > 0.25) return 'bg-primary/30';
    return 'bg-primary/15';
}

export function Heatmap({ data }: { data: HeatmapCell[] }) {
    const { t } = useTranslation();
    const districts = Array.from(new Set((data ?? []).map((d) => d.district)));
    const categories = Array.from(new Set((data ?? []).map((d) => d.category)));
    const max = Math.max(...(data ?? []).map((d) => d.count), 1);

    const getCell = (district: string, category: string) =>
        (data ?? []).find((d) => d.district === district && d.category === category)?.count ?? 0;

    return (
        <Card className="col-span-1">
            <CardHeader>
                <CardTitle>{t('menu.heatmap') || 'Complaint Heatmap'}</CardTitle>
                <CardDescription>
                    {t('menu.heatmap_desc') || 'District × Category complaint density'}
                </CardDescription>
            </CardHeader>
            <CardContent>
                <div className="overflow-x-auto">
                    <table className="w-full text-xs">
                        <thead>
                            <tr>
                                <th className="text-left p-2 font-medium">District / Category</th>
                                {categories.map((c) => (
                                    <th key={c} className="p-2 font-medium">
                                        {c}
                                    </th>
                                ))}
                            </tr>
                        </thead>
                        <tbody>
                            {districts.map((district) => (
                                <tr key={district}>
                                    <td className="p-2 font-medium">{district}</td>
                                    {categories.map((category) => {
                                        const value = getCell(district, category);
                                        return (
                                            <td key={category} className="p-1">
                                                <div
                                                    className={`flex h-8 items-center justify-center rounded ${intensityColor(
                                                        value,
                                                        max,
                                                    )}`}
                                                >
                                                    {value || ''}
                                                </div>
                                            </td>
                                        );
                                    })}
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            </CardContent>
        </Card>
    );
}