<?php

namespace Tests\Feature;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class NilaiMahasiswaTest extends TestCase
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

    public function test_unauthenticated_user_is_redirected_to_login(): void
    {
        $response = $this->get(route('nilai-mahasiswa.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_nilai_mahasiswa_index(): void
    {
        Http::fake([
            '*/api/nilai-mahasiswa*' => Http::response([
                'success' => true,
                'data' => [
                    [
                        'id' => 1,
                        'mata_kuliah_angkatan_kelas_id' => 10,
                        'mahasiswa_id' => 100,
                        'tahun_akademik_id' => 1,
                        'nilai_angka' => 88.5,
                        'nilai_huruf' => 'A',
                        'bobot' => 4.0,
                        'link_nilai' => 'https://docs.google.com/sample',
                        'link_evidence' => 'https://drive.google.com/sample',
                        'mahasiswa' => [
                            'id' => 100,
                            'nim' => '43325001',
                            'nama_mahasiswa' => 'Budi Santoso',
                        ],
                        'mata_kuliah_angkatan_kelas' => [
                            'id' => 10,
                            'enrollment_mata_kuliah' => [
                                'mata_kuliah' => [
                                    'id' => 5,
                                    'kode_mata_kuliah' => 'MK-01',
                                    'nama_mata_kuliah' => 'Pemrograman Web Lanjut',
                                    'sks' => 3,
                                ],
                                'tahun_akademik' => [
                                    'id' => 1,
                                    'tahun_akademik' => '2024/2025',
                                    'semester' => 'Ganjil',
                                ],
                            ],
                            'angkatan_kelas' => [
                                'kelas' => [
                                    'id' => 2,
                                    'kelas' => 'TI-3A',
                                ],
                            ],
                        ],
                        'tahun_akademik' => [
                            'id' => 1,
                            'tahun_akademik' => '2024/2025',
                            'semester' => 'Ganjil',
                        ],
                    ],
                ],
            ], 200),
            '*/api/mata-kuliah-angkatan-kelas*' => Http::response(['success' => true, 'data' => []], 200),
            '*/api/kelas*' => Http::response(['success' => true, 'data' => []], 200),
            '*/api/mata-kuliah*' => Http::response(['success' => true, 'data' => []], 200),
            '*/api/tahun-akademik*' => Http::response(['success' => true, 'data' => []], 200),
            '*/api/data-mahasiswa*' => Http::response(['success' => true, 'data' => []], 200),
        ]);

        $response = $this->withSession([
            'access_token' => $this->getFakeToken(),
            'user' => ['nama' => 'Admin Akademik', 'role' => 'Administrator'],
        ])->get(route('nilai-mahasiswa.index'));

        $response->assertStatus(200);
        $response->assertSee('Nilai Mahasiswa');
        $response->assertSee('Budi Santoso');
        $response->assertSee('43325001');
        $response->assertSee('TI-3A');
        $response->assertSee('Pemrograman Web Lanjut');
        $response->assertSee('Format Import');
        $response->assertSee('Import Nilai');
        $response->assertSee('Tambah Nilai');
    }

    public function test_user_can_download_single_template_xlsx(): void
    {
        Http::fake([
            '*/api/nilai-mahasiswa/template*' => Http::response(
                'FAKE_XLSX_BINARY_CONTENT',
                200,
                [
                    'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'Content-Disposition' => 'attachment; filename="template_nilai_single.xlsx"',
                ]
            ),
        ]);

        $response = $this->withSession([
            'access_token' => $this->getFakeToken(),
            'user' => ['nama' => 'Admin', 'role' => 'Administrator'],
        ])->get(route('nilai-mahasiswa.template', ['mode' => 'single', 'format' => 'xlsx']));

        $response->assertStatus(200);
        $this->assertEquals('FAKE_XLSX_BINARY_CONTENT', $response->getContent());
        $this->assertStringContainsString('template_nilai_single.xlsx', (string) $response->headers->get('Content-Disposition'));
    }

    public function test_user_can_download_multi_template_csv(): void
    {
        Http::fake([
            '*/api/nilai-mahasiswa/template*' => Http::response(
                "Kode Mata Kuliah,Nama Mata Kuliah,Kelas,NIM,Nama Mahasiswa,Nilai Angka,Nilai Huruf,Bobot,Link Nilai,Link Evidence\n",
                200,
                [
                    'Content-Type' => 'text/csv; charset=UTF-8',
                    'Content-Disposition' => 'attachment; filename="template_nilai_multi.csv"',
                ]
            ),
        ]);

        $response = $this->withSession([
            'access_token' => $this->getFakeToken(),
            'user' => ['nama' => 'Admin', 'role' => 'Administrator'],
        ])->get(route('nilai-mahasiswa.template', ['mode' => 'multi', 'format' => 'csv']));

        $response->assertStatus(200);
        $this->assertStringContainsString('Kode Mata Kuliah', (string) $response->getContent());
        $this->assertStringContainsString('template_nilai_multi.csv', (string) $response->headers->get('Content-Disposition'));
    }

    public function test_user_can_import_nilai_via_file(): void
    {
        Http::fake([
            '*/api/nilai-mahasiswa/import*' => Http::response([
                'success' => true,
                'message' => 'Proses import nilai mahasiswa selesai diproses',
                'data' => [
                    'total_rows' => 5,
                    'imported_count' => 3,
                    'updated_count' => 2,
                    'skipped_count' => 0,
                    'failed_count' => 0,
                ],
            ], 200),
        ]);

        $file = UploadedFile::fake()->createWithContent('nilai.csv', "NIM,Nama Mahasiswa,Nilai Angka,Nilai Huruf,Bobot\n43325001,Budi,85,A,4.0\n");

        $response = $this->withSession([
            'access_token' => $this->getFakeToken(),
            'user' => ['nama' => 'Admin', 'role' => 'Administrator'],
        ])->post(route('nilai-mahasiswa.import'), [
            'file' => $file,
            'duplicate_action' => 'update',
        ]);

        $response->assertRedirect(route('nilai-mahasiswa.index'));
        $response->assertSessionHas('toast');
        $toast = session('toast');
        $this->assertEquals('success', $toast['type']);
        $this->assertStringContainsString('3 data baru', $toast['message']);
        $this->assertStringContainsString('2 diperbarui', $toast['message']);
    }

    public function test_user_can_create_nilai_manually(): void
    {
        Http::fake([
            '*/api/nilai-mahasiswa' => Http::response([
                'success' => true,
                'message' => 'Nilai mahasiswa berhasil ditambahkan',
                'data' => ['id' => 99],
            ], 201),
        ]);

        $response = $this->withSession([
            'access_token' => $this->getFakeToken(),
            'user' => ['nama' => 'Admin', 'role' => 'Administrator'],
        ])->post(route('nilai-mahasiswa.store'), [
            'mahasiswa_id' => 10,
            'mata_kuliah_angkatan_kelas_id' => 5,
            'nilai_angka' => 86,
        ]);

        $response->assertRedirect(route('nilai-mahasiswa.index'));
        $response->assertSessionHas('toast');
        $toast = session('toast');
        $this->assertEquals('success', $toast['type']);
    }

    public function test_user_can_update_nilai(): void
    {
        Http::fake([
            '*/api/nilai-mahasiswa/99' => Http::response([
                'success' => true,
                'message' => 'Nilai mahasiswa berhasil diperbarui',
                'data' => ['id' => 99],
            ], 200),
        ]);

        $response = $this->withSession([
            'access_token' => $this->getFakeToken(),
            'user' => ['nama' => 'Admin', 'role' => 'Administrator'],
        ])->put(route('nilai-mahasiswa.update', 99), [
            'nilai_angka' => 90,
        ]);

        $response->assertRedirect(route('nilai-mahasiswa.index'));
        $response->assertSessionHas('toast');
    }

    public function test_user_can_delete_nilai(): void
    {
        Http::fake([
            '*/api/nilai-mahasiswa/99' => Http::response([
                'success' => true,
                'message' => 'Data nilai mahasiswa berhasil dihapus',
                'data' => null,
            ], 200),
        ]);

        $response = $this->withSession([
            'access_token' => $this->getFakeToken(),
            'user' => ['nama' => 'Admin', 'role' => 'Administrator'],
        ])->delete(route('nilai-mahasiswa.destroy', 99));

        $response->assertRedirect(route('nilai-mahasiswa.index'));
        $response->assertSessionHas('toast');
        $this->assertEquals('success', session('toast')['type']);
    }

    public function test_user_can_fetch_khs_and_transkrip_json(): void
    {
        Http::fake([
            '*/api/mahasiswa/100/khs*' => Http::response([
                'success' => true,
                'data' => [
                    'ringkasan' => [
                        'total_sks' => 20,
                        'total_kredit' => 76.0,
                        'ip' => 3.80,
                    ],
                    'items' => [],
                ],
            ], 200),
            '*/api/mahasiswa/100/transkrip*' => Http::response([
                'success' => true,
                'data' => [
                    'ringkasan' => [
                        'total_sks' => 42,
                        'total_kredit' => 157.6,
                        'ipk' => 3.75,
                    ],
                    'mata_kuliah' => [],
                ],
            ], 200),
        ]);

        $khsResponse = $this->withSession([
            'access_token' => $this->getFakeToken(),
            'user' => ['nama' => 'Admin', 'role' => 'Administrator'],
        ])->get(route('nilai-mahasiswa.khs', 100));

        $khsResponse->assertStatus(200);
        $khsResponse->assertJsonPath('data.ringkasan.ip', 3.80);

        $transkripResponse = $this->withSession([
            'access_token' => $this->getFakeToken(),
            'user' => ['nama' => 'Admin', 'role' => 'Administrator'],
        ])->get(route('nilai-mahasiswa.transkrip', 100));

        $transkripResponse->assertStatus(200);
        $transkripResponse->assertJsonPath('data.ringkasan.ipk', 3.75);
    }
}
