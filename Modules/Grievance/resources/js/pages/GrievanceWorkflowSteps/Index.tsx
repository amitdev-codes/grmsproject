import { DataTable } from '@/components/data-table/data-table';
import IndexLayout from '@/components/index-layout';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import type { DataTableRoutes, PaginationMeta } from '@/types/data-table';
import { ArrowDown, ArrowDownRight, GitBranch, Route } from 'lucide-react';
import { useState } from 'react';
import { columns } from './columns';
import type { GrievanceWorkflowStep } from './columns';

interface GrievanceWorkflowStepIndexProps {
    data: GrievanceWorkflowStep[];
    workflowSteps: GrievanceWorkflowStep[];
    meta: PaginationMeta;
}

const routes: DataTableRoutes = {
    index: 'grievance-workflow-steps.index',
    create: 'grievance-workflow-steps.create',
    edit: 'grievance-workflow-steps.edit',
    destroy: 'grievance-workflow-steps.destroy',
};

export default function GrievanceWorkflowStepIndex({
    data,
    workflowSteps,
    meta,
}: GrievanceWorkflowStepIndexProps) {
    const [previewOpen, setPreviewOpen] = useState(false);
    const activeSteps = workflowSteps.filter((step) => step.is_active);

    return (
        <IndexLayout
            title="Grievance Workflow"
            breadcrumbs={[{ label: 'Grievance Workflow', icon: GitBranch }]}
        >
            <div className="flex justify-end">
                <Button
                    type="button"
                    variant="outline"
                    onClick={() => setPreviewOpen(true)}
                >
                    <Route />
                    Show workflow
                </Button>
            </div>
            <DataTable<GrievanceWorkflowStep, unknown>
                columns={columns}
                data={data}
                meta={meta}
                routes={routes}
                title="Grievance Workflow"
                description="Configure the review, approval, rejection, and resolution steps used for grievances."
                searchPlaceholder="Search by step or role…"
                resourceLabel="New Workflow Step"
                defaultSort="step_number"
                defaultOrder="asc"
                getRowLabel={(row) => `Level ${row.step_number}: ${row.name}`}
            />
            <Dialog open={previewOpen} onOpenChange={setPreviewOpen}>
                <DialogContent className="max-h-[85vh] overflow-y-auto sm:max-w-3xl">
                    <DialogHeader>
                        <DialogTitle>Configured grievance redressal flow</DialogTitle>
                        <DialogDescription>
                            This view uses the currently saved workflow steps and
                            routing rules. Inactive steps are omitted.
                        </DialogDescription>
                    </DialogHeader>
                    {activeSteps.length === 0 ? (
                        <p className="py-8 text-center text-sm text-muted-foreground">
                            No active workflow steps are configured.
                        </p>
                    ) : (
                        <ol className="space-y-2">
                            {activeSteps.map((step, index) => {
                                const nextStep = activeSteps[index + 1];
                                const approvalRoute = step.approval_action === 'resolve'
                                    ? 'Resolves the grievance'
                                    : step.approval_target_step
                                      ? `Routes to Level ${step.approval_target_step}`
                                      : nextStep
                                        ? `Advances to Level ${nextStep.step_number}`
                                        : 'No next active step configured';
                                const rejectionRoute = step.rejection_action === 'return_to_step'
                                    ? `Returns to Level ${step.rejection_target_step}`
                                    : 'Rejects the grievance';

                                return (
                                    <li key={step.id} className="relative">
                                        <div className="rounded-lg border bg-card p-4">
                                            <div className="flex flex-wrap items-center gap-2">
                                                <Badge variant="outline">
                                                    Level {step.step_number}
                                                </Badge>
                                                <h3 className="font-semibold">{step.name}</h3>
                                                <Badge variant="secondary">
                                                    {step.action_type.replaceAll('_', ' ')}
                                                </Badge>
                                            </div>
                                            {step.description && (
                                                <p className="mt-2 text-sm text-muted-foreground">
                                                    {step.description}
                                                </p>
                                            )}
                                            <p className="mt-2 text-sm">
                                                <span className="font-medium">Handled by: </span>
                                                {step.approver_user?.name ?? step.role_name ?? 'Unassigned'}
                                            </p>
                                            <div className="mt-3 grid gap-2 text-sm sm:grid-cols-2">
                                                <p>
                                                    <span className="font-medium">On approval: </span>
                                                    {approvalRoute}
                                                </p>
                                                <p>
                                                    <span className="font-medium">On rejection: </span>
                                                    {rejectionRoute}
                                                </p>
                                            </div>
                                        </div>
                                        {nextStep && (
                                            <div className="flex h-9 items-center justify-center text-muted-foreground">
                                                <ArrowDown className="size-4" />
                                            </div>
                                        )}
                                        {step.approval_target_step && (
                                            <p className="mt-2 flex items-center gap-2 text-xs text-muted-foreground">
                                                <ArrowDownRight className="size-4" />
                                                Configured approval branch to Level {step.approval_target_step}
                                            </p>
                                        )}
                                    </li>
                                );
                            })}
                        </ol>
                    )}
                </DialogContent>
            </Dialog>
        </IndexLayout>
    );
}
