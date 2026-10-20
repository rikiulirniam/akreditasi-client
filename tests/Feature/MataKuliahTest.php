<?php

namespace Tests\Feature;

use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MataKuliahTest extends TestCase
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
                'nama' => 'Kuwat',
                'role' => 'Kaprodi',
                'email' => 'kuwat@example.com',
            ],
        ]));
        $signature = base64_encode('fake_signature');

        return "{$header}.{$payload}.{$signature}";
    }

    public function test_unauthenticated_user_is_redirected_to_login(): void
    {
        $response = $this->get(route('mata-kuliah.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_empty_mata_kuliah_page(): void
    {
        Http::fake([
            '*/api/mata-kuliah*' => Http::response([
                'success' => true,
                'data' => [],
            ], 200),
            '*/api/kurikulum*' => Http::response([
                'success' => true,
                'data' => [],
            ], 200),
        ]);

        $response = $this->withSession([
            'access_token' => $this->getFakeToken(),
            'user' => ['nama' => 'Kuwat', 'role' => 'Kaprodi'],
        ])->get(route('mata-kuliah.index'));

        $response->assertStatus(200);
        $response->assertSee('Kamu belum ada data Mata Kuliah');
        $response->assertSee('Tambah Mata Kuliah');
    }

    public function test_mata_kuliah_page_displays_10_items_per_page_with_pagination(): void
    {
        // 25 mock items
        $mockItems = [];
        for ($i = 1; $i <= 25; $i++) {
            $mockItems[] = [
                'id' => $i,
                'id_kurikulum' => 1,
                'kode_mata_kuliah' => sprintf('TRK%03d', $i),
                'nama_mata_kuliah' => "Mata Kuliah {$i}",
                'sks' => 3,
                'created_at' => now()->toDateTimeString(),
                'updated_at' => now()->toDateTimeString(),
            ];
        }

        Http::fake([
            '*/api/mata-kuliah*' => Http::response([
                'success' => true,
                'data' => $mockItems,
            ], 200),
            '*/api/kurikulum*' => Http::response([
                'success' => true,
                'data' => [
                    ['id' => 1, 'nama_kurikulum' => 'Kurikulum TRK 2024'],
                ],
            ], 200),
        ]);

        // Page 1: Should display 10 items (from 01 to 10 of 25)
        $responsePage1 = $this->withSession([
            'access_token' => $this->getFakeToken(),
            'user' => ['nama' => 'Kuwat', 'role' => 'Kaprodi'],
        ])->get(route('mata-kuliah.index', ['page' => 1]));

        $responsePage1->assertStatus(200);
        $responsePage1->assertSee('Menampilkan 01–10 dari 25');
        $responsePage1->assertSee('Mata Kuliah 1');
        $responsePage1->assertSee('Mata Kuliah 10');
        $responsePage1->assertDontSee('Mata Kuliah 11');

        // Page 2: Should display items 11 to 20
        $responsePage2 = $this->withSession([
            'access_token' => $this->getFakeToken(),
            'user' => ['nama' => 'Kuwat', 'role' => 'Kaprodi'],
        ])->get(route('mata-kuliah.index', ['page' => 2]));

        $responsePage2->assertStatus(200);
        $responsePage2->assertSee('Menampilkan 11–20 dari 25');
        $responsePage2->assertSee('Mata Kuliah 11');
        $responsePage2->assertSee('Mata Kuliah 20');
        $responsePage2->assertDontSee('Mata Kuliah 21');
    }

    public function test_user_can_filter_mata_kuliah_by_search_and_kurikulum(): void
    {
        $mockItems = [
            [
                'id' => 1,
                'id_kurikulum' => 1,
                'kode_mata_kuliah' => 'TRK101',
                'nama_mata_kuliah' => 'Algoritma dan Pemrograman',
                'sks' => 3,
            ],
            [
                'id' => 2,
                'id_kurikulum' => 2,
                'kode_mata_kuliah' => 'TI201',
                'nama_mata_kuliah' => 'Basis Data Lanjut',
                'sks' => 4,
            ],
        ];

        Http::fake([
            '*/api/mata-kuliah*' => Http::response([
                'success' => true,
                'data' => $mockItems,
            ], 200),
            '*/api/kurikulum*' => Http::response([
                'success' => true,
                'data' => [
                    ['id' => 1, 'nama_kurikulum' => 'Kurikulum TRK 2024'],
                    ['id' => 2, 'nama_kurikulum' => 'Kurikulum D3 Teknik Informatika 2023'],
                ],
            ], 200),
        ]);

        // Search test
        $responseSearch = $this->withSession([
            'access_token' => $this->getFakeToken(),
        ])->get(route('mata-kuliah.index', ['search' => 'Algoritma']));

        $responseSearch->assertStatus(200);
        $responseSearch->assertSee('Algoritma dan Pemrograman');
        $responseSearch->assertDontSee('Basis Data Lanjut');

        // Kurikulum filter test
        $responseFilter = $this->withSession([
            'access_token' => $this->getFakeToken(),
        ])->get(route('mata-kuliah.index', ['kurikulum_id' => 2]));

        $responseFilter->assertStatus(200);
        $responseFilter->assertSee('Basis Data Lanjut');
        $responseFilter->assertDontSee('Algoritma dan Pemrograman');
    }

    public function test_user_can_view_mata_kuliah_detail_page(): void
    {
        Http::fake([
            '*/api/mata-kuliah/1' => Http::response([
                'success' => true,
                'data' => [
                    'id' => 1,
                    'id_kurikulum' => 1,
                    'kode_mata_kuliah' => 'TRK101',
                    'nama_mata_kuliah' => 'Algoritma Pemograman',
                    'sks' => 3,
                ],
            ], 200),
            '*/api/kurikulum*' => Http::response([
                'success' => true,
                'data' => [
                    ['id' => 1, 'nama_kurikulum' => 'Kurikulum TRK 2024', 'id_prodi' => 1],
                ],
            ], 200),
            '*/api/prodi*' => Http::response([
                'success' => true,
                'data' => [
                    ['id' => 1, 'nama_program_studi' => 'Teknologi Rekayasa Komputer'],
                ],
            ], 200),
            '*/api/kelas*' => Http::response([
                'success' => true,
                'data' => [
                    ['id' => 1, 'kelas' => 'TI1-A'],
                    ['id' => 2, 'kelas' => 'TI1-B'],
                ],
            ], 200),
            '*/api/angkatan-kelas*' => Http::response([
                'success' => true,
                'data' => [
                    [
                        'id' => 8,
                        'kelas_id' => 1,
                        'angkatan_id' => 3,
                        'prodi_id' => 1,
                        'tahun_akademik_id' => 4,
                    ],
                ],
            ], 200),
            '*api/angkatan*' => Http::response([
                'success' => true,
                'data' => [
                    ['id' => 3, 'angkatan' => 2025],
                ],
            ], 200),
            '*/api/tahun-akademik*' => Http::response([
                'success' => true,
                'data' => [
                    ['id' => 4, 'tahun' => '2025/2026'],
                ],
            ], 200),
            '*/api/enrollment-kelas*' => Http::response([
                'success' => true,
                'data' => [
                    ['id' => 100, 'angkatan_kelas_id' => 8, 'id_mahasiswa' => 10],
                    ['id' => 101, 'angkatan_kelas_id' => 8, 'id_mahasiswa' => 11],
                ],
            ], 200),
            '*/api/enrollment-mata-kuliah*' => Http::response([
                'success' => true,
                'data' => [
                    ['id' => 12, 'tahun_akademik_id' => 4, 'mata_kuliah_id' => 1, 'dosen_id' => 6, 'evidence' => ''],
                ],
            ], 200),
            '*/api/mata-kuliah-angkatan-kelas*' => Http::response([
                'success' => true,
                'data' => [
                    ['id' => 93, 'enrollment_mata_kuliah_id' => 12, 'angkatan_kelas_id' => 8],
                ],
            ], 200),
            '*/api/nilai-mahasiswa*' => Http::response([
                'success' => true,
                'data' => [
                    ['enrollment_mahasiswa_angkatan_kelas_id' => 100, 'mata_kuliah_angkatan_kelas_id' => 93, 'bobot_nilai' => 3.75],
                    ['enrollment_mahasiswa_angkatan_kelas_id' => 101, 'mata_kuliah_angkatan_kelas_id' => 93, 'bobot_nilai' => 4],
                ],
            ], 200),
        ]);

        $response = $this->withSession([
            'access_token' => $this->getFakeToken(),
        ])->get(route('mata-kuliah.show', 1));

        $response->assertStatus(200);
        $response->assertSee('Detail Mata Kuliah');
        $response->assertSee('Algoritma Pemograman');
        $response->assertSee('TRK101');
        $response->assertSee('Kurikulum TRK 2024');
        $response->assertSee('Daftar Kelas');
        $response->assertSee('TI1-A');
        $response->assertSee('3.88');
        $response->assertSee('Input Nilai');
        $response->assertSee('Input Nilai Mata Kuliah');
        $response->assertSee('Tampilan saja. Link belum disimpan');
        $response->assertSee('Input Evidence');
    }

    public function test_user_can_save_a_mata_kuliah_evidence_link_to_akademik_service(): void
    {
        Http::fake([
            '*/api/enrollment-mata-kuliah/12' => Http::response([
                'success' => true,
                'data' => [
                    'id' => 12,
                    'tahun_akademik_id' => 4,
                    'mata_kuliah_id' => 1,
                    'dosen_id' => 6,
                    'mata_kuliah_angkatan_kelas_id' => 93,
                    'evidence' => '',
                ],
            ], 200),
        ]);

        $response = $this->from(route('mata-kuliah.show', 1))
            ->withSession(['access_token' => $this->getFakeToken()])
            ->put(route('mata-kuliah.update-evidence', 12), [
                'evidence' => 'https://example.com/evidence',
            ]);

        $response->assertRedirect(route('mata-kuliah.show', 1));
        $response->assertSessionHas('toast');
        $this->assertEquals('success', session('toast.type'));

        Http::assertSent(fn (HttpRequest $request) => $request->method() === 'PUT'
            && str_ends_with($request->url(), '/api/enrollment-mata-kuliah/12')
            && $request->data() === [
                'tahun_akademik_id' => 4,
                'mata_kuliah_id' => 1,
                'evidence' => 'https://example.com/evidence',
                'dosen_id' => 6,
                'mata_kuliah_angkatan_kelas_id' => 93,
            ]);
    }

    public function test_user_can_create_mata_kuliah_and_redirects_back(): void
    {
        Http::fake([
            '*/api/mata-kuliah' => Http::response([
                'success' => true,
                'message' => 'Data mata kuliah berhasil ditambahkan.',
                'data' => [
                    'id' => 99,
                    'id_kurikulum' => 1,
                    'kode_mata_kuliah' => 'TRK101',
                    'nama_mata_kuliah' => 'Pemrograman Web',
                    'sks' => 3,
                ],
            ], 200),
        ]);

        $previousUrl = route('mata-kuliah.index', ['page' => 2]);

        $response = $this->from($previousUrl)->withSession([
            'access_token' => $this->getFakeToken(),
        ])->post(route('mata-kuliah.store'), [
            'nama_mata_kuliah' => 'Pemrograman Web',
            'kode_mata_kuliah' => 'TRK101',
            'id_kurikulum' => 1,
            'sks' => 3,
        ]);

        $response->assertRedirect($previousUrl);
        $response->assertSessionHas('toast');
        $this->assertEquals('success', session('toast.type'));
    }

    public function test_user_cannot_create_mata_kuliah_with_invalid_data(): void
    {
        $response = $this->withSession([
            'access_token' => $this->getFakeToken(),
        ])->post(route('mata-kuliah.store'), [
            'nama_mata_kuliah' => '',
            'kode_mata_kuliah' => '',
            'id_kurikulum' => '',
            'sks' => '',
        ]);

        $response->assertSessionHasErrors(['nama_mata_kuliah', 'kode_mata_kuliah', 'id_kurikulum', 'sks']);
    }

    public function test_user_can_update_mata_kuliah_and_redirects_back(): void
    {
        Http::fake([
            '*/api/mata-kuliah/99' => Http::response([
                'success' => true,
                'message' => 'Data mata kuliah berhasil diperbarui.',
                'data' => [
                    'id' => 99,
                    'id_kurikulum' => 1,
                    'kode_mata_kuliah' => 'TRK101',
                    'nama_mata_kuliah' => 'Pemrograman Web Lanjut',
                    'sks' => 4,
                ],
            ], 200),
        ]);

        $previousUrl = route('mata-kuliah.show', 99);

        $response = $this->from($previousUrl)->withSession([
            'access_token' => $this->getFakeToken(),
        ])->put(route('mata-kuliah.update', 99), [
            'nama_mata_kuliah' => 'Pemrograman Web Lanjut',
            'kode_mata_kuliah' => 'TRK101',
            'id_kurikulum' => 1,
            'sks' => 4,
        ]);

        $response->assertRedirect($previousUrl);
        $response->assertSessionHas('toast');
        $this->assertEquals('success', session('toast.type'));
    }

    public function test_user_can_delete_mata_kuliah_and_redirects_back(): void
    {
        Http::fake([
            '*/api/mata-kuliah/99' => Http::response([
                'success' => true,
                'message' => 'Data mata kuliah berhasil dihapus.',
            ], 200),
        ]);

        $previousUrl = route('mata-kuliah.index', ['page' => 2]);

        $response = $this->from($previousUrl)->withSession([
            'access_token' => $this->getFakeToken(),
        ])->delete(route('mata-kuliah.destroy', 99));

        $response->assertRedirect($previousUrl);
        $response->assertSessionHas('toast');
        $this->assertEquals('success', session('toast.type'));
    }

    public function test_user_deleting_mata_kuliah_from_show_page_redirects_to_index(): void
    {
        Http::fake([
            '*/api/mata-kuliah/99' => Http::response([
                'success' => true,
                'message' => 'Data mata kuliah berhasil dihapus.',
            ], 200),
        ]);

        $previousUrl = route('mata-kuliah.show', 99);

        $response = $this->from($previousUrl)->withSession([
            'access_token' => $this->getFakeToken(),
        ])->delete(route('mata-kuliah.destroy', 99));

        $response->assertRedirect(route('mata-kuliah.index'));
        $response->assertSessionHas('toast');
        $this->assertEquals('success', session('toast.type'));
    }
}
