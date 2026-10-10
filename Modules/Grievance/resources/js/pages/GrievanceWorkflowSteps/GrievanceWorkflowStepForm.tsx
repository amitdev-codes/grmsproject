import {
    NumberField,
    Select2Field,
    StatusField,
    TextField,
    TextareaField,
} from '@/components/form-fields';
import { FormLayout } from '@/components/form-layout';
import { useTranslation } from '@/hooks/use-translation';
import { useForm } from '@inertiajs/react';
import { GitBranch } from 'lucide-react';
import { route } from 'ziggy-js';
import type { GrievanceWorkflowStep } from './columns';

interface Option {
    value: string;
    label: string;
}

interface GrievanceWorkflowStepFormProps {
    workflowStep: GrievanceWorkflowStep | null;
    roles: Option[];
    users: Option[];
}

interface FormValues {
    step_number: string;
    name: string;
    description: string;
    role_name: string;
    approver_user_id: string;
    approval_action: string;
    rejection_action: string;
    rejection_target_step: string;
    is_final_approval: '1' | '0';
    is_active: '1' | '0';
}

const approvalActions = [
    { value: 'advance', label: 'Advance to next level' },
    { value: 'resolve', label: 'Resolve grievance' },
];

const rejectionActions = [
    { value: 'reject', label: 'Reject grievance' },
    { value: 'return_to_step', label: 'Return to an earlier level' },
];

export default function Form({
    workflowStep,
    roles,
    users,
}: GrievanceWorkflowStepFormProps) {
    const isEdit = !!workflowStep;
    const { t } = useTranslation();
    const { data, setData, post, transform, processing, errors } =
        useForm<FormValues>({
            step_number: String(workflowStep?.step_number ?? ''),
            name: workflowStep?.name ?? '',
            description: workflowStep?.description ?? '',
            role_name: workflowStep?.role_name ?? '',
            approver_user_id: workflowStep?.approver_user_id
                ? String(workflowStep.approver_user_id)
                : '',
            approval_action: workflowStep?.approval_action ?? 'advance',
            rejection_action: workflowStep?.rejection_action ?? 'reject',
            rejection_target_step: workflowStep?.rejection_target_step
                ? String(workflowStep.rejection_target_step)
                : '',
            is_final_approval: workflowStep?.is_final_approval ? '1' : '0',
            is_active: workflowStep?.is_active === false ? '0' : '1',
        });

    const submit = (event: React.SyntheticEvent) => {
        event.preventDefault();
        const url = isEdit
            ? route('grievance-workflow-steps.update', workflowStep!.id)
            : route('grievance-workflow-steps.store');

        transform((formData) => ({
            ...formData,
            ...(formData.approver_user_id ? { role_name: '' } : {}),
            ...(formData.rejection_action !== 'return_to_step'
                ? { rejection_target_step: '' }
                : {}),
            ...(isEdit ? { _method: 'put' } : {}),
        }));

        post(url, { preserveScroll: true });
    };

    const userOptions = [{ value: '', label: 'Use a role' }, ...users];
    const roleOptions = [{ value: '', label: 'Select a role' }, ...roles];
    const targetOptions = [
        { value: '', label: 'Select a level' },
        ...(Number(data.step_number) > 1
            ? Array.from(
                  { length: Number(data.step_number) - 1 },
                  (_, index) => ({
                      value: String(index + 1),
                      label: `Level ${index + 1}`,
                  }),
              )
            : []),
    ];

    return (
        <FormLayout
            title={isEdit ? t('Edit Workflow Step') : t('Create Workflow Step')}
            description="Set who handles this level and what happens when it is approved or rejected."
            breadcrumbs={[
                {
                    label: t('Grievance Workflow'),
                    icon: GitBranch,
                    href: route('grievance-workflow-steps.index'),
                },
                {
                    label: isEdit
                        ? t('Edit Workflow Step')
                        : t('Create Workflow Step'),
                },
            ]}
            onSubmit={submit}
            processing={processing}
            submitLabel={isEdit ? t('Save changes') : t('Create Workflow Step')}
        >
            <NumberField
                id="step_number"
                required
                min={1}
                label={t('Level number')}
                value={data.step_number}
                onChange={(value) => setData('step_number', value)}
                error={errors.step_number}
            />
            <TextField
                id="name"
                required
                label={t('Step name')}
                value={data.name}
                onChange={(value) => setData('name', value)}
                error={errors.name}
            />
            <TextareaField
                id="description"
                label={t('Description')}
                value={data.description}
                onChange={(value) => setData('description', value)}
                error={errors.description}
            />
            <Select2Field
                id="role_name"
                label={t('Responsible role')}
                value={data.role_name}
                onChange={(value) => {
                    setData('role_name', value);

                    if (value) {
                        setData('approver_user_id', '');
                    }
                }}
                options={roleOptions}
                error={errors.role_name}
                placeholder={t('Select a role')}
            />
            <Select2Field
                id="approver_user_id"
                label={t('Or assign to a specific user')}
                value={data.approver_user_id}
                onChange={(value) => {
                    setData('approver_user_id', value);

                    if (value) {
                        setData('role_name', '');
                    }
                }}
                options={userOptions}
                error={errors.approver_user_id}
                placeholder={t('Search users')}
            />
            <Select2Field
                id="approval_action"
                required
                label={t('When approved')}
                value={data.approval_action}
                onChange={(value) => setData('approval_action', value)}
                options={approvalActions}
                error={errors.approval_action}
            />
            <Select2Field
                id="rejection_action"
                required
                label={t('When rejected')}
                value={data.rejection_action}
                onChange={(value) => setData('rejection_action', value)}
                options={rejectionActions}
                error={errors.rejection_action}
            />
            {data.rejection_action === 'return_to_step' && (
                <Select2Field
                    id="rejection_target_step"
                    required
                    label={t('Return to level')}
                    value={data.rejection_target_step}
                    onChange={(value) =>
                        setData('rejection_target_step', value)
                    }
                    options={targetOptions}
                    error={errors.rejection_target_step}
                />
            )}
            <StatusField
                id="is_final_approval"
                label={t('Final approval level')}
                value={data.is_final_approval}
                onChange={(value) => setData('is_final_approval', value)}
                error={errors.is_final_approval}
                activeLabel={t('Yes')}
                inactiveLabel={t('No')}
            />
            <StatusField
                id="is_active"
                label={t('Active')}
                value={data.is_active}
                onChange={(value) => setData('is_active', value)}
                error={errors.is_active}
                activeLabel={t('Active')}
                inactiveLabel={t('Inactive')}
            />
        </FormLayout>
    );
}
