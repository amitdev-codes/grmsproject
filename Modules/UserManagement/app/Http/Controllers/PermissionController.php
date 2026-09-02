<?php

namespace Modules\UserManagement\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\UserManagement\Http\Requests\StorePermissionRequest;
use Modules\UserManagement\Http\Requests\UpdatePermissionRequest;
use Modules\UserManagement\Services\PermissionService;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PermissionController extends Controller
{
    public function __construct(protected PermissionService $permissionService)
    {

    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request):Response
    {
        return Inertia::render('UserManagement::Permissions/Index', [
            ...$this->permissionService->table($request),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('UserManagement::Permissions/Form', $this->permissionService->forCreate());
    }

    public function edit(Permission $permission): Response
    {
        return Inertia::render('PermissionManagement::Permissions/Form', $this->permissionService->forEdit($permission));
    }

    public function store(StorePermissionRequest $request): RedirectResponse
    {
        $this->permissionService->store($request->validated(), $request->file('avatar'));

        return redirect()->route('permissions.index')->with('success', 'Permission created successfully.');
    }

    public function update(UpdatePermissionRequest $request, Permission $permission): RedirectResponse
    {
        $this->permissionService->update(
            $permission,
            $request->validated()
        );

        return redirect()->route('permissions.index')->with('success', 'Permission updated successfully.');
    }

    public function destroy(Permission $permission): RedirectResponse
    {
        $this->permissionService->destroy($permission);

        return back()->with('success', 'Permission deleted successfully.');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:permissions,id',
        ]);

        $this->permissionService->bulkDestroy($request->input('ids'));

        return back()->with('success', 'Selected permissions deleted successfully.');
    }

    public function export(Request $request): BinaryFileResponse
    {
        return $this->permissionService->export($request);
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate(['file' => 'required|file|mimes:xlsx,csv,xls']);

        $result = $this->permissionService->import($request->file('file'));

        return back()
            ->with('success', "Imported {$result['created']} permissions.")
            ->with('import_failures', $result['failures']);
    }
}
