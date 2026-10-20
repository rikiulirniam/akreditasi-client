<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class DosenShowTest extends TestCase
{
    public function test_dosen_detail_page_loads_successfully_with_mockup_data(): void
    {
        // Mock remote Tridharma API call
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
}
