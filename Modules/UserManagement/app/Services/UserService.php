<?php

namespace Modules\UserManagement\Services;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Modules\Master\Models\District;
use Modules\Master\Models\Division;
use Modules\Master\Models\Section;
use Modules\UserManagement\DataTables\UserDataTable;
use Modules\UserManagement\Repositories\UserRepository;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class UserService
{
    public function __construct(
        protected UserRepository $repository,
        protected UserDataTable $dataTable,
    ) {}


    public function table(Request $request): array
    {
        return $this->dataTable->toArray($request);
    }

    public function forCreate(): array
    {
        return [
            'user' => null,
            'roles' => Role::pluck('name'),
            'districts' => District::orderBy('name')->get(['id', 'name']),
            'divisions' => Division::orderBy('name')->get(['id', 'name']),
            'sections' => [],
        ];
    }

    public function forEdit(User $user): array
    {
        $user->load('roles', 'media');

        return [
            'user' => $user,
            'roles' => Role::pluck('name'),
            'districts' => District::orderBy('name')->get(['id', 'name']),
            'divisions' => $user->district_id
                ? Division::orderBy('name')->get(['id', 'name'])
                : [],
            'sections' => $user->division_id
                ? Section::where('division_id', $user->division_id)->orderBy('name')->get(['id', 'name'])
                : [],
        ];
    }

    public function store(array $data, ?UploadedFile $avatar): User
    {
        $user = $this->repository->create([
            'name' => $data['name'],
            'username' => $data['username'] ?? null,
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'status' => $data['status'],
            'bio' => $data['bio'] ?? null,
            'district_id' => $data['district_id'] ?? null,
            'division_id' => $data['division_id'] ?? null,
            'section_id' => $data['section_id'] ?? null,
            'password' => bcrypt($data['password']),
        ]);

        $user->syncRoles([$data['role']]);

        if ($avatar) {
            $user->addMedia($avatar)->toMediaCollection('avatar');
        }

        return $user;
    }

    public function update(User $user, array $data, ?UploadedFile $avatar, bool $removeAvatar = false): User
    {
        $this->repository->update($user, [
            'name' => $data['name'],
            'username' => $data['username'] ?? null,
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'status' => $data['status'],
            'bio' => $data['bio'] ?? null,
            'district_id' => $data['district_id'] ?? null,
            'division_id' => $data['division_id'] ?? null,
            'section_id' => $data['section_id'] ?? null,
            ...(! empty($data['password']) ? ['password' => bcrypt($data['password'])] : []),
        ]);

        $user->syncRoles([$data['role']]);

        if ($avatar) {
            $user->clearMediaCollection('avatar');
            $user->addMedia($avatar)->toMediaCollection('avatar');
        } elseif ($removeAvatar) {
            $user->clearMediaCollection('avatar');
        }

        return $user->refresh();
    }

    public function destroy(User $user): bool
    {
        return $this->repository->delete($user);
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
                'username' => $row['username'] ?? null,
                'email' => $row['email'],
                'phone' => $row['phone'] ?? null,
                'status' => filter_var($row['status'] ?? true, FILTER_VALIDATE_BOOLEAN),
                // Imported users get a random password + should go through "forgot password".
                'password' => bcrypt(str()->random(16)),
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
            'username' => 'username',
            'email' => 'email',
            'phone' => 'phone',
            'status' => 'status',
        ];
    }

    protected function importRules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
        ];
    }
}

