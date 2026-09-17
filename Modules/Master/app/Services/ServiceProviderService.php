<?php

namespace Modules\Master\Services;

use Illuminate\Http\Request;
use Modules\Master\DataTable\ServiceProviderDataTable;
use Modules\Master\Models\ServiceProvider;
use Modules\Master\Repository\ServiceProviderRepository;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ServiceProviderService
{
    public function __construct(
        protected ServiceProviderRepository $repository,
        protected ServiceProviderDataTable $dataTable
    ) {}

    public function table(Request $request): array
    {
        return $this->dataTable->toArray($request);
    }

    public function forCreate(): array
    {
        return ['serviceProvider' => null];
    }

    public function forEdit(ServiceProvider $serviceProvider): array
    {
        return ['serviceProvider' => $serviceProvider];
    }

    public function store(array $data): ServiceProvider
    {
        return $this->repository->create($data);
    }

    public function update(ServiceProvider $serviceProvider, array $data): ServiceProvider
    {
        $this->repository->update($serviceProvider, $data);
        return $serviceProvider->refresh();
    }

    public function destroy(ServiceProvider $serviceProvider): bool
    {
        return $this->repository->delete($serviceProvider);
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
            'service-provider-' . now()->format('Y-m-d_His') . '.xlsx',
        );
    }
}