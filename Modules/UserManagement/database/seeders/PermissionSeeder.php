<?php

namespace Modules\UserManagement\Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissionsConfig = config('usermanagement.permissions', []);

        $count = 0;
        foreach ($permissionsConfig as $resource => $actions) {
            foreach ($actions as $action) {
                Permission::firstOrCreate([
                    'name' => "{$resource}.{$action}",
                    'guard_name' => 'web',
                ]);
                $count++;
            }
        }

        // Additional permissions for models not in config but have tables/controllers
        $additionalPermissions = [
            // Grievance related
            'grievance_categories' => ['view', 'create', 'edit', 'delete', 'export'],
            'grievance_channels' => ['view', 'create', 'edit', 'delete', 'export'],
            'grievance_escalations' => ['view', 'create', 'edit', 'delete', 'export'],
            'grievance_messages' => ['view', 'create', 'edit', 'delete', 'export'],
            'grievance_status_histories' => ['view', 'export'],
            'grievance_sla_policies' => ['view', 'create', 'edit', 'delete', 'export'],
            'grievance_escalation_rules' => ['view', 'create', 'edit', 'delete', 'export'],
            'resolutions' => ['view', 'create', 'edit', 'delete', 'export'],
            'resolution_sign_offs' => ['view', 'create', 'edit', 'delete', 'export'],
            'resolution_approval_actions' => ['view', 'create', 'edit', 'delete', 'export'],
            'inbound_sms' => ['view', 'export'],
            'reference_sequences' => ['view'],

            // Master data
            'districts' => ['view', 'create', 'edit', 'delete', 'export'],
            'divisions' => ['view', 'create', 'edit', 'delete', 'export'],
            'sections' => ['view', 'create', 'edit', 'delete', 'export'],
            'project_types' => ['view', 'create', 'edit', 'delete', 'export'],
            'projects' => ['view', 'create', 'edit', 'delete', 'export'],
            'service_providers' => ['view', 'create', 'edit', 'delete', 'export'],

            // User Management
            'roles' => ['view', 'create', 'edit', 'delete'],
            'users' => ['view', 'create', 'edit', 'delete', 'export'],
            'permissions' => ['view'],

            // Frontend/Setting
            'faqs' => ['view', 'create', 'edit', 'delete'],
            'contacts' => ['view', 'edit', 'delete', 'export'],
            'application_settings' => ['view', 'edit'],

            // Reports & Logs
            'reports' => ['view', 'export'],
            'audit_logs' => ['view', 'export'],
            'logs' => ['view'],

            // Notifications
            'notifications' => ['view', 'create', 'edit', 'delete', 'export'],
        ];

        foreach ($additionalPermissions as $resource => $actions) {
            foreach ($actions as $action) {
                Permission::firstOrCreate([
                    'name' => "{$resource}.{$action}",
                    'guard_name' => 'web',
                ]);
                $count++;
            }
        }

        $this->command->info("✅ Created/verified {$count} permissions.");
    }
}