import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { StatusCell } from '@/components/data-table/status-cell';
import { useTranslation } from '@/hooks/use-translation';
import type { ServiceProvider } from './Columns';

interface ServiceProviderViewContentProps {
    provider: ServiceProvider;
}

export function ServiceProviderViewContent({ provider }: ServiceProviderViewContentProps) {
    const { t } = useTranslation();

    return (
        <Card>
            <CardHeader>
                <CardTitle>{t('Service Provider Details')}</CardTitle>
                <CardDescription>{t('Read-only view of this service provider.')}</CardDescription>
            </CardHeader>
            <CardContent className="grid gap-4 sm:grid-cols-2">
                <div>
                    <p className="text-xs font-medium text-muted-foreground">{t('Code')}</p>
                    <p className="font-mono text-sm">{provider.code}</p>
                </div>
                <div>
                    <p className="text-xs font-medium text-muted-foreground">{t('Name')}</p>
                    <p className="text-sm">{provider.name}</p>
                </div>
                <div>
                    <p className="text-xs font-medium text-muted-foreground">{t('Type')}</p>
                    <p className="text-sm">{provider.provider_type}</p>
                </div>
                <div>
                    <p className="text-xs font-medium text-muted-foreground">{t('Contact Name')}</p>
                    <p className="text-sm">{provider.contact_name ?? '—'}</p>
                </div>
                <div>
                    <p className="text-xs font-medium text-muted-foreground">{t('Phone')}</p>
                    <p className="text-sm">{provider.phone ?? '—'}</p>
                </div>
                <div>
                    <p className="text-xs font-medium text-muted-foreground">{t('Email')}</p>
                    <p className="text-sm">{provider.email ?? '—'}</p>
                </div>
                <div>
                    <p className="text-xs font-medium text-muted-foreground">{t('Status')}</p>
                    <StatusCell
                        value={provider.is_active}
                        activeLabel={t('Active')}
                        inactiveLabel={t('Inactive')}
                    />
                </div>
                <div>
                    <p className="text-xs font-medium text-muted-foreground">{t('Created At')}</p>
                    <p className="text-sm">{provider.created_at}</p>
                </div>
            </CardContent>
        </Card>
    );
}