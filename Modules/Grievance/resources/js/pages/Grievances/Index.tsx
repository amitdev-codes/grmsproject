import { DataTable } from '@/components/data-table/data-table';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import type {
    DataTableFilterField,
    DataTableRoutes,
    PaginationMeta,
} from '@/types/data-table';
import { GRIEVANCE_STATUS_OPTIONS } from '@/types/grievance-status';
import { router, usePage } from '@inertiajs/react';
import { FileWarning } from 'lucide-react';
import { useState } from 'react';
import { route } from 'ziggy-js';
import IndexLayout from '../../../../../../resources/js/components/index-layout';
import { columns } from './columns';
import type {
    Grievance,
    Category,
    District,
    Division,
    Section,
} from './columns';
import { GrievanceViewContent } from './GrievanceViewContent';

interface GrievanceIndexProps {
    data: Grievance[];
    meta: PaginationMeta;
    categories: Category[];
    districts: District[];
    divisions: Division[];
    sections: Section[];
    officers: { id: number; name: string; section_id: number | null }[];
}

const routes: DataTableRoutes = {
    index: 'grievances.index',
    create: 'grievances.create',
    edit: 'grievances.edit',
    destroy: 'grievances.destroy',
    bulkDestroy: 'grievances.bulk-destroy',
    export: 'grievances.export',
};

const PRIORITY_OPTIONS = [
    { label: 'Low', value: 'low' },
    { label: 'Normal', value: 'normal' },
    { label: 'High', value: 'high' },
];

