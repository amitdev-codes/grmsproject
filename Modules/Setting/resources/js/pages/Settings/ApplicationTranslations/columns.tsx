import { DataTableColumnHeader } from '@/components/data-table/data-table-column-header';
import type { ColumnDef } from '@tanstack/react-table';

export interface ApplicationTranslation {
    id: number;
    translation_key: string;
    english_text: string;
    sesotho_text: string | null;
    created_at: string;
}

export const columns: ColumnDef<ApplicationTranslation>[] = [
    {
        accessorKey: 'translation_key',
        header: ({ column }) => (
            <DataTableColumnHeader column={column} title="Translation key" />
        ),
        cell: ({ row }) => (
            <span className="font-mono text-xs">
                {row.original.translation_key}
            </span>
        ),
    },
    {
        accessorKey: 'english_text',
        header: ({ column }) => (
            <DataTableColumnHeader column={column} title="English" />
        ),
    },
    {
        accessorKey: 'sesotho_text',
        header: ({ column }) => (
            <DataTableColumnHeader column={column} title="Sesotho" />
        ),
        cell: ({ row }) =>
            row.original.sesotho_text || (
                <span className="text-amber-700">Not translated</span>
            ),
    },
];
