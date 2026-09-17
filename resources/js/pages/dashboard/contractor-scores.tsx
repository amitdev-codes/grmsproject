import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Progress } from '@/components/ui/progress';
import { useTranslation } from '@/hooks/use-translation';

interface ContractorScore {
    name: string;
    total: number;
    resolved: number;
    score: number;
}

export function ContractorScores({ data }: { data: ContractorScore[] }) {
    const { t } = useTranslation();
    const sorted = [...(data ?? [])].sort((a, b) => b.score - a.score);

    return (
        <Card className="col-span-1">
            <CardHeader>
                <CardTitle>{t('menu.contractor_scores') || 'Contractor Performance'}</CardTitle>
                <CardDescription>
                    {t('menu.contractor_desc') || 'Resolution score per contractor'}
                </CardDescription>
            </CardHeader>
            <CardContent className="space-y-4">
                {sorted.length === 0 ? (
                    <p className="text-sm text-muted-foreground">
                        {t('menu.no_data') || 'No data available.'}
                    </p>
                ) : (
                    sorted.map((c) => (
                        <div key={c.name} className="space-y-1.5">
                            <div className="flex items-center justify-between text-sm">
                                <span className="font-medium">{c.name}</span>
                                <span className="font-bold">{c.score}%</span>
                            </div>
                            <Progress value={c.score} className="h-2" />
                            <p className="text-xs text-muted-foreground">
                                {c.resolved} / {c.total} resolved
                            </p>
                        </div>
                    ))
                )}
            </CardContent>
        </Card>
    );
}