export default function GrievanceIndex({
    data,
    meta,
    categories,
    districts,
    divisions,
    sections,
    officers,
}: GrievanceIndexProps) {
    const page = usePage<{
        auth: {
            user?: {
                role_names?: string;
                division_id?: number | null;
                section_id?: number | null;
            };
        };
    }>();
    const user = page.props.auth.user;
    const roles =
        page.props.auth.user?.role_names
            ?.split(',')
            .map((role) => role.trim()) ?? [];
    const [forwarding, setForwarding] = useState<Grievance | null>(null);
    const [destination, setDestination] = useState('');
    const [remarks, setRemarks] = useState('');

    const forwardingMode = forwarding
        ? forwarding.status === 'allocated_division'
            ? 'section'
            : forwarding.status === 'allocated_section'
              ? 'officer'
              : 'division'
        : null;

    const openForwarding = (row: Grievance) => {
        setForwarding(row);
        setDestination('');
        setRemarks('');
    };

    const submitForwarding = () => {
        if (!forwarding || !destination || !forwardingMode) {
            return;
        }

        const endpoint =
            forwardingMode === 'division'
                ? 'grievances.allocate-division'
                : forwardingMode === 'section'
                  ? 'grievances.allocate-section'
                  : 'grievances.assign-officer';
        const field =
            forwardingMode === 'division'
                ? 'division_id'
                : forwardingMode === 'section'
                  ? 'section_id'
                  : 'officer_id';

        router.post(
            route(endpoint, forwarding.id),
            { [field]: destination, remarks },
            {
                preserveScroll: true,
                onSuccess: () => setForwarding(null),
            },
        );
    };

    const workflowActions = (row: Grievance) => {
        const isDirectorQueue =
            roles.includes('Director') &&
            ['submitted', 'reallocation_required'].includes(row.status) &&
            !row.division;
        const isDivisionQueue =
            roles.includes('Division Director') &&
            row.status === 'allocated_division' &&
            row.division?.id === user?.division_id;
        const isSectionQueue =
            roles.includes('Section Manager') &&
            row.status === 'allocated_section' &&
            row.section?.id === user?.section_id;
        const canForward = isDirectorQueue || isDivisionQueue || isSectionQueue;
        const canReject = isDirectorQueue || isSectionQueue;

        return (
            <>
                {canForward && (
                    <Button
                        type="button"
                        size="sm"
                        variant="outline"
                        className="h-6 px-2 text-[11px]"
                        onClick={() => openForwarding(row)}
                    >
                        Forward
                    </Button>
                )}
                {canReject && (
                    <Button
                        type="button"
                        size="sm"
                        variant="destructive"
                        className="h-6 px-2 text-[11px]"
                        onClick={() => {
                            const reason = window.prompt(
                                'Reason for rejection is required:',
                            );

                            if (!reason?.trim()) {
                                return;
                            }

                            const endpoint =
                                roles.includes('Section Manager') &&
                                row.status === 'allocated_section'
                                    ? 'grievances.reject-allocation'
                                    : 'grievances.reject';
                            router.post(
                                route(endpoint, row.id),
                                { reason },
                                { preserveScroll: true },
                            );
                        }}
                    >
                        Reject
                    </Button>
                )}
            </>
        );
    };

    const filterFields: DataTableFilterField[] = [
        { id: 'status', title: 'Status', options: GRIEVANCE_STATUS_OPTIONS },
        { id: 'priority', title: 'Priority', options: PRIORITY_OPTIONS },
        {
            id: 'category_id',
            title: 'Category',
            options: categories.map((c) => ({
                label: c.name_en,
                value: String(c.id),
            })),
        },
        {
            id: 'district_id',
            title: 'District',
            options: districts.map((d) => ({
                label: d.name,
                value: String(d.id),
            })),
        },
        {
            id: 'division_id',
            title: 'Division',
            options: divisions.map((d) => ({
                label: d.name,
                value: String(d.id),
            })),
        },
        {
            id: 'section_id',
            title: 'Section',
            options: sections.map((s) => ({
                label: s.name,
                value: String(s.id),
            })),
        },
    ];

    return (
        <IndexLayout
            title="Grievances"
            breadcrumbs={[{ label: 'Grievances', icon: FileWarning }]}
        >
            <div>
                <DataTable<Grievance, unknown>
                    columns={columns}
                    data={data}
                    meta={meta}
                    routes={routes}
                    filterFields={filterFields}
                    title="Grievances"
                    searchPlaceholder="Search by reference number, complainant…"
                    resourceLabel="New Grievance"
                    defaultSort="created_at"
                    defaultOrder="desc"
                    getRowLabel={(row) => row.reference_no}
                    viewContent={(row) => (
                        <GrievanceViewContent grievance={row} />
                    )}
                    rowActions={workflowActions}
                />
            </div>
            <Dialog
                open={!!forwarding}
                onOpenChange={(open) => !open && setForwarding(null)}
            >
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Forward grievance</DialogTitle>
                        <DialogDescription>
                            {forwarding?.reference_no} will leave your queue
                            after submission.
                        </DialogDescription>
                    </DialogHeader>
                    <div className="space-y-4">
                        <label className="block space-y-1.5 text-sm font-medium">
                            <span>
                                {forwardingMode === 'division'
                                    ? 'Division'
                                    : forwardingMode === 'section'
                                      ? 'Section'
                                      : 'Investigating Officer'}
                            </span>
                            <select
                                className="h-10 w-full rounded-md border bg-background px-3 text-sm"
                                value={destination}
                                onChange={(event) =>
                                    setDestination(event.target.value)
                                }
                            >
                                <option value="">Select destination</option>
                                {forwardingMode === 'division' &&
                                    divisions.map((division) => (
                                        <option
                                            key={division.id}
                                            value={division.id}
                                        >
                                            {division.name}
                                        </option>
                                    ))}
                                {forwardingMode === 'section' &&
                                    sections
                                        .filter(
                                            (section) =>
                                                !forwarding?.division?.id ||
                                                section.division_id ===
                                                    forwarding.division.id,
                                        )
                                        .map((section) => (
                                            <option
                                                key={section.id}
                                                value={section.id}
                                            >
                                                {section.name}
                                            </option>
                                        ))}
                                {forwardingMode === 'officer' &&
                                    officers
                                        .filter(
                                            (officer) =>
                                                !forwarding?.section?.id ||
                                                officer.section_id ===
                                                    forwarding.section.id,
                                        )
                                        .map((officer) => (
                                            <option
                                                key={officer.id}
                                                value={officer.id}
                                            >
                                                {officer.name}
                                            </option>
                                        ))}
                            </select>
                        </label>
                        <label className="block space-y-1.5 text-sm font-medium">
                            <span>Remarks</span>
                            <textarea
                                rows={4}
                                className="w-full rounded-md border bg-background px-3 py-2 text-sm"
                                value={remarks}
                                onChange={(event) =>
                                    setRemarks(event.target.value)
                                }
                                placeholder="Add handover or investigation remarks"
                            />
                        </label>
                    </div>
                    <DialogFooter>
                        <Button
                            variant="outline"
                            onClick={() => setForwarding(null)}
                        >
                            Cancel
                        </Button>
                        <Button
                            disabled={!destination}
                            onClick={submitForwarding}
                        >
                            Submit and forward
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </IndexLayout>
    );
}
