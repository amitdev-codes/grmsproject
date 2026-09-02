import { Badge } from '@/components/ui/badge';
import { isActiveStatus } from '@/lib/status';

interface StatusCellProps {
    value: unknown;
    activeLabel?: string;
    inactiveLabel?: string;
}

export function StatusCell({
    value,
    activeLabel = 'Active',
    inactiveLabel = 'Inactive',
}: StatusCellProps) {
    const active = isActiveStatus(value);

    return (
        <Badge variant={active ? 'default' : 'secondary'}>
            {active ? activeLabel : inactiveLabel}
        </Badge>
    );
}
