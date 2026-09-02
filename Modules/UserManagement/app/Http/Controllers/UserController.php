<?php

namespace Modules\UserManagement\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\UserManagement\Http\Requests\StoreUserRequest;
use Modules\UserManagement\Http\Requests\UpdateUserRequest;
use Modules\UserManagement\Services\UserService;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class UserController extends Controller
{
    public function __construct(protected UserService $userService) {}

    public function index(Request $request):Response
    {
        return Inertia::render('UserManagement::Users/Index', [
            ...$this->userService->table($request),
            'roles' => Role::pluck('name'),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('UserManagement::Users/Form', $this->userService->forCreate());
    }

    public function edit(User $user): Response
    {
        return Inertia::render('UserManagement::Users/Form', $this->userService->forEdit($user));
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $this->userService->store($request->validated(), $request->file('avatar'));

        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $this->userService->update(
            $user,
            $request->validated(),
            $request->file('avatar'),
            $request->boolean('remove_avatar'),
        );

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->userService->destroy($user);

        return back()->with('success', 'User deleted successfully.');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:users,id',
        ]);

        $this->userService->bulkDestroy($request->input('ids'));

        return back()->with('success', 'Selected users deleted successfully.');
    }

    public function export(Request $request): BinaryFileResponse
    {
        return $this->userService->export($request);
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate(['file' => 'required|file|mimes:xlsx,csv,xls']);

        $result = $this->userService->import($request->file('file'));

        return back()
            ->with('success', "Imported {$result['created']} users.")
            ->with('import_failures', $result['failures']);
    }
}
