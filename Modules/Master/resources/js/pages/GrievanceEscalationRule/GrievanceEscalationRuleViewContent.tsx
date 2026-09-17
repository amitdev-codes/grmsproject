import { StatusCell } from '@/components/data-table/status-cell';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { useTranslation } from '@/hooks/use-translation';
import type { GrievanceEscalationRule } from './Columns';

interface GrievanceEscalationRuleViewContentProps {
    rule: GrievanceEscalationRule;
}

export function GrievanceEscalationRuleViewContent({ rule }: GrievanceEscalationRuleViewContentProps) {
    const { t } = useTranslation();

    return (
        <Card>
            <CardHeader>
                <CardTitle>{t('Escalation Rule Details')}</CardTitle>
                <CardDescription>{t('Read-only view of this escalation rule.')}</CardDescription>
            </CardHeader>
            <CardContent className="grid gap-4 sm:grid-cols-2">
                <div>
                    <p className="text-xs font-medium text-muted-foreground">{t('Escalation Level')}</p>
                    <p className="text-sm">{rule.escalation_level}</p>
                </div>
                <div>
                    <p className="text-xs font-medium text-muted-foreground">{t('SLA Policy')}</p>
                    <p className="text-sm">{rule.sla_policy?.name ?? '—'}</p>
                </div>
                <div>
                    <p className="text-xs font-medium text-muted-foreground">{t('Breach After (hours)')}</p>
                    <p className="text-sm">{rule.breach_after_hours}</p>
                </div>
                <div>
                    <p className="text-xs font-medium text-muted-foreground">{t('Extension (hours)')}</p>
                    <p className="text-sm">{rule.extension_hours ?? '—'}</p>
                </div>
                <div>
                    <p className="text-xs font-medium text-muted-foreground">{t('Target Role')}</p>
                    <p className="text-sm">{rule.target_role}</p>
                </div>
                <div>
                    <p className="text-xs font-medium text-muted-foreground">{t('Requires Manual Review')}</p>
                    <StatusCell
                        value={rule.requires_manual_review}
                        activeLabel={t('Yes')}
                        inactiveLabel={t('No')}
                    />
                </div>
                <div>
                    <p className="text-xs font-medium text-muted-foreground">{t('Status')}</p>
                    <StatusCell
                        value={rule.is_active}
                        activeLabel={t('Active')}
                        inactiveLabel={t('Inactive')}
                    />
                </div>
                <div>
                    <p className="text-xs font-medium text-muted-foreground">{t('Created At')}</p>
                    <p className="text-sm">{rule.created_at}</p>
                </div>
            </CardContent>
        </Card>
    );
}