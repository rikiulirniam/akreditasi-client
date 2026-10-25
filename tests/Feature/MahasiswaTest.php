<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MahasiswaTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    protected function getFakeToken(): string
    {
        $header = base64_encode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
        $payload = base64_encode(json_encode([
            'sub' => 1,
            'exp' => time() + 3600,
            'user' => [
                'nama' => 'Admin Test',
                'role' => 'Admin',
                'email' => 'admin@example.com',
            ],
        ]));
        $signature = base64_encode('fake_signature');

        return "{$header}.{$payload}.{$signature}";
    }

    protected function authSession(): array
    {
        return [
            'access_token' => $this->getFakeToken(),
            'user' => ['nama' => 'Admin Test', 'role' => 'Admin'],
        ];
    }

    public function test_unauthenticated_user_is_redirected_to_login(): void
    {
        $response = $this->get(route('mahasiswa.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_mahasiswa_index_with_status_column(): void
    {
        Http::fake([
            '*/api/mahasiswa*' => Http::response([
                'success' => true,
                'data' => [
                    [
                        'id' => 1,
                        'nama_mahasiswa' => 'Budi Santoso',
                        'nim' => 33423001,
                        'tahun_masuk' => 2023,
                        'jenis_pendaftaran' => 'SNBP',
                        'tanggal_kelulusan' => null,
                        'masa_studi_bulan' => 48,
                    ],
                ],
                'pagination' => [
                    'total' => 1,
                    'per_page' => 10,
                    'current_page' => 1,
                    'last_page' => 1,
                ],
            ], 200),
            '*/api/status-mahasiswa*' => Http::response([
                'success' => true,
                'data' => [
                    [
                        'id' => 10,
                        'id_enrollment_mahasiswa_angkatan_kelas' => 1,
                        'status' => 'Aktif',
                    ],
                ],
            ], 200),
            '*/api/enrollment-kelas*' => Http::response([
                'success' => true,
                'data' => [
                    [
                        'id' => 1,
                        'id_mahasiswa' => 1,
                        'angkatan_kelas_id' => 1,
                    ],
                ],
            ], 200),
            '*/api/angkatan*' => Http::response([
                'success' => true,
                'data' => [
                    ['id' => 1, 'angkatan' => 2023],
                ],
            ], 200),
        ]);

        $response = $this->withSession($this->authSession())
            ->get(route('mahasiswa.index'));

        $response->assertStatus(200);
        $response->assertSee('Status');
        $response->assertSee('Budi Santoso');
        $response->assertSee('Aktif');
        // Ensure eye icon is a link redirecting to show page
        $response->assertSee(route('mahasiswa.show', 1));
    }

    public function test_authenticated_user_can_view_mahasiswa_show_page(): void
    {
        Http::fake([
            '*/api/mahasiswa/1' => Http::response([
                'success' => true,
                'data' => [
                    'id' => 1,
                    'nama_mahasiswa' => 'Budi Santoso',
                    'nim' => 33423001,
                    'tahun_masuk' => 2023,
                    'jenis_pendaftaran' => 'SNBP',
                    'tanggal_kelulusan' => null,
                    'masa_studi_bulan' => 48,
                ],
            ], 200),
            '*/api/enrollment-kelas*' => Http::response([
                'success' => true,
                'data' => [
                    [
                        'id' => 5,
                        'id_mahasiswa' => 1,
                        'angkatan_kelas_id' => 1,
                        'nama_kelas' => 'TI 2-A',
                        'nama_angkatan' => '2023',
                    ],
                ],
            ], 200),
            '*/api/status-mahasiswa*' => Http::response([
                'success' => true,
                'data' => [
                    [
                        'id' => 10,
                        'id_enrollment_mahasiswa_angkatan_kelas' => 5,
                        'status' => 'Aktif',
                        'semester' => 'Semester 1',
                        'created_at' => '2023-09-01T08:00:00Z',
                    ],
                ],
            ], 200),
            '*/api/tahun-akademik*' => Http::response([
                'success' => true,
                'data' => [
                    ['id' => 1, 'tahun_akademik' => '2023/2024 Ganjil'],
                ],
            ], 200),
            '*/api/angkatan*' => Http::response([
                'success' => true,
                'data' => [
                    ['id' => 1, 'angkatan' => 2023],
                ],
            ], 200),
            '*/api/angkatan-kelas*' => Http::response([
                'success' => true,
                'data' => [],
            ], 200),
        ]);

        $response = $this->withSession($this->authSession())
            ->get(route('mahasiswa.show', 1));

        $response->assertStatus(200);
        $response->assertSee('Detail Mahasiswa');
        $response->assertSee('Budi Santoso');
        $response->assertSee('33423001');
        $response->assertSee('History Status Mahasiswa');
        $response->assertSee('Tambah Status');
        $response->assertSee('Aktif');
    }

    public function test_user_can_add_status_for_mahasiswa(): void
    {
        Http::fake([
            '*/api/status-mahasiswa*' => Http::response([
                'success' => true,
                'message' => 'Status mahasiswa berhasil ditambahkan.',
                'data' => [
                    'id' => 50,
                    'id_enrollment_mahasiswa_angkatan_kelas' => 5,
                    'status' => 'Aktif',
                ],
            ], 200),
        ]);

        $response = $this->withSession($this->authSession())
            ->post(route('mahasiswa.status.store', 1), [
                'id_enrollment_mahasiswa_angkatan_kelas' => 5,
                'semester' => 'Semester 2',
                'tahun_akademik_id' => 1,
                'status' => 'Aktif',
            ]);

        $response->assertRedirect(route('mahasiswa.show', 1));
        $response->assertSessionHas('toast');
    }

    public function test_user_can_update_status_for_mahasiswa(): void
    {
        Http::fake([
            '*/api/status-mahasiswa/*' => Http::response([
                'success' => true,
                'message' => 'Status mahasiswa berhasil diperbarui.',
                'data' => [
                    'id' => 50,
                    'id_enrollment_mahasiswa_angkatan_kelas' => 5,
                    'status' => 'Cuti',
                ],
            ], 200),
        ]);

        $response = $this->withSession($this->authSession())
            ->put(route('mahasiswa.status.update', [1, 50]), [
                'id_enrollment_mahasiswa_angkatan_kelas' => 5,
                'semester' => 'Semester 2',
                'tahun_akademik_id' => 1,
                'status' => 'Cuti',
            ]);

        $response->assertRedirect(route('mahasiswa.show', 1));
        $response->assertSessionHas('toast');
    }

    public function test_user_can_delete_status_for_mahasiswa(): void
    {
        Http::fake([
            '*/api/status-mahasiswa/*' => Http::response([
                'success' => true,
                'message' => 'Status mahasiswa berhasil dihapus.',
            ], 200),
        ]);

        $response = $this->withSession($this->authSession())
            ->delete(route('mahasiswa.status.destroy', [1, 50]));

        $response->assertRedirect(route('mahasiswa.show', 1));
        $response->assertSessionHas('toast');
    }

    public function test_user_can_add_status_without_semester(): void
    {
        Http::fake([
            '*/api/status-mahasiswa*' => Http::response([
                'success' => true,
                'message' => 'Status mahasiswa berhasil ditambahkan.',
                'data' => [
                    'id' => 51,
                    'id_enrollment_mahasiswa_angkatan_kelas' => 5,
                    'status' => 'Aktif',
                ],
            ], 200),
        ]);

        $response = $this->withSession($this->authSession())
            ->post(route('mahasiswa.status.store', 1), [
                'id_enrollment_mahasiswa_angkatan_kelas' => 5,
                'tahun_akademik_id' => 1,
                'status' => 'Aktif',
            ]);

        $response->assertRedirect(route('mahasiswa.show', 1));
        $response->assertSessionHas('toast');
    }

    public function test_user_cannot_add_status_without_tahun_akademik_id(): void
    {
        $response = $this->withSession($this->authSession())
            ->post(route('mahasiswa.status.store', 1), [
                'id_enrollment_mahasiswa_angkatan_kelas' => 5,
                'status' => 'Aktif',
            ]);

        $response->assertSessionHasErrors('tahun_akademik_id');
    }

    public function test_user_can_add_status_with_combo_tahun_akademik_gasal(): void
    {
        Http::fake([
            '*/api/status-mahasiswa*' => Http::response([
                'success' => true,
                'message' => 'Status mahasiswa berhasil ditambahkan.',
                'data' => [
                    'id' => 52,
                    'id_enrollment_mahasiswa_angkatan_kelas' => 5,
                    'status' => 'Aktif',
                ],
            ], 200),
        ]);

        $response = $this->withSession($this->authSession())
            ->post(route('mahasiswa.status.store', 1), [
                'id_enrollment_mahasiswa_angkatan_kelas' => 5,
                'tahun_akademik_id' => '4_Gasal',
                'status' => 'Aktif',
            ]);

        $response->assertRedirect(route('mahasiswa.show', 1));
        $response->assertSessionHas('toast');

        // Verify session saved with separated integer id and semester
        $custom = session('custom_status_mahasiswa_1', []);
        $this->assertNotEmpty($custom);
        $last = end($custom);
        $this->assertEquals(4, $last['tahun_akademik_id']);
        $this->assertEquals('Gasal', $last['semester']);
    }

    public function test_authenticated_user_can_filter_mahasiswa_by_status(): void
    {
        Http::fake([
            '*/api/mahasiswa*' => Http::response([
                'success' => true,
                'data' => [
                    [
                        'id' => 1,
                        'nama_mahasiswa' => 'Budi Santoso',
                        'nim' => 33423001,
                        'tahun_masuk' => 2023,
                        'jenis_pendaftaran' => 'SNBP',
                        'tanggal_kelulusan' => null,
                        'masa_studi_bulan' => 48,
                    ],
                    [
                        'id' => 2,
                        'nama_mahasiswa' => 'Siti Aminah',
                        'nim' => 33423002,
                        'tahun_masuk' => 2022,
                        'jenis_pendaftaran' => 'SNBT',
                        'tanggal_kelulusan' => '2026-08-01',
                        'masa_studi_bulan' => 48,
                    ],
                ],
            ], 200),
            '*/api/status-mahasiswa*' => Http::response([
                'success' => true,
                'data' => [
                    [
                        'id' => 10,
                        'id_enrollment_mahasiswa_angkatan_kelas' => 1,
                        'status' => 'Aktif',
                    ],
                    [
                        'id' => 11,
                        'id_enrollment_mahasiswa_angkatan_kelas' => 2,
                        'status' => 'Lulus',
                    ],
                ],
            ], 200),
            '*/api/enrollment-kelas*' => Http::response([
                'success' => true,
                'data' => [
                    ['id' => 1, 'id_mahasiswa' => 1],
                    ['id' => 2, 'id_mahasiswa' => 2],
                ],
            ], 200),
            '*/api/angkatan*' => Http::response([
                'success' => true,
                'data' => [
                    ['id' => 1, 'angkatan' => 2023],
                    ['id' => 2, 'angkatan' => 2022],
                ],
            ], 200),
        ]);

        $response = $this->withSession($this->authSession())
            ->get(route('mahasiswa.index', ['status' => 'Lulus']));

        $response->assertStatus(200);
        $response->assertSee('Siti Aminah');
        $response->assertDontSee('Budi Santoso');
        $response->assertSee('Semua Status');
        $response->assertSee('Semua Tahun Masuk');
    }

    public function test_authenticated_user_can_filter_mahasiswa_by_tahun_and_search(): void
    {
        Http::fake([
            '*/api/mahasiswa*' => Http::response([
                'success' => true,
                'data' => [
                    [
                        'id' => 1,
                        'nama_mahasiswa' => 'Budi Santoso',
                        'nim' => 33423001,
                        'tahun_masuk' => 2023,
                        'jenis_pendaftaran' => 'SNBP',
                        'tanggal_kelulusan' => null,
                        'masa_studi_bulan' => 48,
                    ],
                    [
                        'id' => 2,
                        'nama_mahasiswa' => 'Budi Hartono',
                        'nim' => 33422002,
                        'tahun_masuk' => 2022,
                        'jenis_pendaftaran' => 'SNBT',
                        'tanggal_kelulusan' => null,
                        'masa_studi_bulan' => 48,
                    ],
                ],
            ], 200),
            '*/api/status-mahasiswa*' => Http::response(['success' => true, 'data' => []], 200),
            '*/api/enrollment-kelas*' => Http::response(['success' => true, 'data' => []], 200),
            '*/api/angkatan*' => Http::response(['success' => true, 'data' => []], 200),
        ]);

        $response = $this->withSession($this->authSession())
            ->get(route('mahasiswa.index', ['search' => 'Budi', 'tahun' => '2023']));

        $response->assertStatus(200);
        $response->assertSee('Budi Santoso');
        $response->assertDontSee('Budi Hartono');
    }
}
