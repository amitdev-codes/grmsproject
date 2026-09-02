<?php

namespace Modules\UserManagement\Services;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Modules\UserManagement\DataTables\RoleDataTable;
use Modules\UserManagement\Repositories\RoleRepository;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class RoleService
{
    public function __construct(
        protected RoleRepository $repository,
        protected RoleDataTable $dataTable,
    ) {}

    public function table(Request $request): array
    {
        return $this->dataTable->toArray($request);
    }

    public function forCreate(): array
    {
        return [
            'role' => null,
            'permissions' => $this->groupedPermissions(),
        ];
    }

    public function forEdit(Role $role): array
    {
        $role->load('permissions');

        return [
            'role' => [
                ...$role->toArray(),
                'permissions' => $role->permissions->pluck('name'),
            ],
            'permissions' => $this->groupedPermissions(),
        ];
    }

    public function store(array $data): Role
    {
        $role = $this->repository->create([
            'code' => $data['code'],
            'name' => $data['name'],
            'name_st' => $data['name_st'],
            'status' => $data['status'] ?? true,
            'guard_name' => 'web',
        ]);

        $role->syncPermissions($data['permissions'] ?? []);

        return $role;
    }

    public function update(Role $role, array $data): Role
    {
        $this->repository->update($role, [
            'code' => $data['code'],
            'name' => $data['name'],
            'name_st' => $data['name_st'],
            'status' => $data['status'] ?? $role->status,
        ]);

        $role->syncPermissions($data['permissions'] ?? []);

        return $role->refresh();
    }

    public function destroy(Role $role): bool
    {
        return $this->repository->delete($role);
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
            'roles-'.now()->format('Y-m-d_His').'.xlsx',
        );
    }
    protected function groupedPermissions(): array
    {
        return Permission::orderBy('name')
            ->get()
            ->groupBy(fn (Permission $permission) => explode('.', $permission->name)[0] ?? 'general')
            ->map(fn ($perms) => $perms->map(fn (Permission $p) => [
                'name' => $p->name,
                'action' => explode('.', $p->name)[1] ?? $p->name,
            ])->values())
            ->toArray();
    }
    /**
     * @return array{created: int, failures: array}
     */
    public function import(UploadedFile $file): array
    {
        $parsed = $this->repository->import($file, $this->importMap(), $this->importRules());

        $created = 0;

        foreach ($parsed['imported'] as $row) {
            $role = $this->repository->create([
                'code' => $row['code'],
                'name' => $row['name'],
                'name_st' => $row['name_st'],
                'status' => filter_var($row['status'] ?? true, FILTER_VALIDATE_BOOLEAN),
                'guard_name' => 'web',
            ]);

            if (!empty($row['permissions'])) {
                $role->syncPermissions(array_map('trim', explode(',', $row['permissions'])));
            }

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
            'code' => 'code',
            'name' => 'name',
            'name_st' => 'name_st',
            'status' => 'status',
            'permissions' => 'permissions',
        ];
    }

    protected function importRules(): array
    {
        return [
            'code' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'name_st' => 'required|string|max:255',
        ];
    }
}
