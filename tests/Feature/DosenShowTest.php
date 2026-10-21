<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class DosenShowTest extends TestCase
{
    public function test_dosen_detail_page_loads_successfully_with_mockup_data(): void
    {
        // Mock remote Tridharma API call with relations data
        Http::fake([
            '*/dosen/1*' => Http::response([
                'success' => true,
                'data' => [
                    'id' => 1,
                    'nama_dosen' => 'Dr. Budi Santoso, M. Kom.',
                    'nidn' => '0015088501',
                    'status_dosen' => 'Aktif',
                    'email' => 'Budisantoso@gmail.com',
                    'sertifikasi' => 'https://drive.google.com/test-cert',
                    'jabatan' => ['nama_jabatan' => 'Lektor Kepala'],
                    'jurusan' => 'Teknik Elektro',
                    'prodi' => 'D4 - Teknologi Rekayasa Komputer',
                    'penelitian' => [
                        [
                            'judul' => 'Sistem Prediksi Harga Komoditas Pangan Jawa Tengah dengan LSTM',
                            'tahun' => 2024,
                            'peran' => 'Ketua Pengusul',
                            'skema' => 'Penelitian Terapan Unggulan Perguruan Tinggi',
                            'anggota' => 'Ahmad Dahlan, Siti Nurhaliza',
                            'link' => 'https://drive.google.com/penelitian-1',
                        ],
                    ],
                    'pkm' => [
                        [
                            'judul' => 'SISTEM KEAMANAN LINGKUNGAN BERBASIS INTERNET OF THING (IOT)',
                            'tahun' => 2023,
                            'peran' => 'Ketua Pengusul',
                            'skema' => 'Pemberdayaan Kemitraan Masyarakat',
                            'anggota' => 'Riko Kurniawan',
                            'link' => 'https://drive.google.com/pkm-1',
                        ],
                    ],
                ],
            ], 200),
        ]);

        $response = $this->withSession([
            'access_token' => 'dummy-token',
            'user' => [
                'nama' => 'Kuwat',
                'role' => 'Kaprodi',
                'email' => 'kuwat@example.com',
            ],
        ])->get(route('dosen.show', 1));

        $response->assertStatus(200);
        $response->assertSee('<title>Detail Dosen - Portal Akreditasi</title>', false);
        $response->assertDontSee('<title><a href', false);
        $response->assertSee('Dosen');
        $response->assertSee('Detail dosen');
        $response->assertSee('Dr. Budi Santoso, M. Kom.');
        $response->assertSee('Lektor Kepala');
        $response->assertSee('Teknik Elektro');
        $response->assertSee('D4 - Teknologi Rekayasa Komputer');
        $response->assertSee('Budisantoso@gmail.com');
        $response->assertSee('Aktif');
        $response->assertSee('Lihat sertifikasi');
        $response->assertSee('Penelitian');
        $response->assertSee('HaKI');
        $response->assertSee('Riwayat Pendidikan');
        $response->assertSee('Pengabdian Kepada Masyarakat');
        $response->assertSee('Sistem Prediksi Harga Komoditas Pangan Jawa Tengah dengan LSTM');
        $response->assertSee('SISTEM KEAMANAN LINGKUNGAN BERBASIS INTERNET OF THING (IOT)');
    }

    public function test_dosen_detail_page_renders_empty_states_when_no_relations_exist(): void
    {
        Http::fake([
            '*/dosen/99*' => Http::response([
                'success' => true,
                'data' => [
                    'id' => 99,
                    'nama_dosen' => 'Dosen Tanpa Relasi',
                    'nidn' => '1234567890',
                    'status_dosen' => 'Aktif',
                    'email' => 'tanpa.relasi@example.com',
                ],
            ], 200),
        ]);

        $response = $this->withSession([
            'access_token' => 'dummy-token',
            'user' => [
                'nama' => 'Kuwat',
                'role' => 'Kaprodi',
                'email' => 'kuwat@example.com',
            ],
        ])->get(route('dosen.show', 99));

        $response->assertStatus(200);
        $response->assertSee('Belum ada data penelitian.');
        $response->assertSee('Belum ada data pengabdian kepada masyarakat.');
        $response->assertSee('Belum ada riwayat pendidikan.');
        $response->assertSee('Belum ada data luaran.');
    }

    public function test_dosen_detail_page_handles_array_relations_without_htmlspecialchars_error(): void
    {
        // Mock API response where relational fields are returned as nested arrays/objects
        Http::fake([
            '*/dosen/2*' => Http::response([
                'success' => true,
                'data' => [
                    'id' => 2,
                    'nama_dosen' => 'Prof. Dr. Ir. Siti Aminah, M.T.',
                    'nidn' => '0023098102',
                    'status_dosen' => ['nama' => 'Aktif'],
                    'email' => 'siti.aminah@polines.ac.id',
                    'sertifikasi' => ['url' => 'https://drive.google.com/siti-cert'],
                    'jabatan' => ['id_jabatan' => 2, 'nama_jabatan' => 'Guru Besar'],
                    'bidang_keahlian' => [
                        ['id_bidang_keahlian' => 5, 'nama_bidang' => 'Teknik Elektro Industri'],
                    ],
                    'jurusan' => ['id_jurusan' => 3, 'nama_jurusan' => 'Teknik Elektro'],
                    'prodi' => ['id_prodi' => 1, 'nama_prodi' => 'D4 - Teknologi Rekayasa Komputer'],
                ],
            ], 200),
        ]);

        $response = $this->withSession([
            'access_token' => 'dummy-token',
            'user' => [
                'nama' => 'Kuwat',
                'role' => 'Kaprodi',
                'email' => 'kuwat@example.com',
            ],
        ])->get(route('dosen.show', 2));

        $response->assertStatus(200);
        $response->assertSee('Prof. Dr. Ir. Siti Aminah, M.T.');
        $response->assertSee('Guru Besar');
        $response->assertSee('Teknik Elektro');
        $response->assertSee('D4 - Teknologi Rekayasa Komputer');
    }

    public function test_dosen_index_page_handles_array_fields_without_error(): void
    {
        Http::fake([
            '*/dosen*' => Http::response([
                'success' => true,
                'data' => [
                    'data' => [
                        [
                            'id_dosen' => 1,
                            'nama_dosen' => 'Dr. Budi Santoso, M. Kom.',
                            'nidn' => '0015088501',
                            'status_dosen' => ['status' => 'DTPS'],
                            'email' => 'Budisantoso@gmail.com',
                            'sertifikasi' => ['url' => 'https://drive.google.com'],
                        ],
                    ],
                    'total' => 1,
                    'per_page' => 10,
                    'current_page' => 1,
                    'last_page' => 1,
                ],
            ], 200),
            '*/jabatan-akademik*' => Http::response(['data' => []], 200),
            '*/pendidikan*' => Http::response(['data' => []], 200),
            '*/bidang-keahlian*' => Http::response(['data' => []], 200),
        ]);

        $response = $this->withSession([
            'access_token' => 'dummy-token',
            'user' => [
                'nama' => 'Kuwat',
                'role' => 'Kaprodi',
                'email' => 'kuwat@example.com',
            ],
        ])->get(route('dosen.index'));

        $response->assertStatus(200);
        $response->assertSee('Dr. Budi Santoso, M. Kom.');
        $response->assertSee('0015088501');
    }

    public function test_dosen_index_passes_search_and_status_to_remote_api(): void
    {
        Http::fake([
            '*/dosen*' => Http::response([
                'success' => true,
                'data' => [
                    'data' => [],
                    'total' => 0,
                    'per_page' => 10,
                    'current_page' => 1,
                    'last_page' => 1,
                ],
            ], 200),
            '*/jabatan-akademik*' => Http::response(['data' => []], 200),
            '*/pendidikan*' => Http::response(['data' => []], 200),
            '*/bidang-keahlian*' => Http::response(['data' => []], 200),
        ]);

        $response = $this->withSession([
            'access_token' => 'dummy-token',
            'user' => [
                'nama' => 'Kuwat',
                'role' => 'Kaprodi',
                'email' => 'kuwat@example.com',
            ],
        ])->get(route('dosen.index', ['search' => 'Budi', 'status' => 'DTPS']));

        $response->assertStatus(200);

        Http::assertSent(function ($request) {
            if (! str_contains($request->url(), '/dosen')) {
                return false;
            }
            $query = [];
            parse_str(parse_url($request->url(), PHP_URL_QUERY) ?? '', $query);

            return ($query['search'] ?? null) === 'Budi' && ($query['status'] ?? null) === 'DTPS';
        });
    }

    public function test_dosen_store_passes_dynamic_jabatan_and_pendidikan(): void
    {
        Http::fake([
            '*/dosen' => Http::response([
                'success' => true,
                'data' => ['id' => 10],
            ], 201),
        ]);

        $response = $this->withSession([
            'access_token' => 'dummy-token',
            'user' => ['nama' => 'Kuwat', 'role' => 'Kaprodi'],
        ])->post(route('dosen.store'), [
            'nama_dosen' => 'Dr. Hendra Gunawan',
            'nidn' => '0029088401',
            'status_dosen' => 'DTPS',
            'email' => 'hendra@example.com',
            'sertifikasi' => 'https://drive.google.com/cert',
            'id_jabatan' => 4,
            'id_pendidikan' => 3,
        ]);

        $response->assertRedirect(route('dosen.index'));

        Http::assertSent(function ($request) {
            if ($request->method() !== 'POST' || ! str_ends_with(parse_url($request->url(), PHP_URL_PATH), '/dosen')) {
                return false;
            }
            $data = $request->data();

            return ($data['id_jabatan'] ?? null) === 4
                && ($data['id_pendidikan'] ?? null) === 3
                && ($data['nama_dosen'] ?? null) === 'Dr. Hendra Gunawan';
        });
    }

    public function test_dosen_update_passes_dynamic_jabatan_and_pendidikan(): void
    {
        Http::fake([
            '*/dosen/10' => Http::response([
                'success' => true,
                'data' => ['id' => 10],
            ], 200),
        ]);

        $response = $this->withSession([
            'access_token' => 'dummy-token',
            'user' => ['nama' => 'Kuwat', 'role' => 'Kaprodi'],
        ])->put(route('dosen.update', 10), [
            'nama_dosen' => 'Dr. Hendra Gunawan, M.T.',
            'nidn' => '0029088401',
            'status_dosen' => 'Tidak Tetap',
            'email' => 'hendra.updated@example.com',
            'id_jabatan' => 5,
            'id_pendidikan' => 4,
        ]);

        $response->assertRedirect(route('dosen.index'));

        Http::assertSent(function ($request) {
            if ($request->method() !== 'PUT' || ! str_contains($request->url(), '/dosen/10')) {
                return false;
            }
            $data = $request->data();

            return ($data['id_jabatan'] ?? null) === 5
                && ($data['id_pendidikan'] ?? null) === 4
                && ($data['nama_dosen'] ?? null) === 'Dr. Hendra Gunawan, M.T.';
        });
    }
}
