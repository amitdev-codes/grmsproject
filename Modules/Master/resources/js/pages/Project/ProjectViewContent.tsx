import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { useTranslation } from '@/hooks/use-translation';
import type { Project } from './Columns';

interface ProjectViewContentProps {
    project: Project;
}

export function ProjectViewContent({ project }: ProjectViewContentProps) {
    const { t } = useTranslation();

    return (
        <Card>
            <CardHeader>
                <CardTitle>{t('Project Details')}</CardTitle>
                <CardDescription>{t('Read-only view of this project.')}</CardDescription>
            </CardHeader>
            <CardContent className="grid gap-4 sm:grid-cols-2">
                <div>
                    <p className="text-xs font-medium text-muted-foreground">{t('Code')}</p>
                    <p className="font-mono text-sm">{project.code}</p>
                </div>
                <div>
                    <p className="text-xs font-medium text-muted-foreground">{t('Title')}</p>
                    <p className="text-sm">{project.title}</p>
                </div>
                <div>
                    <p className="text-xs font-medium text-muted-foreground">{t('Type')}</p>
                    <p className="text-sm">{project.project_type?.name ?? '—'}</p>
                </div>
                <div>
                    <p className="text-xs font-medium text-muted-foreground">{t('District')}</p>
                    <p className="text-sm">{project.district?.name ?? '—'}</p>
                </div>
                <div>
                    <p className="text-xs font-medium text-muted-foreground">{t('Contractor')}</p>
                    <p className="text-sm">{project.contractor?.name ?? '—'}</p>
                </div>
                <div>
                    <p className="text-xs font-medium text-muted-foreground">{t('Contract Amount')}</p>
                    <p className="text-sm">
                        {project.contract_amount
                            ? `${project.currency} ${Number(project.contract_amount).toLocaleString()}`
                            : '—'}
                    </p>
                </div>
                <div>
                    <p className="text-xs font-medium text-muted-foreground">{t('Status')}</p>
                    <p className="text-sm">{project.status}</p>
                </div>
                <div>
                    <p className="text-xs font-medium text-muted-foreground">{t('Created At')}</p>
                    <p className="text-sm">{project.created_at}</p>
                </div>
            </CardContent>
        </Card>
    );
}