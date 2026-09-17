import { StatusCell } from '@/components/data-table/status-cell';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { useTranslation } from '@/hooks/use-translation';
import type { ProjectType } from './Columns';

interface ProjectTypeViewContentProps {
    projectType: ProjectType;
}

export function ProjectTypeViewContent({ projectType }: ProjectTypeViewContentProps) {
    const { t } = useTranslation();

    return (
        <Card>
            <CardHeader>
                <CardTitle>{t('Project Type Details')}</CardTitle>
                <CardDescription>{t('Read-only view of this project type.')}</CardDescription>
            </CardHeader>
            <CardContent className="grid gap-4 sm:grid-cols-2">
                <div>
                    <p className="text-xs font-medium text-muted-foreground">{t('Code')}</p>
                    <p className="font-mono text-sm">{projectType.code}</p>
                </div>
                <div>
                    <p className="text-xs font-medium text-muted-foreground">{t('Name')}</p>
                    <p className="text-sm">{projectType.name}</p>
                </div>
                <div>
                    <p className="text-xs font-medium text-muted-foreground">{t('Name St')}</p>
                    <p className="text-sm">{projectType.name_st ?? '—'}</p>
                </div>
                <div>
                    <p className="text-xs font-medium text-muted-foreground">{t('Sort Order')}</p>
                    <p className="text-sm">{projectType.sort_order}</p>
                </div>
                <div>
                    <p className="text-xs font-medium text-muted-foreground">{t('Status')}</p>
                    <StatusCell
                        value={projectType.is_active}
                        activeLabel={t('Active')}
                        inactiveLabel={t('Inactive')}
                    />
                </div>
                <div>
                    <p className="text-xs font-medium text-muted-foreground">{t('Created At')}</p>
                    <p className="text-sm">{projectType.created_at}</p>
                </div>
            </CardContent>
        </Card>
    );
}