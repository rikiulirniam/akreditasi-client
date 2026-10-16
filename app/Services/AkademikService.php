<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class AkademikService
{
    protected string $baseUrl;

    protected ?string $token = null;

    public function __construct(?string $baseUrl = null, ?string $token = null)
    {
        $this->baseUrl = rtrim($baseUrl ?? (string) config('services.akademik.base_url', 'https://akademik.fayra.my.id/api'), '/');
        $this->token = $token;
    }

    public function withToken(?string $token = null): static
    {
        $clone = clone $this;
        $clone->token = $token ?? (string) session('access_token');

        return $clone;
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    protected function http(?string $token = null): PendingRequest
    {
        $resolvedToken = $token ?? $this->token ?? (string) session('access_token');

        $client = Http::acceptJson()
            ->asJson()
            ->timeout(20);

        if (! empty($resolvedToken)) {
            $client = $client->withToken($resolvedToken);
        }

        return $client;
    }

    protected function buildUrl(string $endpoint): string
    {
        $trimmed = ltrim($endpoint, '/');

        if (str_starts_with($trimmed, 'api/')) {
            $trimmed = substr($trimmed, 4);
        }

        return "{$this->baseUrl}/{$trimmed}";
    }

    /*
    |--------------------------------------------------------------------------
    | Generic HTTP Request Helpers
    |--------------------------------------------------------------------------
    */

    public function get(string $endpoint, array $query = [], ?string $token = null): Response
    {
        return $this->http($token)->get($this->buildUrl($endpoint), $query);
    }

    public function post(string $endpoint, array $data = [], ?string $token = null): Response
    {
        return $this->http($token)->post($this->buildUrl($endpoint), $data);
    }

    public function put(string $endpoint, array $data = [], ?string $token = null): Response
    {
        return $this->http($token)->put($this->buildUrl($endpoint), $data);
    }

    public function delete(string $endpoint, array $data = [], ?string $token = null): Response
    {
        return $this->http($token)->delete($this->buildUrl($endpoint), $data);
    }

    /*
    |==========================================================================
    | MODUL: USER MANAGEMENT (/api/users/*)
    |==========================================================================
    */

    public function getUserList(array $query = [], ?string $token = null): Response
    {
        return $this->get('users', $query, $token);
    }

    public function getUserById(int|string $userId, ?string $token = null): Response
    {
        return $this->get("users/{$userId}", [], $token);
    }

    public function createUser(array $userData, ?string $token = null): Response
    {
        return $this->post('users', $userData, $token);
    }

    public function updateUser(int|string $userId, array $userData, ?string $token = null): Response
    {
        return $this->put("users/{$userId}", $userData, $token);
    }

    public function deleteUser(int|string $userId, ?string $token = null): Response
    {
        return $this->delete("users/{$userId}", [], $token);
    }
    /*
    |==========================================================================
    | MODUL: ROLE MANAGEMENT (/api/roles/*)
    |==========================================================================
    */

    public function getRoleList(array $query = [], ?string $token = null): Response
    {
        return $this->get('roles', $query, $token);
    }

    public function getRoleById(int|string $roleId, ?string $token = null): Response
    {
        return $this->get("roles/{$roleId}", [], $token);
    }

    public function createRole(array $roleData, ?string $token = null): Response
    {
        if (! isset($roleData['guard_name'])) {
            $roleData['guard_name'] = 'api';
        }

        return $this->post('roles', $roleData, $token);
    }

    public function updateRole(int|string $roleId, array $roleData, ?string $token = null): Response
    {
        return $this->put("roles/{$roleId}", $roleData, $token);
    }

    public function deleteRole(int|string $roleId, ?string $token = null): Response
    {
        return $this->delete("roles/{$roleId}", [], $token);
    }

    /*
    |==========================================================================
    | MODUL: PERMISSIONS (/api/permissions/*)
    |==========================================================================
    */

    public function getPermissionList(array $query = [], ?string $token = null): Response
    {
        return $this->get('permissions', $query, $token);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getAllPermissions(?string $token = null, bool $fresh = false): array
    {
        $resolvedToken = $token ?? $this->token ?? (string) session('access_token');
        $cacheKey = 'akademik_all_permissions_'.md5($resolvedToken);

        if (! $fresh && cache()->has($cacheKey)) {
            $cached = cache()->get($cacheKey);
            if (! empty($cached) && is_array($cached)) {
                return $cached;
            }
        }

        $all = [];
        $page = 1;

        do {
            $response = $this->getPermissionList(['page' => $page], $resolvedToken);
            if (! $response->successful()) {
                if (cache()->has($cacheKey)) {
                    return cache()->get($cacheKey);
                }
                break;
            }

            $data = $response->json('data') ?? [];
            if (empty($data)) {
                break;
            }

            $all = array_merge($all, $data);

            $pagination = $response->json('pagination');
            $lastPage = $pagination['last_page'] ?? $page;
            $page++;
        } while ($page <= $lastPage);

        if (! empty($all)) {
            cache()->put($cacheKey, $all, now()->addMinutes(15));
        } elseif (cache()->has($cacheKey)) {
            return cache()->get($cacheKey);
        }

        return $all;
    }

    public function clearPermissionsCache(?string $token = null): void
    {
        $resolvedToken = $token ?? $this->token ?? (string) session('access_token');
        cache()->forget('akademik_all_permissions_'.md5($resolvedToken));
    }

    public function getPermissionById(int|string $permissionId, ?string $token = null): Response
    {
        return $this->get("permissions/{$permissionId}", [], $token);
    }

    public function createPermission(array $permissionData, ?string $token = null): Response
    {
        if (! isset($permissionData['guard_name'])) {
            $permissionData['guard_name'] = 'api';
        }

        return $this->post('permissions', $permissionData, $token);
    }

    public function updatePermission(int|string $permissionId, array $permissionData, ?string $token = null): Response
    {
        return $this->put("permissions/{$permissionId}", $permissionData, $token);
    }

    public function deletePermission(int|string $permissionId, ?string $token = null): Response
    {
        return $this->delete("permissions/{$permissionId}", [], $token);
    }

    /*
    |==========================================================================
    | MODUL: PERGURUAN TINGGI (/api/perguruan-tinggi/*)
    |==========================================================================
    */

    public function getPerguruanTinggiList(array $query = [], ?string $token = null): Response
    {
        return $this->get('perguruan-tinggi', $query, $token);
    }

    public function getPerguruanTinggiById(int|string $id, ?string $token = null): Response
    {
        return $this->get("perguruan-tinggi/{$id}", [], $token);
    }

    public function updatePerguruanTinggi(int|string $id, array $data, ?string $token = null): Response
    {
        return $this->put("perguruan-tinggi/{$id}", $data, $token);
    }

    /*
    |==========================================================================
    | MODUL: PRODI & UPPS (/api/prodi, /api/upps)
    |==========================================================================
    */

    public function getProdiList(array $query = [], ?string $token = null): Response
    {
        return $this->get('prodi', $query, $token);
    }

    public function getProdiById(int|string $id, ?string $token = null): Response
    {
        return $this->get("prodi/{$id}", [], $token);
    }

    public function createProdi(array $data, ?string $token = null): Response
    {
        return $this->post('prodi', $data, $token);
    }

    public function updateProdi(int|string $id, array $data, ?string $token = null): Response
    {
        return $this->put("prodi/{$id}", $data, $token);
    }

    public function deleteProdi(int|string $id, ?string $token = null): Response
    {
        return $this->delete("prodi/{$id}", [], $token);
    }

    public function getUppsList(array $query = [], ?string $token = null): Response
    {
        return $this->get('upps', $query, $token);
    }

    public function getUppsById(int|string $id, ?string $token = null): Response
    {
        return $this->get("upps/{$id}", [], $token);
    }

    public function createUpps(array $data, ?string $token = null): Response
    {
        return $this->post('upps', $data, $token);
    }

    public function updateUpps(int|string $id, array $data, ?string $token = null): Response
    {
        return $this->put("upps/{$id}", $data, $token);
    }

    public function deleteUpps(int|string $id, ?string $token = null): Response
    {
        return $this->delete("upps/{$id}", [], $token);
    }

    /*
    |==========================================================================
    | MODUL: AUTENTIKASI PENGGUNA (/api/auth/*)
    |==========================================================================
    */

    public function login(string|array $emailOrCredentials, ?string $password = null): Response
    {
        if (is_array($emailOrCredentials)) {
            $email = (string) ($emailOrCredentials['email'] ?? $emailOrCredentials['username'] ?? '');
            $password = (string) ($emailOrCredentials['password'] ?? $password ?? '');
        } else {
            $email = $emailOrCredentials;
            $password = (string) $password;
        }

        return $this->http()->post($this->buildUrl('auth/login'), [
            'email' => $email,
            'password' => $password,
        ]);
    }

    public function getAuthProfile(?string $token = null): Response
    {
        return $this->get('auth/me', [], $token);
    }

    public function logoutAuth(?string $token = null): Response
    {
        return $this->post('auth/logout', [], $token);
    }

    public function refreshAuthToken(?string $token = null): Response
    {
        return $this->post('auth/refresh', [], $token);
    }
}
