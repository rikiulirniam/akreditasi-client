<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class KelasTest extends TestCase
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
                'nama' => 'Admin Akademik',
                'role' => 'Administrator',
                'email' => 'admin@akademik.id',
            ],
        ]));
        $signature = base64_encode('fake_signature');

        return "{$header}.{$payload}.{$signature}";
    }

    public function test_kelas_index_displays_angkatan_and_jumlah_mahasiswa(): void
    {
        Http::fake([
            '*/api/kelas*' => Http::response([
                'success' => true,
                'data' => [
                    [
                        'id' => 12,
                        'kelas' => 'TI-4C',
                        'nama_kelas' => 'TI-4C',
                        'angkatan' => [
                            'id' => 5,
                            'angkatan' => 2023,
                        ],
                        'prodi' => [
                            'nama_prodi' => 'Teknologi Rekayasa Komputer',
                        ],
                        'tahun_akademik' => [
                            'nama_tahun_akademik' => '2026/2027 Ganjil',
                        ],
                        'jumlah_mahasiswa' => 25,
                        'jumlah_mata_kuliah' => 51,
                    ],
                ],
                'pagination' => [
                    'total' => 1,
                    'per_page' => 10,
                    'current_page' => 1,
                    'last_page' => 1,
                ],
            ], 200),
        ]);

        $response = $this->withSession([
            'access_token' => $this->getFakeToken(),
            'user' => [
                'id' => 1,
                'nama' => 'Administrator',
                'role' => 'ADMINISTRATOR',
            ],
        ])->get(route('kelas.index'));

        $response->assertStatus(200);
        $response->assertSee('TI-4C');
        $response->assertSee('2023');
        $response->assertSee('25 Mahasiswa');
        $response->assertSee(route('kelas.show', 12));
    }

    public function test_kelas_show_displays_detail_mahasiswa_and_mata_kuliah(): void
    {
        Http::fake([
            '*/api/kelas/12/mahasiswa*' => Http::response([
                'success' => true,
                'data' => [
                    [
                        'id' => 276,
                        'nim' => 43323201,
                        'nim_display' => '4.33.23.2.01',
                        'nama_mahasiswa' => 'Eka Ramdhan Siregar',
                        'status' => 'Aktif',
                        'ipk' => 3.66,
                        'jumlah_prestasi' => 2,
                    ],
                ],
            ], 200),
            '*/api/kelas/12/mata-kuliah*' => Http::response([
                'success' => true,
                'data' => [
                    [
                        'id' => 1,
                        'kode_mata_kuliah' => '432-191-101',
                        'nama_mata_kuliah' => 'Pengantar Teknologi Informasi',
                        'sks' => 2,
                        'dosen_pengampu' => 'Dr. Amin Suharjono, S.T., M.T.',
                    ],
                ],
            ], 200),
            '*/api/kelas/12*' => Http::response([
                'success' => true,
                'data' => [
                    'id' => 12,
                    'kelas' => 'TI-4C',
                    'nama_kelas' => 'TI-4C',
                    'angkatan' => [
                        'id' => 5,
                        'angkatan' => 2023,
                    ],
                    'prodi' => [
                        'nama_prodi' => 'Teknologi Rekayasa Komputer',
                    ],
                    'tahun_akademik' => [
                        'nama_tahun_akademik' => '2026/2027 Ganjil',
                    ],
                ],
            ], 200),
        ]);

        $response = $this->withSession([
            'access_token' => $this->getFakeToken(),
            'user' => [
                'id' => 1,
                'nama' => 'Administrator',
                'role' => 'ADMINISTRATOR',
            ],
        ])->get(route('kelas.show', 12));

        $response->assertStatus(200);
        $response->assertSee('TI-4C');
        $response->assertSee('2023');
        $response->assertSee('Teknologi Rekayasa Komputer');
        // Mahasiswa verification
        $response->assertSee('Eka Ramdhan Siregar');
        $response->assertSee('4.33.23.2.01');
        $response->assertSee('3.66');
        $response->assertSee('2 Prestasi');
        // Mata Kuliah verification
        $response->assertSee('432-191-101');
        $response->assertSee('Pengantar Teknologi Informasi');
        $response->assertSee('Dr. Amin Suharjono, S.T., M.T.');
        $response->assertSee('2 SKS');
    }

    public function test_kelas_show_filters_courses_by_active_academic_year(): void
    {
        Http::fake([
            '*/api/kelas/12/mahasiswa*' => Http::response([
                'success' => true,
                'data' => [],
            ], 200),
            '*/api/kelas/12/mata-kuliah*' => Http::response([
                'success' => true,
                'data' => [
                    [
                        'id' => 1,
                        'kode_mata_kuliah' => '432-191-101',
                        'nama_mata_kuliah' => 'Mata Kuliah Lama Semester 1',
                        'sks' => 2,
                        'dosen_pengampu' => 'Dosen Lama',
                        'tahun_akademik' => [
                            'id' => 3,
                            'tahun' => '2023/2024',
                            'semester' => 'Ganjil',
                            'is_active' => false,
                        ],
                    ],
                    [
                        'id' => 45,
                        'kode_mata_kuliah' => '432-191-701',
                        'nama_mata_kuliah' => 'Virtual System & Services',
                        'sks' => 3,
                        'dosen_pengampu' => 'Dr. Eni Dwi Wardihani, S.T., M.T.',
                        'tahun_akademik' => [
                            'id' => 9,
                            'tahun' => '2026/2027',
                            'semester' => 'Ganjil',
                            'nama_tahun_akademik' => '2026/2027 Ganjil',
                            'is_active' => true,
                        ],
                    ],
                ],
            ], 200),
            '*/api/kelas/12*' => Http::response([
                'success' => true,
                'data' => [
                    'id' => 12,
                    'kelas' => 'TI-4C',
                    'tahun_akademik' => [
                        'id' => 9,
                        'nama_tahun_akademik' => '2026/2027 Ganjil',
                        'is_active' => true,
                    ],
                ],
            ], 200),
        ]);

        $response = $this->withSession([
            'access_token' => $this->getFakeToken(),
            'user' => [
                'id' => 1,
                'nama' => 'Administrator',
                'role' => 'ADMINISTRATOR',
            ],
        ])->get(route('kelas.show', 12));

        $response->assertStatus(200);
        $response->assertSee('Virtual System & Services');
        $response->assertSee('432-191-701');
        $response->assertDontSee('Mata Kuliah Lama Semester 1');
    }

    public function test_kelas_store_mata_kuliah_and_dosen_successfully(): void
    {
        Http::fake([
            '*/api/kelas/12*' => Http::response([
                'success' => true,
                'data' => [
                    'id' => 12,
                    'kelas' => 'TI-4C',
                    'tahun_akademik_id' => 9,
                    'angkatan_kelas_id' => 97,
                ],
            ], 200),
            '*/api/enrollment-mata-kuliah' => Http::response([
                'success' => true,
                'message' => 'Enrollment berhasil dibuat',
                'data' => [
                    'id' => 501,
                    'tahun_akademik_id' => 9,
                    'mata_kuliah_id' => 5,
                    'dosen_id' => 7,
                ],
            ], 201),
            '*/api/mata-kuliah-angkatan-kelas' => Http::response([
                'success' => true,
                'message' => 'Mata kuliah kelas berhasil dihubungkan',
                'data' => [
                    'id' => 801,
                    'enrollment_mata_kuliah_id' => 501,
                    'angkatan_kelas_id' => 97,
                ],
            ], 201),
            '*/api/mata-kuliah/5*' => Http::response([
                'success' => true,
                'data' => [
                    'id' => 5,
                    'kode_mata_kuliah' => '432-191-501',
                    'nama_mata_kuliah' => 'Kecerdasan Buatan',
                    'sks' => 3,
                ],
            ], 200),
            '*/api/users/7*' => Http::response([
                'success' => true,
                'data' => [
                    'id' => 7,
                    'name' => 'Dr. Amin Suharjono, S.T., M.T.',
                    'email' => 'dosen@polines.ac.id',
                ],
            ], 200),
        ]);

        $response = $this->withSession([
            'access_token' => $this->getFakeToken(),
            'user' => [
                'id' => 1,
                'nama' => 'Administrator',
                'role' => 'ADMINISTRATOR',
            ],
        ])->post(route('kelas.mata-kuliah.store', 12), [
            'mata_kuliah_id' => 5,
            'dosen_id' => 7,
        ]);

        $response->assertRedirect(route('kelas.show', 12));
        $response->assertSessionHas('toast', function ($toast) {
            return ($toast['type'] ?? '') === 'success';
        });
    }

    public function test_kelas_update_dosen_pengampu_successfully(): void
    {
        Http::fake([
            '*/api/enrollment-mata-kuliah/501' => Http::response([
                'success' => true,
                'message' => 'Enrollment berhasil diperbarui',
                'data' => [
                    'id' => 501,
                    'tahun_akademik_id' => 9,
                    'mata_kuliah_id' => 5,
                    'dosen_id' => 8,
                ],
            ], 200),
            '*/api/users/8*' => Http::response([
                'success' => true,
                'data' => [
                    'id' => 8,
                    'name' => 'Dr. Eni Dwi Wardihani, S.T., M.T.',
                    'email' => 'dosen2@polines.ac.id',
                ],
            ], 200),
        ]);

        $response = $this->withSession([
            'access_token' => $this->getFakeToken(),
            'user' => [
                'id' => 1,
                'nama' => 'Administrator',
                'role' => 'ADMINISTRATOR',
            ],
        ])->put(route('kelas.mata-kuliah.update', [12, 501]), [
            'mata_kuliah_id' => 5,
            'tahun_akademik_id' => 9,
            'dosen_id' => 8,
        ]);

        $response->assertRedirect(route('kelas.show', 12));
        $response->assertSessionHas('toast', function ($toast) {
            return ($toast['type'] ?? '') === 'success';
        });
    }

    public function test_kelas_destroy_mata_kuliah_successfully(): void
    {
        Http::fake([
            '*/api/mata-kuliah-angkatan-kelas/801' => Http::response([
                'success' => true,
                'message' => 'Mata kuliah angkatan kelas berhasil dihapus',
            ], 200),
        ]);

        $response = $this->withSession([
            'access_token' => $this->getFakeToken(),
            'user' => [
                'id' => 1,
                'nama' => 'Administrator',
                'role' => 'ADMINISTRATOR',
            ],
        ])->delete(route('kelas.mata-kuliah.destroy', [12, 801]));

        $response->assertRedirect(route('kelas.show', 12));
        $response->assertSessionHas('toast', function ($toast) {
            return ($toast['type'] ?? '') === 'success';
        });
    }
}
