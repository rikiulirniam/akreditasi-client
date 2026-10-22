<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Registrasi Gate::before berbasis permissions JWT di session
        Gate::before(function (?User $user, string $ability) {
            $sessionUser = session('user', []);
            $rawRole = $sessionUser['role'] ?? ($sessionUser['role_name'] ?? ($sessionUser['roles'][0]['name'] ?? ''));
            $currentRole = strtoupper(trim((string) $rawRole));

            // Administrator / Superadmin memiliki bypass ke semua permission
            if (in_array($currentRole, ['ADMINISTRATOR', 'ADMIN', 'SUPERADMIN'], true)) {
                return true;
            }

            $userPermissions = (array) session('permissions', []);
            if (empty($userPermissions) && $currentRole !== '') {
                $userPermissions = match ($currentRole) {
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

            $userPermissions = array_map('strtolower', $userPermissions);
            $requiredNormalized = str_replace('-', '_', strtolower(trim($ability)));

            foreach ($userPermissions as $perm) {
                $permNormalized = str_replace('-', '_', $perm);
                if ($permNormalized === $requiredNormalized || $permNormalized === '*') {
                    return true;
                }
                if (str_ends_with($permNormalized, '.*')) {
                    $prefix = substr($permNormalized, 0, -1);
                    if (str_starts_with($requiredNormalized, $prefix)) {
                        return true;
                    }
                }
                if (str_starts_with($permNormalized, '*.')) {
                    $suffix = substr($permNormalized, 1);
                    if (str_ends_with($requiredNormalized, $suffix)) {
                        return true;
                    }
                }
            }

            return false;
        });

        // Blade directive: @haspermission('kurikulum.create')
        Blade::if('haspermission', function (string|array $permissions) {
            $perms = is_array($permissions) ? $permissions : explode(',', $permissions);
            foreach ($perms as $perm) {
                if (Gate::allows(trim($perm))) {
                    return true;
                }
            }

            return false;
        });

        // Blade directive: @hasrole('ADMINISTRATOR,KAPRODI')
        Blade::if('hasrole', function (string|array $roles) {
            $user = session('user', []);
            $rawRole = $user['role'] ?? ($user['role_name'] ?? ($user['roles'][0]['name'] ?? ''));
            $currentRole = strtoupper(trim((string) $rawRole));

            if (in_array($currentRole, ['ADMINISTRATOR', 'ADMIN', 'SUPERADMIN'], true)) {
                return true;
            }

            $checkRoles = is_array($roles) ? $roles : explode(',', $roles);
            $checkRoles = array_map(fn ($r) => strtoupper(trim($r)), $checkRoles);

            return in_array($currentRole, $checkRoles, true);
        });
    }
}
