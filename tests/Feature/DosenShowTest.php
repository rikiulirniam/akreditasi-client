<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
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
        $response->assertSee('Tambah Pendidikan');
        $response->assertSee(route('pendidikan.index', ['id_dosen' => 1]), false);
        $response->assertSee('Dr. Budi Santoso, M. Kom.');
        $response->assertSee('Lektor Kepala');
        $response->assertSee('Teknik Elektro');
        $response->assertSee('D4 - Teknologi Rekayasa Komputer');
        $response->assertSee('Budisantoso@gmail.com');
        $response->assertSee('Aktif');
        $response->assertSee('Lihat sertifikasi');
        $response->assertSee(e(route('sertifikasi.index', ['tipe_pemilik' => 'dosen', 'id_dosen' => 1])), false);
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

    public function test_dosen_create_and_edit_forms_offer_unique_higher_education_options(): void
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
            '*/pendidikan*' => Http::response([
                'data' => [
                    ['id_pendidikan' => 1, 'nama_pendidikan' => 'S2'],
                    ['id_pendidikan' => 2, 'nama_pendidikan' => 'S2'],
                    ['id_pendidikan' => 3, 'nama_pendidikan' => 'S3'],
                    ['id_pendidikan' => 4, 'nama_pendidikan' => 'S1'],
                ],
            ], 200),
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

        $response->assertOk();
        $html = $response->getContent();

        $this->assertSame(1, substr_count($html, 'id="createPendidikanSelect"'));
        $this->assertSame(2, substr_count($html, '>S2</option>'));
        $this->assertSame(2, substr_count($html, '>S3</option>'));
        $this->assertSame(2, substr_count($html, '>S1</option>'));
        $this->assertStringContainsString('id="editPendidikanSelect"', $html);
    }

    public function test_dosen_index_shows_empty_state_inside_the_table_and_keeps_page_actions_available(): void
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
        ])->get(route('dosen.index'));

        $response->assertOk()
            ->assertSee('Belum ada data dosen')
            ->assertSee('id="dosen-table" class="w-full min-w-[1050px] table-fixed text-left border-collapse"', false)
            ->assertSee('mt-7 min-w-0 overflow-x-auto', false)
            ->assertSee('openImportModalBtnTable')
            ->assertSee('openCreateModalBtnTable')
            ->assertSee('Download Template Excel');
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

    public function test_jabatan_and_education_controls_are_available_in_create_and_edit_forms(): void
    {
        Http::fake([
            '*/dosen*' => Http::response([
                'success' => true,
                'data' => [
                    'data' => [[
                        'id_dosen' => 10,
                        'nama_dosen' => 'Dosen Uji',
                        'nidn' => '0012345678',
                        'status_dosen' => 'DTPS',
                        'email' => 'dosen@example.com',
                        'id_jabatan' => 4,
                        'id_pendidikan' => 6,
                    ]],
                    'total' => 1,
                    'per_page' => 10,
                    'current_page' => 1,
                    'last_page' => 1,
                ],
            ]),
            '*/jabatan-akademik*' => Http::response([
                'data' => [['id_jabatan' => 4, 'nama_jabatan' => 'Lektor']],
            ]),
            '*/pendidikan*' => Http::response(['data' => []]),
            '*/bidang-keahlian*' => Http::response(['data' => []]),
        ]);

        foreach (['Admin', 'Administrator', 'reviewer', 'Kaprodi'] as $role) {
            $response = $this->withSession([
                'access_token' => 'dummy-token',
                'user' => ['nama' => 'Pengguna', 'role' => $role],
            ])->get(route('dosen.index'));

            $response->assertOk()
                ->assertSee('Jabatan Akademik')
                ->assertSee('id="createJabatanSelect"', false)
                ->assertSee('id="editJabatanSelect"', false)
                ->assertSee('Lektor');
        }

        $dosenResponse = $this->withSession([
            'access_token' => 'dummy-token',
            'user' => ['nama' => 'Dosen', 'role' => 'Dosen'],
        ])->get(route('dosen.index'));

        $dosenResponse->assertOk()
            ->assertDontSee('id="createJabatanSelect"', false)
            ->assertDontSee('id="editJabatanSelect"', false);

        $response->assertSee('id="createPendidikanSelect"', false)
            ->assertSee('id="editPendidikanSelect"', false)
            ->assertSee('data-id-jabatan="4"', false)
            ->assertSee('data-id-pendidikan="6"', false);
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
                && ! array_key_exists('sertifikasi', $data)
                && ($data['nama_dosen'] ?? null) === 'Dr. Hendra Gunawan';
        });
    }

    public function test_dosen_update_allows_missing_education_id(): void
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
        ]);

        $response->assertRedirect(route('dosen.index'));

        Http::assertSent(fn ($request): bool => $request->method() === 'PUT'
            && str_contains($request->url(), '/dosen/10')
            && ($request->data()['status_dosen'] ?? null) === 'Tidak Tetap'
            && ($request->data()['id_pendidikan'] ?? null) === null);
    }

    public function test_dosen_update_preserves_existing_education_id_when_status_changes(): void
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
            'id_pendidikan' => 6,
        ]);

        $response->assertRedirect(route('dosen.index'));

        Http::assertSent(fn ($request): bool => $request->method() === 'PUT'
            && str_contains($request->url(), '/dosen/10')
            && ($request->data()['status_dosen'] ?? null) === 'Tidak Tetap'
            && ($request->data()['id_pendidikan'] ?? null) === 6);
    }

    public function test_dosen_update_logs_tridharma_response_without_sensitive_data(): void
    {
        Http::fake([
            '*/dosen/10' => Http::response([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => ['status_dosen' => ['Status tidak valid']],
                'data' => ['nama_dosen' => 'Jangan catat data ini'],
            ], 422),
        ]);

        Log::shouldReceive('debug')
            ->once()
            ->with('Tridharma dosen update', \Mockery::on(fn (array $context): bool => $context['http_status'] === 422
                && $context['requested_status'] === 'Tidak Tetap'
                && $context['api_message'] === 'Validasi gagal'
                && $context['api_error_fields'] === ['status_dosen']
                && ! array_key_exists('api_data', $context)));

        $response = $this->withSession([
            'access_token' => 'dummy-token',
            'user' => ['nama' => 'Kuwat', 'role' => 'Kaprodi'],
        ])->put(route('dosen.update', 10), [
            'nama_dosen' => 'Dr. Hendra Gunawan, M.T.',
            'nidn' => '0029088401',
            'status_dosen' => 'Tidak Tetap',
            'email' => 'hendra.updated@example.com',
            'id_pendidikan' => 1,
        ]);

        $response->assertRedirect();
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

    public function test_dosen_index_status_filter_submits_dtt_for_tidak_tetap_and_drops_non_active(): void
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
            'user' => ['nama' => 'Kuwat', 'role' => 'Kaprodi'],
        ])->get(route('dosen.index'));

        $response->assertOk()
            ->assertSee('data-value="DTT"', false)
            ->assertSee('data-label="Tidak tetap"', false)
            ->assertDontSee('data-value="Non-aktif"', false);
    }

    public function test_dosen_index_shows_friendly_label_for_selected_tidak_tetap_filter(): void
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
            'user' => ['nama' => 'Kuwat', 'role' => 'Kaprodi'],
        ])->get(route('dosen.index', ['status' => 'DTT']));

        $response->assertOk();
        $html = preg_replace('/\s+/', ' ', $response->getContent());
        $this->assertStringContainsString(
            '<span id="filterStatusLabel" class="text-sm font-semibold text-gray-900 truncate pr-2"> Tidak tetap </span>',
            $html
        );
        $this->assertStringContainsString('name="status" id="filterStatusSelect" value="DTT"', $html);
    }

    public function test_dosen_index_normalizes_tidak_tetap_status_query_to_dtt(): void
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

        $this->withSession([
            'access_token' => 'dummy-token',
            'user' => ['nama' => 'Kuwat', 'role' => 'Kaprodi'],
        ])->get(route('dosen.index', ['status' => 'Tidak Tetap']))->assertOk();

        Http::assertSent(function ($request) {
            if (! str_contains($request->url(), '/dosen')) {
                return false;
            }
            $query = [];
            parse_str(parse_url($request->url(), PHP_URL_QUERY) ?? '', $query);

            return ($query['status'] ?? null) === 'DTT';
        });
    }

    public function test_dosen_template_download_serves_xlsx_workbook(): void
    {
        $response = $this->withSession([
            'access_token' => 'dummy-token',
            'user' => ['nama' => 'Kuwat', 'role' => 'Kaprodi'],
        ])->get(route('dosen.template'));

        $response->assertOk();
        $this->assertSame(
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            $response->headers->get('content-type')
        );
        $this->assertStringContainsString('template_data_dosen.xlsx', (string) $response->headers->get('content-disposition'));
        $this->assertSame('PK', substr($response->streamedContent(), 0, 2));
    }

    public function test_dosen_export_proxies_authenticated_xlsx_response(): void
    {
        Http::fake([
            '*/dosen/export' => Http::response('xlsx-bytes', 200, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename=dosen.xlsx',
            ]),
        ]);

        $response = $this->withSession([
            'access_token' => 'test-export-token',
            'user' => ['nama' => 'Kuwat', 'role' => 'Kaprodi'],
        ])->get(route('dosen.export'));

        $response->assertOk()
            ->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->assertHeader('Content-Disposition', 'attachment; filename=dosen.xlsx')
            ->assertContent('xlsx-bytes');

        Http::assertSent(fn ($request): bool => str_ends_with($request->url(), '/dosen/export')
            && $request->hasHeader('Authorization', 'Bearer test-export-token'));
    }

    public function test_dosen_ajax_store_returns_success_and_sends_relation_ids_and_manual_fields(): void
    {
        Http::fake([
            '*/dosen' => Http::response([
                'success' => true,
                'message' => 'Data dosen berhasil ditambahkan.',
                'data' => ['id_dosen' => 10],
            ], 201),
        ]);

        $response = $this->withSession([
            'access_token' => 'dummy-token',
            'user' => ['nama' => 'Kuwat', 'role' => 'Kaprodi'],
        ])->postJson(route('dosen.store'), [
            'nama_dosen' => 'Dr. Hendra Gunawan',
            'nidn' => '0029088401',
            'nuptk' => '9912345678000001',
            'status_dosen' => 'DTT',
            'email' => 'hendra@example.com',
            'sertifikasi_dosen' => 'Tersertifikasi',
            'id_jabatan' => '4',
            'id_pendidikan' => '3',
        ]);

        $response->assertCreated()
            ->assertJsonPath('success', true);

        Http::assertSent(fn ($request): bool => $request->method() === 'POST'
            && str_ends_with(parse_url($request->url(), PHP_URL_PATH), '/dosen')
            && $request->data()['nuptk'] === '9912345678000001'
            && $request->data()['id_jabatan'] === 4
            && $request->data()['id_pendidikan'] === 3
            && $request->data()['sertifikasi_dosen'] === 'Tersertifikasi');
    }

    public function test_dosen_ajax_update_returns_backend_field_validation_errors(): void
    {
        Http::fake([
            '*/dosen/10' => Http::response([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors' => ['id_pendidikan' => ['Pendidikan terakhir tidak valid.']],
            ], 422),
        ]);

        $response = $this->withSession([
            'access_token' => 'dummy-token',
            'user' => ['nama' => 'Kuwat', 'role' => 'Kaprodi'],
        ])->putJson(route('dosen.update', 10), [
            'nama_dosen' => 'Dr. Hendra Gunawan',
            'nidn' => '0029088401',
            'nuptk' => '',
            'status_dosen' => 'DTPS',
            'email' => 'hendra@example.com',
            'sertifikasi_dosen' => 'Belum Tersertifikasi',
            'id_jabatan' => '4',
            'id_pendidikan' => '999',
        ]);

        $response->assertUnprocessable()
            ->assertJsonPath('errors.id_pendidikan.0', 'Pendidikan terakhir tidak valid.');
    }

    public function test_dosen_sertifikasi_route_redirects_and_filters_certifications_by_id_dosen(): void
    {
        $redirectResponse = $this->withSession([
            'access_token' => 'dummy-token',
            'user' => ['nama' => 'Kuwat', 'role' => 'Kaprodi'],
        ])->get(route('dosen.sertifikasi.index', 1));

        $redirectResponse->assertRedirect(route('sertifikasi.index', ['tipe_pemilik' => 'dosen', 'id_dosen' => 1]));

        Http::fake([
            'https://tridharma.fayra.my.id/api/dosen/1*' => Http::response([
                'success' => true,
                'data' => [
                    'id_dosen' => 1,
                    'nama_dosen' => 'Dr. Budi Santoso',
                ],
            ], 200),
            'https://tridharma.fayra.my.id/api/sertifikasi*' => Http::response([
                'success' => true,
                'data' => [
                    'data' => [
                        [
                            'id' => 10,
                            'id_dosen' => 1,
                            'jenis_sertifikasi' => 'Sertifikasi Pendidik Dosen 1',
                            'nomor_sertifikat' => 'CERT-DOSEN-1',
                            'status_sertifikasi' => 'Aktif',
                            'tahun_sertifikasi' => '2024',
                        ],
                        [
                            'id' => 20,
                            'id_dosen' => 2,
                            'jenis_sertifikasi' => 'Sertifikasi Dosen Lain',
                            'nomor_sertifikat' => 'CERT-DOSEN-2',
                            'status_sertifikasi' => 'Aktif',
                            'tahun_sertifikasi' => '2024',
                        ],
                    ],
                    'current_page' => 1,
                    'last_page' => 1,
                    'per_page' => 10,
                    'total' => 2,
                ],
            ], 200),
        ]);

        $response = $this->withSession([
            'access_token' => 'dummy-token',
            'user' => ['nama' => 'Kuwat', 'role' => 'Kaprodi'],
        ])->get(route('sertifikasi.index', ['tipe_pemilik' => 'dosen', 'id_dosen' => 1]));

        $response->assertOk();
        $response->assertSee('Sertifikasi Pendidik');
        $response->assertSee('CERT-DOSEN-1');
        $response->assertDontSee('CERT-DOSEN-2');
        $response->assertSee(route('dosen.show', 1));
    }
}
