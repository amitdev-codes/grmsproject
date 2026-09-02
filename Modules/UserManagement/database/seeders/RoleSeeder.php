<?php

namespace Modules\UserManagement\Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $defaultRoles = config('usermanagement.default_roles', []);
        $rolePermissionsConfig = config('usermanagement.role_permissions', []);
        $permissionsConfig = config('usermanagement.permissions', []);
        $allPermissions = Permission::pluck('name')->all();

        foreach ($defaultRoles as $roleData) {
            $role = Role::updateOrCreate(
                ['code' => $roleData['code']],
                [
                    'name' => $roleData['name'],
                    'name_st' => $roleData['name_st'],
                    'code' => $roleData['code'],
                    'status' => $roleData['status'],
                    'guard_name' => 'web',
                ]
            );

            $rolePermissions = $rolePermissionsConfig[$roleData['name']] ?? [];

            if ($rolePermissions === '*' || (is_array($rolePermissions) && in_array('*', $rolePermissions, true))) {
                $role->syncPermissions($allPermissions);
                $this->command->info("✅ {$role->name}: ALL permissions (" . count($allPermissions) . ')');

                continue;
            }

            $expanded = $this->expandPermissions($rolePermissions, $permissionsConfig);
            $role->syncPermissions($expanded);

            $this->command->info("✅ {$role->name}: " . count($expanded) . ' permissions');
        }
    }

    private function expandPermissions(array $rolePermissions, array $permissionsConfig): array
    {
        $expanded = [];
        foreach ($rolePermissions as $permission) {
            if (str_ends_with($permission, '.*')) {
                $resource = str_replace('.*', '', $permission);
                foreach ($permissionsConfig[$resource] ?? [] as $action) {
                    $expanded[] = "{$resource}.{$action}";
                }
            } else {
                $expanded[] = $permission;
            }
        }

        return array_unique($expanded);
    }
}
