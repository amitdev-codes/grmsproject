<?php

namespace Modules\Grievance\Services;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Modules\Grievance\Datatable\GrievanceChannelDataTable;
use Modules\Grievance\Models\GrievanceChannel;
use Modules\Grievance\Repositories\GrievanceChannelRepository;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class GrievanceChannelService
{
    public function __construct(
        protected GrievanceChannelRepository $repository,
        protected GrievanceChannelDataTable $dataTable,
    ) {}

    public function table(Request $request): array
    {
        return $this->dataTable->toArray($request);
    }

    public function forCreate(): array
    {
        return ['grievanceChannel' => null];
    }

    public function forEdit(GrievanceChannel $grievanceChannel): array
    {
        return ['grievanceChannel' => $grievanceChannel];
    }

    public function store(array $data): GrievanceChannel
    {
        return $this->repository->create($data);
    }

    public function update(GrievanceChannel $grievanceChannel, array $data): GrievanceChannel
    {
        $this->repository->update($grievanceChannel, $data);

        return $grievanceChannel->refresh();
    }

    public function destroy(GrievanceChannel $grievanceChannel): bool
    {
        return $this->repository->delete($grievanceChannel);
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
            'grievanceChannel-'.now()->format('Y-m-d_His').'.xlsx',
        );
    }

    /**
     * @return array{created: int, failures: array}
     */
    public function import(UploadedFile $file): array
    {
        $parsed = $this->repository->import($file, $this->importMap(), $this->importRules());

        $created = 0;

        foreach ($parsed['imported'] as $row) {
            $this->repository->create([
                'code' => $row['code'] ?? null,
                'name' => $row['name'],
                'is_active' => $row['is_active'] ?? true,
            ]);
            $created++;
        }

        return [
            'created' => $created,
            'failures' => $parsed['failures'],
        ];
    }

    protected function importMap(): array
    {
        return [
            'code' => 'Code',
            'name' => 'name',
            'is_active' => 'is_active',
        ];
    }

    protected function importRules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:255'],
            'is_active' => ['boolean'],
        ];
    }
}
