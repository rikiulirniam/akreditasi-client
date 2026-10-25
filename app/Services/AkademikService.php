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
        return $this->sendRequest('get', $endpoint, $query, $token);
    }

    public function post(string $endpoint, array $data = [], ?string $token = null): Response
    {
        return $this->sendRequest('post', $endpoint, $data, $token);
    }

    public function put(string $endpoint, array $data = [], ?string $token = null): Response
    {
        return $this->sendRequest('put', $endpoint, $data, $token);
    }

    public function patch(string $endpoint, array $data = [], ?string $token = null): Response
    {
        return $this->sendRequest('patch', $endpoint, $data, $token);
    }

    public function delete(string $endpoint, array $data = [], ?string $token = null): Response
    {
        return $this->sendRequest('delete', $endpoint, $data, $token);
    }

    protected function sendRequest(string $method, string $endpoint, array $params = [], ?string $token = null): Response
    {
        $resolvedToken = $token ?? $this->token ?? (string) session('access_token');
        $url = $this->buildUrl($endpoint);

        $client = $this->http($resolvedToken);
        $response = match (strtolower($method)) {
            'post' => $client->post($url, $params),
            'put' => $client->put($url, $params),
            'patch' => $client->patch($url, $params),
            'delete' => $client->delete($url, $params),
            default => $client->get($url, $params),
        };

        // Jika respons 401 dan bukan endpoint auth, coba auto-refresh token 1x
        $isAuthEndpoint = str_starts_with(ltrim($endpoint, '/'), 'auth/');
        if ($response->status() === 401 && ! $isAuthEndpoint && ! empty($resolvedToken)) {
            try {
                $refreshResponse = $this->refreshAuthToken($resolvedToken);
                $body = $refreshResponse->json();

                if ($refreshResponse->successful() && ($body['success'] ?? false) === true) {
                    $authData = $body['data']['authorization'] ?? [];
                    $newAccessToken = $authData['access_token'] ?? null;

                    if ($newAccessToken) {
                        if (session()->isStarted()) {
                            $expiresAt = null;
                            if (str_contains($newAccessToken, '.')) {
                                $parts = explode('.', $newAccessToken);
                                if (isset($parts[1])) {
                                    $jwtPayload = json_decode(base64_decode(strtr($parts[1], '-_', '+/')), true);
                                    if (is_array($jwtPayload) && isset($jwtPayload['exp'])) {
                                        $expiresAt = (int) $jwtPayload['exp'];
                                    }
                                }
                            }
                            if (! $expiresAt && isset($authData['expires_in'])) {
                                $expiresAt = time() + (int) $authData['expires_in'];
                            }

                            session()->put([
                                'access_token' => $newAccessToken,
                                'token_type' => $authData['token_type'] ?? 'Bearer',
                                'token_expires_at' => $expiresAt,
                            ]);
                        }

                        // Retry request dengan token baru
                        $retryClient = $this->http($newAccessToken);

                        return match (strtolower($method)) {
                            'post' => $retryClient->post($url, $params),
                            'put' => $retryClient->put($url, $params),
                            'patch' => $retryClient->patch($url, $params),
                            'delete' => $retryClient->delete($url, $params),
                            default => $retryClient->get($url, $params),
                        };
                    }
                }
            } catch (\Throwable $e) {
                // Abaikan kesalahan refresh dan kembalikan respons 401 asli
            }
        }

        return $response;
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

    public function getUppsDetail(int|string $id, ?string $token = null): Response
    {
        return $this->getUppsById($id, $token);
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

    public function loginWithGoogle(array $googleData): Response
    {
        return $this->http()->post($this->buildUrl('auth/google'), [
            'google_id' => (string) ($googleData['google_id'] ?? $googleData['id'] ?? ''),
            'email' => (string) ($googleData['email'] ?? ''),
            'name' => (string) ($googleData['name'] ?? ''),
            'avatar' => isset($googleData['avatar']) ? (string) $googleData['avatar'] : null,
        ]);
    }
    /*
        |==========================================================================
        | MODUL: KEMAHASISWAAN - ANGKATAN (/api/angkatan/*)
        |==========================================================================
        */

    public function getAngkatanList(array $query = [], ?string $token = null): Response
    {
        return $this->get('angkatan', $query, $token);
    }

    public function getAngkatanById(int|string $id, ?string $token = null): Response
    {
        return $this->get("angkatan/{$id}", [], $token);
    }

    public function createAngkatan(array $data, ?string $token = null): Response
    {
        return $this->post('angkatan', $data, $token);
    }

    public function updateAngkatan(int|string $id, array $data, ?string $token = null): Response
    {
        return $this->put("angkatan/{$id}", $data, $token);
    }

    public function deleteAngkatan(int|string $id, ?string $token = null): Response
    {
        return $this->delete("angkatan/{$id}", [], $token);
    }

    /*
    |==========================================================================
    | MODUL: KEMAHASISWAAN - KELAS (/api/kelas/*)
    |==========================================================================
    */

    public function getKelasList(array $query = [], ?string $token = null): Response
    {
        return $this->get('kelas', $query, $token);
    }

    public function getKelasById(int|string $id, ?string $token = null): Response
    {
        return $this->get("kelas/{$id}", [], $token);
    }

    public function createKelas(array $data, ?string $token = null): Response
    {
        return $this->post('kelas', $data, $token);
    }

    public function updateKelas(int|string $id, array $data, ?string $token = null): Response
    {
        return $this->put("kelas/{$id}", $data, $token);
    }

    public function deleteKelas(int|string $id, ?string $token = null): Response
    {
        return $this->delete("kelas/{$id}", [], $token);
    }

    public function getMahasiswaByKelasId(int|string $kelasId, array $query = [], ?string $token = null): Response
    {
        return $this->get("kelas/{$kelasId}/mahasiswa", $query, $token);
    }

    public function getMataKuliahByKelasId(int|string $kelasId, array $query = [], ?string $token = null): Response
    {
        return $this->get("kelas/{$kelasId}/mata-kuliah", $query, $token);
    }
    /*
        |==========================================================================
        | MODUL: PERGURUAN TINGGI - TAHUN AKADEMIK (/api/tahun-akademik/*)
        |==========================================================================
        */

    public function getTahunAkademikList(array $query = [], ?string $token = null): Response
    {
        return $this->get('tahun-akademik', $query, $token);
    }

    public function getTahunAkademikById(int|string $id, ?string $token = null): Response
    {
        return $this->get("tahun-akademik/{$id}", [], $token);
    }

    public function createTahunAkademik(array $data, ?string $token = null): Response
    {
        return $this->post('tahun-akademik', $data, $token);
    }

    public function updateTahunAkademik(int|string $id, array $data, ?string $token = null): Response
    {
        return $this->put("tahun-akademik/{$id}", $data, $token);
    }

    public function deleteTahunAkademik(int|string $id, ?string $token = null): Response
    {
        return $this->delete("tahun-akademik/{$id}", [], $token);
    }
    /*
        |==========================================================================
        | MODUL: KEMAHASISWAAN - ANGKATAN KELAS (/api/angkatan-kelas/*)
        |==========================================================================
        */

    public function getAngkatanKelasList(array $query = [], ?string $token = null): Response
    {
        return $this->get('angkatan-kelas', $query, $token);
    }

    public function getAngkatanKelasById(int|string $id, ?string $token = null): Response
    {
        return $this->get("angkatan-kelas/{$id}", [], $token);
    }

    public function createAngkatanKelas(array $data, ?string $token = null): Response
    {
        return $this->post('angkatan-kelas', $data, $token);
    }

    public function updateAngkatanKelas(int|string $id, array $data, ?string $token = null): Response
    {
        return $this->put("angkatan-kelas/{$id}", $data, $token);
    }

    public function deleteAngkatanKelas(int|string $id, ?string $token = null): Response
    {
        return $this->delete("angkatan-kelas/{$id}", [], $token);
    }

    /*
    |==========================================================================
    | MODUL: KEMAHASISWAAN - MAHASISWA (/api/mahasiswa/*)
    |==========================================================================
    */

    public function getMahasiswaList(array $query = [], ?string $token = null): Response
    {
        return $this->get('mahasiswa', $query, $token);
    }

    public function getMahasiswaById(int|string $id, ?string $token = null): Response
    {
        return $this->get("mahasiswa/{$id}", [], $token);
    }

    public function createMahasiswa(array $data, ?string $token = null): Response
    {
        return $this->post('mahasiswa', $data, $token);
    }

    public function updateMahasiswa(int|string $id, array $data, ?string $token = null): Response
    {
        return $this->put("mahasiswa/{$id}", $data, $token);
    }

    public function deleteMahasiswa(int|string $id, ?string $token = null): Response
    {
        return $this->delete("mahasiswa/{$id}", [], $token);
    }

    public function getJenisPrestasiList(array $query = [], ?string $token = null): Response
    {
        return $this->get('jenis-prestasi', $query, $token);
    }

    public function getPrestasiMahasiswaList(array $query = [], ?string $token = null): Response
    {
        return $this->get('prestasi-mahasiswa', $query, $token);
    }

    public function getPrestasiMahasiswaById(int|string $id, ?string $token = null): Response
    {
        return $this->get("prestasi-mahasiswa/{$id}", [], $token);
    }

    public function createPrestasiMahasiswa(array $data, ?string $token = null): Response
    {
        return $this->post('prestasi-mahasiswa', $data, $token);
    }

    public function updatePrestasiMahasiswa(int|string $id, array $data, ?string $token = null): Response
    {
        return $this->put("prestasi-mahasiswa/{$id}", $data, $token);
    }

    public function deletePrestasiMahasiswa(int|string $id, ?string $token = null): Response
    {
        return $this->delete("prestasi-mahasiswa/{$id}", [], $token);
    }

    public function getEvidencePrestasiMahasiswaList(array $query = [], ?string $token = null): Response
    {
        return $this->get('evidence-prestasi-mahasiswa', $query, $token);
    }

    public function getEvidencePrestasiMahasiswaById(int|string $id, ?string $token = null): Response
    {
        return $this->get("evidence-prestasi-mahasiswa/{$id}", [], $token);
    }

    public function createEvidencePrestasiMahasiswa(array $data, ?string $token = null): Response
    {
        return $this->post('evidence-prestasi-mahasiswa', $data, $token);
    }

    public function updateEvidencePrestasiMahasiswa(int|string $id, array $data, ?string $token = null): Response
    {
        return $this->put("evidence-prestasi-mahasiswa/{$id}", $data, $token);
    }

    public function deleteEvidencePrestasiMahasiswa(int|string $id, ?string $token = null): Response
    {
        return $this->delete("evidence-prestasi-mahasiswa/{$id}", [], $token);
    }

    public function getJenisPrestasiById(int|string $id, ?string $token = null): Response
    {
        return $this->get("jenis-prestasi/{$id}", [], $token);
    }

    public function createJenisPrestasi(array $data, ?string $token = null): Response
    {
        return $this->post('jenis-prestasi', $data, $token);
    }

    public function updateJenisPrestasi(int|string $id, array $data, ?string $token = null): Response
    {
        return $this->put("jenis-prestasi/{$id}", $data, $token);
    }

    public function deleteJenisPrestasi(int|string $id, ?string $token = null): Response
    {
        return $this->delete("jenis-prestasi/{$id}", [], $token);
    }

    /*
    |==========================================================================
    | MODUL: KURIKULUM (/api/kurikulum/*)
    |==========================================================================
    */

    public function getKurikulumList(array $query = [], ?string $token = null): Response
    {
        return $this->get('kurikulum', $query, $token);
    }

    public function getKurikulumById(int|string $id, ?string $token = null): Response
    {
        return $this->get("kurikulum/{$id}", [], $token);
    }

    public function createKurikulum(array $data, ?string $token = null): Response
    {
        return $this->post('kurikulum', $data, $token);
    }

    public function updateKurikulum(int|string $id, array $data, ?string $token = null): Response
    {
        return $this->put("kurikulum/{$id}", $data, $token);
    }

    public function deleteKurikulum(int|string $id, ?string $token = null): Response
    {
        return $this->delete("kurikulum/{$id}", [], $token);
    }

    public function getMataKuliahByKurikulumId(int|string $kurikulumId, array $query = [], ?string $token = null): Response
    {
        return $this->get("kurikulum/{$kurikulumId}/mata-kuliah", $query, $token);
    }

    /*
    |==========================================================================
    | MODUL: CPL (/api/cpl/*)
    |==========================================================================
    */

    public function getCplList(array $query = [], ?string $token = null): Response
    {
        return $this->get('cpl', $query, $token);
    }

    public function getCplById(int|string $id, ?string $token = null): Response
    {
        return $this->get("cpl/{$id}", [], $token);
    }

    public function createCpl(array $data, ?string $token = null): Response
    {
        return $this->post('cpl', $data, $token);
    }

    public function updateCpl(int|string $id, array $data, ?string $token = null): Response
    {
        return $this->put("cpl/{$id}", $data, $token);
    }

    public function deleteCpl(int|string $id, ?string $token = null): Response
    {
        return $this->delete("cpl/{$id}", [], $token);
    }

    /*
    |==========================================================================
    | MODUL: MATA KULIAH (/api/mata-kuliah/*)
    |==========================================================================
    */

    public function getMataKuliahList(array $query = [], ?string $token = null): Response
    {
        return $this->get('mata-kuliah', $query, $token);
    }

    public function getMataKuliahById(int|string $id, ?string $token = null): Response
    {
        return $this->get("mata-kuliah/{$id}", [], $token);
    }

    public function getCplByMataKuliahId(int|string $mataKuliahId, ?string $token = null): Response
    {
        return $this->get("mata-kuliah/{$mataKuliahId}/cpl", [], $token);
    }

    public function syncMataKuliahCpl(int|string $mataKuliahId, array $cplIds, ?string $token = null): Response
    {
        return $this->post("mata-kuliah/{$mataKuliahId}/cpl/sync", ['cpl_ids' => $cplIds], $token);
    }

    public function createMataKuliah(array $data, ?string $token = null): Response
    {
        return $this->post('mata-kuliah', $data, $token);
    }

    public function updateMataKuliah(int|string $id, array $data, ?string $token = null): Response
    {
        return $this->put("mata-kuliah/{$id}", $data, $token);
    }

    public function deleteMataKuliah(int|string $id, ?string $token = null): Response
    {
        return $this->delete("mata-kuliah/{$id}", [], $token);
    }

    /*
    |==========================================================================
    | MODUL: KEMAHASISWAAN - KEGIATAN MAHASISWA (/api/kegiatan-mahasiswa/*)
    |==========================================================================
    */

    public function getKegiatanMahasiswaList(array $query = [], ?string $token = null): Response
    {
        return $this->get('kegiatan-mahasiswa', $query, $token);
    }

    public function getKegiatanMahasiswaById(int|string $id, ?string $token = null): Response
    {
        return $this->get("kegiatan-mahasiswa/{$id}", [], $token);
    }

    public function createKegiatanMahasiswa(array $data, ?string $token = null): Response
    {
        return $this->post('kegiatan-mahasiswa', $data, $token);
    }

    public function updateKegiatanMahasiswa(int|string $id, array $data, ?string $token = null): Response
    {
        return $this->put("kegiatan-mahasiswa/{$id}", $data, $token);
    }

    public function deleteKegiatanMahasiswa(int|string $id, ?string $token = null): Response
    {
        return $this->delete("kegiatan-mahasiswa/{$id}", [], $token);
    }

    /*
    |==========================================================================
    | MODUL: KEMAHASISWAAN - ENROLLMENT KELAS (/api/enrollment-kelas/*)
    |==========================================================================
    */

    public function getEnrollmentKelasList(array $query = [], ?string $token = null): Response
    {
        return $this->get('enrollment-kelas', $query, $token);
    }

    public function getEnrollmentKelasById(int|string $id, ?string $token = null): Response
    {
        return $this->get("enrollment-kelas/{$id}", [], $token);
    }

    public function createEnrollmentKelas(array $data, ?string $token = null): Response
    {
        return $this->post('enrollment-kelas', $data, $token);
    }

    public function updateEnrollmentKelas(int|string $id, array $data, ?string $token = null): Response
    {
        return $this->put("enrollment-kelas/{$id}", $data, $token);
    }

    public function deleteEnrollmentKelas(int|string $id, ?string $token = null): Response
    {
        return $this->delete("enrollment-kelas/{$id}", [], $token);
    }

    /*
    |==========================================================================
    | MODUL: ENROLLMENT MATA KULIAH & NILAI
    |==========================================================================
    */

    public function getEnrollmentMataKuliahList(array $query = [], ?string $token = null): Response
    {
        return $this->get('enrollment-mata-kuliah', $query, $token);
    }

    public function getEnrollmentMataKuliahById(int|string $id, ?string $token = null): Response
    {
        return $this->get("enrollment-mata-kuliah/{$id}", [], $token);
    }

    public function updateEnrollmentMataKuliah(int|string $id, array $data, ?string $token = null): Response
    {
        return $this->put("enrollment-mata-kuliah/{$id}", $data, $token);
    }

    public function getMataKuliahAngkatanKelasList(array $query = [], ?string $token = null): Response
    {
        return $this->get('mata-kuliah-angkatan-kelas', $query, $token);
    }

    public function getNilaiMahasiswaList(array $query = [], ?string $token = null): Response
    {
        return $this->get('nilai-mahasiswa', $query, $token);
    }

    /*
    |==========================================================================
    | MODUL: ANGGOTA UPPS (/api/anggota-upps/*)
    |==========================================================================
    */

    public function getAnggotaUppsList(array $query = [], ?string $token = null): Response
    {
        return $this->get('anggota-upps', $query, $token);
    }

    public function createAnggotaUpps(array $data, ?string $token = null): Response
    {
        return $this->post('anggota-upps', $data, $token);
    }

    public function updateAnggotaUpps(int|string $id, array $data, ?string $token = null): Response
    {
        return $this->put("anggota-upps/{$id}", $data, $token);
    }

    public function deleteAnggotaUpps(int|string $id, ?string $token = null): Response
    {
        return $this->delete("anggota-upps/{$id}", [], $token);
    }

    /*
    |==========================================================================
    | MODUL: JENIS PRESTASI (/api/jenis-prestasi/*)
    |==========================================================================
    */

    public function getNilaiMahasiswaById(int|string $id, ?string $token = null): Response
    {
        return $this->get("nilai-mahasiswa/{$id}", [], $token);
    }

    public function updateNilaiMahasiswaLinkNilai(int|string $id, array $data, ?string $token = null): Response
    {
        return $this->patch("nilai-mahasiswa/{$id}/link-nilai", $data, $token);
    }

    public function updateNilaiMahasiswaLinkEvidence(int|string $id, array $data, ?string $token = null): Response
    {
        return $this->patch("nilai-mahasiswa/{$id}/link-evidence", $data, $token);
    }
    /*
    |==========================================================================
    | MODUL: KEMAHASISWAAN - STATUS MAHASISWA (/api/status-mahasiswa/*)
    |==========================================================================
    */

    public function getStatusMahasiswaList(array $query = [], ?string $token = null): Response
    {
        return $this->get('status-mahasiswa', $query, $token);
    }

    public function getStatusMahasiswaById(int|string $id, ?string $token = null): Response
    {
        return $this->get("status-mahasiswa/{$id}", [], $token);
    }

    public function createStatusMahasiswa(array $data, ?string $token = null): Response
    {
        return $this->post('status-mahasiswa', $data, $token);
    }

    public function updateStatusMahasiswa(int|string $id, array $data, ?string $token = null): Response
    {
        return $this->put("status-mahasiswa/{$id}", $data, $token);
    }

    public function deleteStatusMahasiswa(int|string $id, ?string $token = null): Response
    {
        return $this->delete("status-mahasiswa/{$id}", [], $token);
    }
}
