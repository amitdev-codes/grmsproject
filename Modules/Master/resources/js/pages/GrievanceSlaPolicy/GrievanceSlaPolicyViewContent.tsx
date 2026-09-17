import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { StatusCell } from '@/components/data-table/status-cell';
import { useTranslation } from '@/hooks/use-translation';
import type { GrievanceSlaPolicy } from './Columns';

interface GrievanceSlaPolicyViewContentProps {
    policy: GrievanceSlaPolicy;
}

export function GrievanceSlaPolicyViewContent({ policy }: GrievanceSlaPolicyViewContentProps) {
    const { t } = useTranslation();

    return (
        <Card>
            <CardHeader>
                <CardTitle>{t('SLA Policy Details')}</CardTitle>
                <CardDescription>{t('Read-only view of this SLA policy.')}</CardDescription>
            </CardHeader>
            <CardContent className="grid gap-4 sm:grid-cols-2">
                <div>
                    <p className="text-xs font-medium text-muted-foreground">{t('Code')}</p>
                    <p className="font-mono text-sm">{policy.code}</p>
                </div>
                <div>
                    <p className="text-xs font-medium text-muted-foreground">{t('Name')}</p>
                    <p className="text-sm">{policy.name}</p>
                </div>
                <div>
                    <p className="text-xs font-medium text-muted-foreground">{t('Priority')}</p>
                    <p className="text-sm">{policy.priority ?? '—'}</p>
                </div>
                <div>
                    <p className="text-xs font-medium text-muted-foreground">{t('Acknowledgement (hours)')}</p>
                    <p className="text-sm">{policy.acknowledgement_hours}</p>
                </div>
                <div>
                    <p className="text-xs font-medium text-muted-foreground">{t('Resolution (hours)')}</p>
                    <p className="text-sm">{policy.resolution_hours}</p>
                </div>
                <div>
                    <p className="text-xs font-medium text-muted-foreground">{t('Use Business Hours')}</p>
                    <StatusCell
                        value={policy.use_business_hours}
                        activeLabel={t('Yes')}
                        inactiveLabel={t('No')}
                    />
                </div>
                <div>
                    <p className="text-xs font-medium text-muted-foreground">{t('Status')}</p>
                    <StatusCell
                        value={policy.is_active}
                        activeLabel={t('Active')}
                        inactiveLabel={t('Inactive')}
                    />
                </div>
                <div>
                    <p className="text-xs font-medium text-muted-foreground">{t('Created At')}</p>
                    <p className="text-sm">{policy.created_at}</p>
                </div>
            </CardContent>
        </Card>
    );
}