import { formatDate } from '@/lib/format';

interface DateCellProps {
    value: string | Date | null | undefined;
}

export function DateCell({ value }: DateCellProps) {
    return <>{formatDate(value)}</>;
}
