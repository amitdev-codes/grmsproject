<?php

namespace Modules\UserManagement\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\UserManagement\Http\Requests\StoreRoleRequest;
use Modules\UserManagement\Http\Requests\UpdateRoleRequest;
use Modules\UserManagement\Services\RoleService;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class RoleController extends Controller
{
    public function __construct(protected RoleService $roleService) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        return Inertia::render('UserManagement::Roles/Index', [
            ...$this->roleService->table($request),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('UserManagement::Roles/RoleForm', $this->roleService->forCreate());
    }

    public function edit(Role $role): Response
    {
        return Inertia::render('UserManagement::Roles/RoleForm', $this->roleService->forEdit($role));
    }

    public function store(StoreRoleRequest $request): RedirectResponse
    {
        $this->roleService->store($request->validated());

        return redirect()->route('roles.index')->with('success', 'Role created successfully.');
    }

    public function update(UpdateRoleRequest $request, Role $role): RedirectResponse
    {
        $this->roleService->update(
            $role,
            $request->validated(),
        );

        return redirect()->route('roles.index')->with('success', 'Role updated successfully.');
    }

    public function destroy(Role $role): RedirectResponse
    {
        $this->roleService->destroy($role);

        return back()->with('success', 'Role deleted successfully.');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:roles,id',
        ]);

        $this->roleService->bulkDestroy($request->input('ids'));

        return back()->with('success', 'Selected roles deleted successfully.');
    }

    public function export(Request $request): BinaryFileResponse
    {
        return $this->roleService->export($request);
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate(['file' => 'required|file|mimes:xlsx,csv,xls']);

        $result = $this->roleService->import($request->file('file'));

        return back()
            ->with('success', "Imported {$result['created']} roles.")
            ->with('import_failures', $result['failures']);
    }
}
