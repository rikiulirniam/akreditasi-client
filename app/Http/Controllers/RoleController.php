<?php

namespace App\Http\Controllers;

use App\Services\AkademikService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RoleController extends Controller
{
    public function __construct(
        protected AkademikService $akademikService,
    ) {}

    public function index(Request $request): View|RedirectResponse
    {
        $page = (int) $request->query('page', 1);
        $response = $this->akademikService->getRoleList(['page' => $page]);

        if ($response->status() === 401) {
            return redirect()->route('login')->with('toast', [
                'type' => 'warning',
                'title' => 'Sesi Berakhir',
                'message' => 'Sesi Anda telah kedaluwarsa. Silakan login kembali.',
            ]);
        }

        $roles = $response->json('data') ?? [];
        $pagination = $response->json('pagination') ?? [
            'total' => count($roles),
            'per_page' => 10,
            'current_page' => $page,
            'last_page' => 1,
        ];

        return view('roles.index', [
            'roles' => $roles,
            'pagination' => $pagination,
        ]);
    }

    public function create(): View|RedirectResponse
    {
        $permissions = $this->akademikService->getAllPermissions();
        if (empty($permissions)) {
            $permissions = $this->akademikService->getAllPermissions(null, true);
        }

        $groupedPermissions = $this->groupPermissions($permissions);

        return view('roles.create', [
            'groupedPermissions' => $groupedPermissions,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'guard_name' => ['nullable', 'string', 'max:255'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string'],
        ], [
            'name.required' => 'Nama peran wajib diisi.',
        ]);

        $payload = [
            'name' => strtoupper(trim($validated['name'])),
            'guard_name' => $validated['guard_name'] ?: 'api',
            'permissions' => $validated['permissions'] ?? [],
        ];

        $response = $this->akademikService->createRole($payload);
        $body = $response->json();

        if ($response->successful() && ($body['success'] ?? false)) {
            return redirect()->route('roles.index')->with('toast', [
                'type' => 'success',
                'title' => 'Berhasil',
                'message' => $body['message'] ?? 'Peran berhasil ditambahkan.',
            ]);
        }

        $errorMessage = $body['message'] ?? 'Gagal menambahkan peran.';

        return back()->withInput()->with('toast', [
            'type' => 'error',
            'title' => 'Gagal',
            'message' => $errorMessage,
        ]);
    }

    public function edit(int|string $id): View|RedirectResponse
    {
        $roleResponse = $this->akademikService->getRoleById($id);

        if ($roleResponse->status() === 401) {
            return redirect()->route('login')->with('toast', [
                'type' => 'warning',
                'title' => 'Sesi Berakhir',
                'message' => 'Sesi Anda telah kedaluwarsa. Silakan login kembali.',
            ]);
        }

        if (! $roleResponse->successful()) {
            return redirect()->route('roles.index')->with('toast', [
                'type' => 'error',
                'title' => 'Tidak Ditemukan',
                'message' => 'Data peran tidak ditemukan atau gagal dimuat.',
            ]);
        }

        $role = $roleResponse->json('data');
        $permissions = $this->akademikService->getAllPermissions();
        if (empty($permissions)) {
            $permissions = $this->akademikService->getAllPermissions(null, true);
        }

        $rolePermissionNames = collect($role['permissions'] ?? [])->pluck('name')->all();
        $groupedPermissions = $this->groupPermissions($permissions);

        return view('roles.edit', [
            'role' => $role,
            'rolePermissionNames' => $rolePermissionNames,
            'groupedPermissions' => $groupedPermissions,
        ]);
    }

    public function update(Request $request, int|string $id): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'guard_name' => ['nullable', 'string', 'max:255'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string'],
        ], [
            'name.required' => 'Nama peran wajib diisi.',
        ]);

        $payload = [
            'name' => strtoupper(trim($validated['name'])),
            'guard_name' => $validated['guard_name'] ?: 'api',
            'permissions' => $validated['permissions'] ?? [],
        ];

        $response = $this->akademikService->updateRole($id, $payload);
        $body = $response->json();

        if ($response->successful() && ($body['success'] ?? false)) {
            return redirect()->route('roles.index')->with('toast', [
                'type' => 'success',
                'title' => 'Berhasil',
                'message' => $body['message'] ?? 'Peran berhasil diperbarui.',
            ]);
        }

        $errorMessage = $body['message'] ?? 'Gagal memperbarui peran.';

        return back()->withInput()->with('toast', [
            'type' => 'error',
            'title' => 'Gagal',
            'message' => $errorMessage,
        ]);
    }

    public function destroy(int|string $id): RedirectResponse
    {
        $response = $this->akademikService->deleteRole($id);
        $body = $response->json();

        if ($response->successful() && ($body['success'] ?? false)) {
            return redirect()->route('roles.index')->with('toast', [
                'type' => 'success',
                'title' => 'Berhasil Dihapus',
                'message' => $body['message'] ?? 'Peran berhasil dihapus.',
            ]);
        }

        return redirect()->route('roles.index')->with('toast', [
            'type' => 'error',
            'title' => 'Gagal Menghapus',
            'message' => $body['message'] ?? 'Peran gagal dihapus.',
        ]);
    }

    /**
     * @param  array<int, array<string, mixed>>  $permissions
     * @return array<string, array<int, array<string, mixed>>>
     */
    protected function groupPermissions(array $permissions): array
    {
        $grouped = [];

        foreach ($permissions as $perm) {
            $name = $perm['name'] ?? '';
            $group = str_contains($name, '.') ? explode('.', $name)[0] : 'general';
            $grouped[$group][] = $perm;
        }

        ksort($grouped);

        return $grouped;
    }
}
