<?php

namespace Modules\UserManagement\Services;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Modules\UserManagement\DataTables\PermissionDataTable;
use Modules\UserManagement\Repositories\PermissionRepository;
use Spatie\Permission\Models\Permission;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PermissionService
{
    public function __construct(
        protected PermissionRepository $repository,
        protected PermissionDataTable $dataTable
    ) {}

    public function table(Request $request): array
    {
        return $this->dataTable->toArray($request);
    }
    public function forCreate(): array
    {
        return [
            'name' => null,
        ];
    }

    public function forEdit(Permission $permission): array
    {

        return [
            'permission' => $permission,
        ];
    }

    public function store(array $data): Permission
    {
        return $this->repository->create([
            'name' => $data['name'],
        ]);
    }

    public function update(Permission $permission, array $data): Permission
    {
        $this->repository->update($permission, [
            'name' => $data['name'],
        ]);

        $permission->syncRoles([$data['role']]);
        return $permission->refresh();
    }

    public function destroy(Permission $permission): bool
    {
        return $this->repository->delete($permission);
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
            'users-'.now()->format('Y-m-d_His').'.xlsx',
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
                'name' => $row['name'],
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
            'name' => 'name',
        ];
    }

    protected function importRules(): array
    {
        return [
            'name' => 'required|string|max:255',
        ];
    }
}
