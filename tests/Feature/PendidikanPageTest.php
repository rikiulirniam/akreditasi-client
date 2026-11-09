<?php

namespace Tests\Feature;

use Illuminate\Http\Client\Request as ClientRequest;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PendidikanPageTest extends TestCase
{
    public function test_unauthenticated_redirects_to_login(): void
    {
        $response = $this->get(route('pendidikan.index'));

        $this->assertTrue(in_array($response->status(), [200, 302], true));
    }

    public function test_index_displays_pendidikan_with_clean_non_duplicate_jenjang_options(): void
    {
        Http::fake([
            'https://tridharma.fayra.my.id/api/pendidikan*' => Http::response([
                'success' => true,
                'data' => [
                    'current_page' => 1,
                    'data' => [
                        [
                            'id_pendidikan' => 1,
                            'universitas' => 'Institut Teknologi Bandung',
                            'program_studi' => 'Teknik Elektro dan Informatika',
                            'jenjang' => 'S2 Terapan',
                            'tahun_lulus' => 2020,
                            'dosen_count' => 2,
                        ],
                        [
                            'id_pendidikan' => 4,
                            'universitas' => 'Politeknik Negeri Semarang',
                            'program_studi' => 'Teknik Telekomunikasi',
                            'jenjang' => 'D4/S1',
                            'tahun_lulus' => 2018,
                            'dosen_count' => 0,
                        ],
                    ],
                    'from' => 1,
                    'to' => 2,
                    'total' => 2,
                    'per_page' => 10,
                    'last_page' => 1,
                ],
            ]),
            'https://tridharma.fayra.my.id/api/dosen*' => Http::response([
                'success' => true,
                'data' => [
                    ['id_dosen' => 1, 'nama_dosen' => 'Dr. Budi Santoso'],
                ],
            ]),
            'https://tridharma.fayra.my.id/api/tendik*' => Http::response([
                'success' => true,
                'data' => [
                    ['id_tendik' => 1, 'nama_tendik' => 'Ari Mursandi, S.Kom.'],
                ],
            ]),
        ]);

        $response = $this->withSession([
            'access_token' => 'mock-jwt-token',
            'user' => ['id' => 10, 'role' => 'kaprodi', 'nama' => 'Kaprodi Test'],
        ])->get(route('pendidikan.index'));

        $response->assertOk()
            ->assertSee('Pendidikan')
            ->assertSee('2 Data ditemukan')
            ->assertSee('Tambah Pendidikan')
            ->assertDontSee('+ Tambah Pendidikan')
            // Verify clean jenjang options exist
            ->assertSee('>D4/S1<', false)
            ->assertSee('>S2<', false)
            ->assertSee('>S3<', false)
            ->assertSee('>D3<', false)
            // Verify Profesi, Spesialis, and old S1 are eliminated from option list
            ->assertDontSee('>Profesi<', false)
            ->assertDontSee('>Spesialis<', false)
            ->assertDontSee('>S1<', false)
            ->assertDontSee('>S2 Terapan<', false)
            ->assertDontSee('>S3 Terapan<', false);
    }

    public function test_dosen_user_only_sees_their_own_education(): void
    {
        Http::fake([
            'https://tridharma.fayra.my.id/api/dosen*' => Http::response([
                'success' => true,
                'data' => [
                    [
                        'id_dosen' => 2,
                        'nama_dosen' => 'Dr. Ir. Ahmad Dahlan, M.T.',
                        'email' => 'ahmad.dahlan@polines.ac.id',
                        'id_pendidikan' => 2,
                        'pendidikan' => [
                            'id_pendidikan' => 2,
                            'universitas' => 'Universitas Gadjah Mada',
                            'program_studi' => 'Ilmu Komputer',
                            'jenjang' => 'S2',
                            'tahun_lulus' => 2021,
                        ],
                    ],
                ],
            ]),
            'https://tridharma.fayra.my.id/api/pendidikan/2' => Http::response([
                'success' => true,
                'data' => [
                    'id_pendidikan' => 2,
                    'universitas' => 'Universitas Gadjah Mada',
                    'program_studi' => 'Ilmu Komputer',
                    'jenjang' => 'S2',
                    'tahun_lulus' => 2021,
                ],
            ]),
            'https://tridharma.fayra.my.id/api/pendidikan*' => Http::response([
                'success' => true,
                'data' => [
                    [
                        'id_pendidikan' => 1,
                        'universitas' => 'Institut Teknologi Bandung',
                        'program_studi' => 'Teknik Elektro',
                        'jenjang' => 'S2',
                        'tahun_lulus' => 2020,
                    ],
                    [
                        'id_pendidikan' => 2,
                        'universitas' => 'Universitas Gadjah Mada',
                        'program_studi' => 'Ilmu Komputer',
                        'jenjang' => 'S2',
                        'tahun_lulus' => 2021,
                    ],
                ],
            ]),
        ]);

        $response = $this->withSession([
            'access_token' => 'mock-jwt-token',
            'user' => [
                'id' => 2,
                'role' => 'dosen',
                'nama' => 'Dr. Ir. Ahmad Dahlan, M.T.',
                'email' => 'ahmad.dahlan@polines.ac.id',
            ],
        ])->get(route('pendidikan.index'));

        $response->assertOk()
            ->assertSee('Akun Dosen')
            ->assertSee('Dr. Ir. Ahmad Dahlan, M.T.')
            ->assertSee('Riwayat Pendidikan Saya')
            ->assertSee('1 Data ditemukan')
            ->assertSee('Universitas Gadjah Mada')
            // Should NOT see another dosen's education
            ->assertDontSee('Institut Teknologi Bandung');
    }

    public function test_dosen_cannot_edit_or_delete_other_peoples_education(): void
    {
        Http::fake([
            'https://tridharma.fayra.my.id/api/dosen*' => Http::response([
                'success' => true,
                'data' => [
                    [
                        'id_dosen' => 2,
                        'nama_dosen' => 'Dr. Ir. Ahmad Dahlan, M.T.',
                        'email' => 'ahmad.dahlan@polines.ac.id',
                        'id_pendidikan' => 2,
                    ],
                ],
            ]),
        ]);

        // Attempting to update education id 1 (which belongs to someone else)
        $updateResp = $this->withSession([
            'access_token' => 'mock-jwt-token',
            'user' => [
                'id' => 2,
                'role' => 'dosen',
                'email' => 'ahmad.dahlan@polines.ac.id',
            ],
        ])->put(route('pendidikan.update', 1), [
            'universitas' => 'Hacked University',
            'program_studi' => 'Cyber',
            'jenjang' => 'S2',
            'tahun_lulus' => 2020,
        ]);

        $updateResp->assertSessionHas('toast');
        $toast = session('toast');
        $this->assertEquals('error', $toast['type']);
        $this->assertStringContainsString('milik Anda sendiri', $toast['message']);

        // Attempting to delete education id 1 (which belongs to someone else)
        $deleteResp = $this->withSession([
            'access_token' => 'mock-jwt-token',
            'user' => [
                'id' => 2,
                'role' => 'dosen',
                'email' => 'ahmad.dahlan@polines.ac.id',
            ],
        ])->delete(route('pendidikan.destroy', 1));

        $deleteResp->assertSessionHas('toast');
        $delToast = session('toast');
        $this->assertEquals('error', $delToast['type']);
        $this->assertStringContainsString('milik Anda sendiri', $delToast['message']);
    }

    public function test_dosen_store_automatically_links_to_their_dosen_profile(): void
    {
        Http::fake([
            'https://tridharma.fayra.my.id/api/dosen*' => Http::response([
                'success' => true,
                'data' => [
                    [
                        'id_dosen' => 2,
                        'nama_dosen' => 'Dr. Ir. Ahmad Dahlan, M.T.',
                        'email' => 'ahmad.dahlan@polines.ac.id',
                        'id_pendidikan' => null,
                    ],
                ],
            ]),
            'https://tridharma.fayra.my.id/api/pendidikan' => Http::response([
                'success' => true,
                'data' => [
                    'id_pendidikan' => 10,
                    'universitas' => 'Universitas Indonesia',
                    'program_studi' => 'Ilmu Komputer',
                    'jenjang' => 'S3',
                    'tahun_lulus' => 2024,
                ],
            ], 201),
            'https://tridharma.fayra.my.id/api/dosen/2' => Http::response([
                'success' => true,
                'message' => 'Data dosen berhasil diperbarui.',
            ], 200),
        ]);

        $response = $this->withSession([
            'access_token' => 'mock-jwt-token',
            'user' => [
                'id' => 2,
                'role' => 'dosen',
                'nama' => 'Dr. Ir. Ahmad Dahlan, M.T.',
                'email' => 'ahmad.dahlan@polines.ac.id',
            ],
        ])->post(route('pendidikan.store'), [
            'universitas' => 'Universitas Indonesia',
            'program_studi' => 'Ilmu Komputer',
            'jenjang' => 'S3',
            'tahun_lulus' => 2024,
        ]);

        $response->assertRedirect(route('pendidikan.index'))
            ->assertSessionHas('toast');

        Http::assertSent(fn (ClientRequest $request): bool => $request->method() === 'PUT'
            && $request->url() === 'https://tridharma.fayra.my.id/api/dosen/2'
            && (int) $request['id_pendidikan'] === 10);
    }

    public function test_store_from_dosen_context_links_new_education_and_preserves_context(): void
    {
        Http::fake([
            'https://tridharma.fayra.my.id/api/pendidikan' => Http::response([
                'success' => true,
                'data' => ['id_pendidikan' => 15],
            ], 201),
            'https://tridharma.fayra.my.id/api/dosen/7' => Http::response([
                'success' => true,
            ]),
        ]);

        $response = $this->withSession([
            'access_token' => 'mock-jwt-token',
            'user' => ['role' => 'kaprodi'],
        ])->post(route('pendidikan.store'), [
            'universitas' => 'Universitas Negeri Semarang',
            'program_studi' => 'Pendidikan Informatika',
            'jenjang' => 'S2',
            'tahun_lulus' => 2024,
            'id_dosen' => 7,
        ]);

        $response->assertRedirect(route('pendidikan.index', ['id_dosen' => 7]));

        Http::assertSent(fn (ClientRequest $request): bool => $request->method() === 'PUT'
            && $request->url() === 'https://tridharma.fayra.my.id/api/dosen/7'
            && (int) $request['id_pendidikan'] === 15);
    }

    public function test_filter_matches_normalized_jenjang(): void
    {
        Http::fake([
            'https://tridharma.fayra.my.id/api/pendidikan*' => Http::response([
                'success' => true,
                'data' => [
                    [
                        'id_pendidikan' => 1,
                        'universitas' => 'ITB',
                        'program_studi' => 'Informatika',
                        'jenjang' => 'S2 Terapan',
                        'tahun_lulus' => 2020,
                    ],
                    [
                        'id_pendidikan' => 2,
                        'universitas' => 'Polines',
                        'program_studi' => 'Telekomunikasi',
                        'jenjang' => 'D4/S1',
                        'tahun_lulus' => 2018,
                    ],
                ],
            ]),
            'https://tridharma.fayra.my.id/api/dosen*' => Http::response([
                'success' => true,
                'data' => [],
            ]),
            'https://tridharma.fayra.my.id/api/tendik*' => Http::response([
                'success' => true,
                'data' => [],
            ]),
        ]);

        // When filtering by 'S1', it should match 'D4/S1'
        $responseS1 = $this->withSession([
            'access_token' => 'mock-jwt-token',
            'user' => ['role' => 'kaprodi'],
        ])->get(route('pendidikan.index', ['jenjang' => 'S1']));

        $responseS1->assertOk()
            ->assertSee('Polines')
            ->assertDontSee('ITB');

        // When filtering by 'S2', it should match 'S2 Terapan'
        $responseS2 = $this->withSession([
            'access_token' => 'mock-jwt-token',
            'user' => ['role' => 'kaprodi'],
        ])->get(route('pendidikan.index', ['jenjang' => 'S2']));

        $responseS2->assertOk()
            ->assertSee('ITB')
            ->assertDontSee('Polines');
    }
}
