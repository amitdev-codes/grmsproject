import { DataTableColumnHeader } from '@/components/data-table/data-table-column-header';
import { DateCell } from '@/components/data-table/date-cell';
import { Badge } from '@/components/ui/badge';
import type { ColumnDef } from '@tanstack/react-table';

export interface Division {
    id: number;
    name: string;
}

export interface Section {
    id: number;
    code: string;
    division_id: number;
    name: string;
    name_st: string | null;
    division?: Division | null;
    created_at: string;
}

const divisionVariant = (
    division: string,
): 'default' | 'secondary' | 'outline' =>
    division === 'Northern Region'
        ? 'default'
        : division === 'Central Region'
          ? 'secondary'
          : 'outline';

export const Columns: ColumnDef<Section>[] = [
    {
        accessorKey: 'code',
        header: ({ column }) => (
            <DataTableColumnHeader column={column} title="Code" />
        ),
    },
    {
        accessorKey: 'name',
        header: ({ column }) => (
            <DataTableColumnHeader column={column} title="Name" />
        ),
    },
    {
        accessorKey: 'name_st',
        header: ({ column }) => (
            <DataTableColumnHeader column={column} title="Name ST" />
        ),
        cell: ({ row }) => row.original.name_st ?? '—',
    },
    {
        id: 'division',
        header: ({ column }) => (
            <DataTableColumnHeader column={column} title="Division" />
        ),
        cell: ({ row }) => {
            const division = row.original.division;
            return division ? (
                <Badge variant={divisionVariant(division.name)}>
                    {division.name}
                </Badge>
            ) : (
                '—'
            );
        },
        enableSorting: false,
    },
    {
        accessorKey: 'created_at',
        header: ({ column }) => (
            <DataTableColumnHeader column={column} title="Created" />
        ),
        cell: ({ row }) => <DateCell value={row.original.created_at} />,
    },
];
