<?php

namespace App\Http\Controllers;

use App\Services\AkademikService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PermissionController extends Controller
{
    public function __construct(
        protected AkademikService $akademikService,
    ) {}

    public function index(Request $request): View|RedirectResponse
    {
        $allPermissions = $this->akademikService->getAllPermissions();
        $grouped = [];

        foreach ($allPermissions as $perm) {
            $name = $perm['name'] ?? '';
            $parts = explode('.', $name, 2);
            $module = count($parts) === 2 ? strtolower(trim($parts[0])) : 'general';
            $action = count($parts) === 2 ? strtolower(trim($parts[1])) : $name;

            if (! isset($grouped[$module])) {
                $grouped[$module] = [
                    'module' => $module,
                    'guard_name' => $perm['guard_name'] ?? 'api',
                    'actions' => [],
                    'permissions' => [],
                ];
            }

            if (! in_array($action, $grouped[$module]['actions'], true)) {
                $grouped[$module]['actions'][] = $action;
            }

            $grouped[$module]['permissions'][] = [
                'id' => $perm['id'],
                'name' => $name,
                'action' => $action,
                'guard_name' => $perm['guard_name'] ?? 'api',
            ];
        }

        ksort($grouped);

        $search = strtolower(trim((string) $request->query('search', '')));
        if ($search !== '') {
            $grouped = array_filter($grouped, function ($item) use ($search) {
                if (str_contains($item['module'], $search)) {
                    return true;
                }
                foreach ($item['actions'] as $act) {
                    if (str_contains($act, $search)) {
                        return true;
                    }
                }

                return false;
            });
        }

        $page = (int) $request->query('page', 1);
        $perPage = 10;
        $total = count($grouped);
        $offset = ($page - 1) * $perPage;
        $modules = array_slice(array_values($grouped), $offset, $perPage);

        $pagination = [
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => max(1, (int) ceil($total / $perPage)),
            'from' => $total > 0 ? $offset + 1 : 0,
            'to' => min($offset + $perPage, $total),
        ];

        return view('permissions.index', [
            'modules' => $modules,
            'pagination' => $pagination,
            'search' => $search,
        ]);
    }

    public function create(): RedirectResponse
    {
        return redirect()->route('permissions.index');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'module' => ['nullable', 'string', 'max:100'],
            'actions' => ['nullable', 'array'],
            'actions.*' => ['string', 'max:50'],
            'name' => ['nullable', 'string', 'max:255'],
            'guard_name' => ['nullable', 'string', 'max:100'],
        ]);

        $guardName = $validated['guard_name'] ?: 'api';

        // Bulk module creation
        if (! empty($validated['module']) && ! empty($validated['actions'])) {
            $module = strtolower(trim($validated['module']));
            $actions = array_unique(array_filter(array_map('strtolower', array_map('trim', $validated['actions']))));

            if (empty($actions)) {
                return back()->withInput()->with('toast', [
                    'type' => 'error',
                    'title' => 'Gagal',
                    'message' => 'Pilih atau tambahkan minimal satu aksi hak akses.',
                ]);
            }

            $createdCount = 0;
            $failed = [];

            foreach ($actions as $action) {
                $permName = "{$module}.{$action}";
                $response = $this->akademikService->clearPermissionsCache();
                $this->akademikService->createPermission([
                    'name' => $permName,
                    'guard_name' => $guardName,
                ]);

                if ($response->successful()) {
                    $createdCount++;
                } else {
                    $failed[] = $permName;
                }
            }

            if ($createdCount > 0) {
                $msg = "{$createdCount} hak akses untuk modul {$module} berhasil dibuat.";
                if (! empty($failed)) {
                    $msg .= ' ('.count($failed).' aksi dilewati atau sudah ada).';
                }

                return redirect()->route('permissions.index')->with('toast', [
                    'type' => 'success',
                    'title' => 'Berhasil',
                    'message' => $msg,
                ]);
            }

            return back()->withInput()->with('toast', [
                'type' => 'error',
                'title' => 'Gagal Menambahkan',
                'message' => 'Semua aksi hak akses gagal dibuat atau sudah terdaftar.',
            ]);
        }

        // Single permission fallback
        if (! empty($validated['name'])) {
            $payload = [
                'name' => strtolower(trim($validated['name'])),
                'guard_name' => $guardName,
            ];

            $response = $this->akademikService->clearPermissionsCache();
            $this->akademikService->createPermission($payload);
            $body = $response->json();

            if ($response->successful() && ($body['success'] ?? false)) {
                return redirect()->route('permissions.index')->with('toast', [
                    'type' => 'success',
                    'title' => 'Berhasil',
                    'message' => $body['message'] ?? 'Hak akses berhasil ditambahkan.',
                ]);
            }
        }

        return back()->withInput()->with('toast', [
            'type' => 'error',
            'title' => 'Gagal',
            'message' => 'Silakan lengkapi nama modul dan pilih minimal satu aksi.',
        ]);
    }

    public function edit(int|string $id): RedirectResponse
    {
        return redirect()->route('permissions.index');
    }

    public function update(Request $request, int|string $id): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'guard_name' => ['nullable', 'string', 'max:255'],
        ], [
            'name.required' => 'Nama hak akses wajib diisi.',
        ]);

        $payload = [
            'name' => strtolower(trim($validated['name'])),
            'guard_name' => $validated['guard_name'] ?: 'api',
        ];

        $response = $this->akademikService->clearPermissionsCache();
        $this->akademikService->updatePermission($id, $payload);
        $body = $response->json();

        if ($response->successful() && ($body['success'] ?? false)) {
            return redirect()->route('permissions.index')->with('toast', [
                'type' => 'success',
                'title' => 'Berhasil',
                'message' => $body['message'] ?? 'Hak akses berhasil diperbarui.',
            ]);
        }

        $errorMessage = $body['message'] ?? 'Gagal memperbarui hak akses.';

        return back()->withInput()->with('toast', [
            'type' => 'error',
            'title' => 'Gagal',
            'message' => $errorMessage,
        ]);
    }

    public function updateModule(Request $request, string $module): RedirectResponse
    {
        $validated = $request->validate([
            'module' => ['nullable', 'string', 'max:100'],
            'new_module' => ['nullable', 'string', 'max:100'],
            'guard_name' => ['nullable', 'string', 'max:100'],
            'actions' => ['required', 'array', 'min:1'],
            'actions.*' => ['string', 'max:50'],
        ], [
            'new_module.required' => 'Nama modul wajib diisi.',
            'actions.required' => 'Pilih minimal satu aksi hak akses.',
            'actions.min' => 'Pilih minimal satu aksi hak akses.',
        ]);

        $oldModule = strtolower(trim($module));
        $newModule = strtolower(trim($validated['new_module'] ?? $validated['module'] ?? $oldModule));
        if (empty($newModule)) {
            $newModule = $oldModule;
        }
        $guardName = $validated['guard_name'] ?: 'api';
        $targetActions = array_unique(array_filter(array_map('strtolower', array_map('trim', $validated['actions']))));

        $allPermissions = $this->akademikService->getAllPermissions();

        $existing = [];
        foreach ($allPermissions as $p) {
            $name = $p['name'] ?? '';
            $parts = explode('.', $name, 2);
            $mod = count($parts) === 2 ? strtolower(trim($parts[0])) : 'general';
            $act = count($parts) === 2 ? strtolower(trim($parts[1])) : $name;

            if ($mod === $oldModule) {
                $existing[$act] = $p;
            }
        }

        // 1. Existing actions still kept or removed
        foreach ($existing as $act => $perm) {
            if (in_array($act, $targetActions, true)) {
                if ($newModule !== $oldModule) {
                    $this->akademikService->clearPermissionsCache();
                    $this->akademikService->updatePermission($perm['id'], [
                        'name' => "{$newModule}.{$act}",
                        'guard_name' => $guardName,
                    ]);
                }
            } else {
                $this->akademikService->clearPermissionsCache();
                $this->akademikService->deletePermission($perm['id']);
            }
        }

        // 2. Newly added actions
        foreach ($targetActions as $act) {
            if (! isset($existing[$act])) {
                $this->akademikService->clearPermissionsCache();
                $this->akademikService->createPermission([
                    'name' => "{$newModule}.{$act}",
                    'guard_name' => $guardName,
                ]);
            }
        }

        return redirect()->route('permissions.index')->with('toast', [
            'type' => 'success',
            'title' => 'Modul Diperbarui',
            'message' => "Hak akses modul {$newModule} berhasil diperbarui.",
        ]);
    }

    public function destroyModule(string $module): RedirectResponse
    {
        $targetModule = strtolower(trim($module));
        $allPermissions = $this->akademikService->getAllPermissions();

        $deletedCount = 0;
        foreach ($allPermissions as $p) {
            $name = $p['name'] ?? '';
            $parts = explode('.', $name, 2);
            $mod = count($parts) === 2 ? strtolower(trim($parts[0])) : 'general';

            if ($mod === $targetModule) {
                $delResp = $this->akademikService->clearPermissionsCache();
                $this->akademikService->deletePermission($p['id']);
                if ($delResp->successful()) {
                    $deletedCount++;
                }
            }
        }

        return redirect()->route('permissions.index')->with('toast', [
            'type' => 'success',
            'title' => 'Modul Dihapus',
            'message' => "Semua hak akses untuk modul {$targetModule} ({$deletedCount} hak akses) berhasil dihapus.",
        ]);
    }

    public function destroy(int|string $id): RedirectResponse
    {
        $response = $this->akademikService->clearPermissionsCache();
        $this->akademikService->deletePermission($id);
        $body = $response->json();

        if ($response->successful() && ($body['success'] ?? false)) {
            return redirect()->route('permissions.index')->with('toast', [
                'type' => 'success',
                'title' => 'Berhasil Dihapus',
                'message' => $body['message'] ?? 'Hak akses berhasil dihapus.',
            ]);
        }

        return redirect()->route('permissions.index')->with('toast', [
            'type' => 'error',
            'title' => 'Gagal Menghapus',
            'message' => $body['message'] ?? 'Hak akses gagal dihapus.',
        ]);
    }
}
