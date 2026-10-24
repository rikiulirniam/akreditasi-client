<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        $userData = $request->session()->get('user', []);
        $rawRole = $userData['role'] ?? ($userData['role_name'] ?? ($userData['roles'][0]['name'] ?? ''));
        $currentRole = strtoupper(trim((string) $rawRole));

        // Administrator / Superadmin selalu memiliki bypass ke seluruh permission
        if (in_array($currentRole, ['ADMINISTRATOR', 'ADMIN', 'SUPERADMIN'], true)) {
            return $next($request);
        }

        // Ambil permissions dari session pengguna
        $userPermissions = (array) $request->session()->get('permissions', []);

        // Fallback default permissions berdasarkan role jika session permissions belum terisi
        if (empty($userPermissions) && $currentRole !== '') {
            $userPermissions = $this->getDefaultPermissionsForRole($currentRole);
        }

        $userPermissions = array_map('strtolower', $userPermissions);

        // Tentukan permissions yang dibutuhkan
        $requiredPermissions = [];
        foreach ($permissions as $permGroup) {
            foreach (explode(',', $permGroup) as $p) {
                $trimmed = strtolower(trim($p));
                if ($trimmed === '') {
                    continue;
                }

                // Jika parameter berupa nama resource tanpa action (misal "kurikulum" atau "mata_kuliah")
                if (! str_contains($trimmed, '.')) {
                    $action = $this->resolveActionFromRoute($request);
                    $requiredPermissions[] = "{$trimmed}.{$action}";
                } else {
                    $requiredPermissions[] = $trimmed;
                }
            }
        }

        foreach ($requiredPermissions as $required) {
            if ($this->hasPermission($userPermissions, $required)) {
                return $next($request);
            }
        }

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki hak akses untuk tindakan ini.',
            ], 403);
        }

        abort(403, 'Anda tidak memiliki hak akses untuk tindakan ini.');
    }

    /**
     * Petakan method resource route ke action permission API (.read, .create, .update, .delete)
     */
    protected function resolveActionFromRoute(Request $request): string
    {
        $route = $request->route();
        $actionMethod = $route ? $route->getActionMethod() : '';

        return match ($actionMethod) {
            'create', 'store' => 'create',
            'edit', 'update' => 'update',
            'destroy' => 'delete',
            default => 'read',
        };
    }

    /**
     * Cek kecocokan permission dengan dukungan wildcard dan normalisasi dash/underscore
     */
    protected function hasPermission(array $userPermissions, string $required): bool
    {
        if (in_array('*', $userPermissions, true)) {
            return true;
        }

        $requiredNormalized = str_replace('-', '_', $required);

        foreach ($userPermissions as $perm) {
            $permNormalized = str_replace('-', '_', $perm);
            if ($permNormalized === $requiredNormalized) {
                return true;
            }

            // Wildcard per module: misal "kurikulum.*"
            if (str_ends_with($permNormalized, '.*')) {
                $prefix = substr($permNormalized, 0, -1);
                if (str_starts_with($requiredNormalized, $prefix)) {
                    return true;
                }
            }

            // Wildcard per action: misal "*.read"
            if (str_starts_with($permNormalized, '*.')) {
                $suffix = substr($permNormalized, 1);
                if (str_ends_with($requiredNormalized, $suffix)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Pemetaan fallback jika token di session tidak menyertakan array permissions
     */
    protected function getDefaultPermissionsForRole(string $role): array
    {
        return match ($role) {
            'ADMINISTRATOR', 'ADMIN' => ['*'],
            'KAPRODI' => [
                '*.read',
                'kurikulum.create', 'kurikulum.update', 'kurikulum.delete',
                'mata_kuliah.create', 'mata_kuliah.update', 'mata_kuliah.delete',
                'cpl.create', 'cpl.update', 'cpl.delete',
                'mata_kuliah_cpl.create', 'mata_kuliah_cpl.update', 'mata_kuliah_cpl.delete',
                'enrollment_mata_kuliah.update',
            ],
            'OPERATOR' => [
                '*.read',
                'kelas.create', 'kelas.update', 'kelas.delete',
                'mahasiswa.create', 'mahasiswa.update', 'mahasiswa.delete',
                'prestasi_mahasiswa.create', 'prestasi_mahasiswa.update', 'prestasi_mahasiswa.delete',
                'evidence_prestasi_mahasiswa.create', 'evidence_prestasi_mahasiswa.update', 'evidence_prestasi_mahasiswa.delete',
                'jenis_prestasi.create', 'jenis_prestasi.update', 'jenis_prestasi.delete',
                'angkatan.create', 'angkatan.update', 'angkatan.delete',
                'angkatan_kelas.create', 'angkatan_kelas.update', 'angkatan_kelas.delete',
                'tahun_akademik.create', 'tahun_akademik.update', 'tahun_akademik.delete',
            ],
            'DOSEN' => [
                '*.read',
                'nilai_mahasiswa.create', 'nilai_mahasiswa.update',
                'enrollment_mata_kuliah.update',
            ],
            default => ['*.read'],
        };
    }
}
