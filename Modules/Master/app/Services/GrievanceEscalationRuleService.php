<?php

namespace Modules\Master\Services;

use Illuminate\Http\Request;
use Modules\Master\DataTable\GrievanceEscalationRuleDataTable;
use Modules\Master\Models\GrievanceEscalationRule;
use Modules\Master\Repository\GrievanceEscalationRuleRepository;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class GrievanceEscalationRuleService
{
    public function __construct(
        protected GrievanceEscalationRuleRepository $repository,
        protected GrievanceEscalationRuleDataTable $dataTable
    ) {}

    public function table(Request $request): array
    {
        return $this->dataTable->toArray($request);
    }

    public function forCreate(): array
    {
        return ['grievanceEscalationRule' => null];
    }

    public function forEdit(GrievanceEscalationRule $grievanceEscalationRule): array
    {
        return ['grievanceEscalationRule' => $grievanceEscalationRule];
    }

    public function store(array $data): GrievanceEscalationRule
    {
        return $this->repository->create($data);
    }

    public function update(GrievanceEscalationRule $grievanceEscalationRule, array $data): GrievanceEscalationRule
    {
        $this->repository->update($grievanceEscalationRule, $data);
        return $grievanceEscalationRule->refresh();
    }

    public function destroy(GrievanceEscalationRule $grievanceEscalationRule): bool
    {
        return $this->repository->delete($grievanceEscalationRule);
    }

    public function bulkDestroy(array $ids): int
    {
        return $this->repository->bulkDelete($ids);
    }

    public function export(Request $request): BinaryFileResponse
    {
        return $this->repository->export(
            $this->dataTable->exportColumns(),
            $this->dataTable->exportQuery($request),
            'grievance-escalation-rule-' . now()->format('Y-m-d_His') . '.xlsx',
        );
    }
}