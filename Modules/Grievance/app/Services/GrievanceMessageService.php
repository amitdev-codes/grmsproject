<?php

namespace Modules\Grievance\Services;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Modules\Grievance\Datatable\GrievanceMessageDataTable;
use Modules\Grievance\Models\Grievance;
use Modules\Grievance\Models\GrievanceMessage;
use Modules\Grievance\Repositories\GrievanceMessageRepository;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class GrievanceMessageService
{
    public function __construct(
        protected GrievanceMessageRepository $repository,
        protected GrievanceMessageDataTable $dataTable,
    ) {}

    public function table(Request $request): array
    {
        return $this->dataTable->toArray($request);
    }
    public function forCreate(): array
    {
        return [
            'grievanceMessage' => null,
            ...$this->lookups(),
        ];
    }
    public function forEdit(GrievanceMessage $grievanceMessage): array
    {
        return [
            'grievanceMessage' => $grievanceMessage->load(['grievance', 'user']),
            ...$this->lookups(),
        ];
    }
    protected function lookups(): array
    {
        return [
            'grievances' => $this->grievanceOptions(),
            'users' => $this->userOptions(),
        ];
    }
    protected function grievanceOptions(): Collection
    {
        return Grievance::query()
            ->select(['id', 'reference_number'])
            ->orderBy('reference_number')
            ->get();
    }

    protected function userOptions(): Collection
    {
        return User::query()
            ->select(['id', 'name'])
            ->orderBy('name')
            ->get();
    }
    public function store(array $data): GrievanceMessage
    {
        return $this->repository->create($data);
    }
    public function update(GrievanceMessage $grievanceMessage, array $data): GrievanceMessage
    {
        $this->repository->update($grievanceMessage, $data);

        return $grievanceMessage->refresh();
    }
    public function destroy(GrievanceMessage $grievanceMessage): bool
    {
        return $this->repository->delete($grievanceMessage);
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
            'grievance-messages-'.now()->format('Y-m-d_His').'.xlsx',
        );
    }


}
