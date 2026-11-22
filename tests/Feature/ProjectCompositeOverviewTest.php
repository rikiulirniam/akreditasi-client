<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ProjectCompositeOverviewTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        config(['services.akreditasi.base_url' => 'https://akreditasi.fayra.my.id']);
    }

    public function test_project_detail_renders_the_composite_overview_contract(): void
    {
        Http::fake([
            'https://akreditasi.fayra.my.id/api/v1/projects/42/composite-overview' => Http::response([
                'success' => true,
                'data' => [
                    'profil_project' => [
                        'id' => 42,
                        'nama_project' => 'Akreditasi Informatika 2026',
                        'program_studi' => ['nama_prodi' => 'Teknik Informatika'],
                        'status_project' => 'in_progress',
                        'tahun_target_submit' => 2026,
                        'versi_instrumen' => ['nomor_versi' => '3.0'],
                        'jenjang' => ['nama_jenjang' => 'Sarjana'],
                        'jenis_instrumen' => ['nama_jenis' => 'LAM-INFOKOM'],
                    ],
                    'progress' => [
                        'project_id' => 42,
                        'kriteria' => 2,
                        'total_indikator' => 3,
                        'indikator_terisi_bukti' => 2,
                        'progres_total_persen' => 66.67,
                    ],
                    'progress_9_kriteria' => [
                        [
                            'id' => 1,
                            'kode_kriteria' => 'K1',
                            'nama_kriteria' => 'Tata Kelola',
                            'total_indikator' => 2,
                            'indikator_terisi_bukti' => 1,
                            'progres_persen' => 50,
                        ],
                        [
                            'id' => 2,
                            'kode_kriteria' => 'K2',
                            'nama_kriteria' => 'Pendidikan',
                            'total_indikator' => 1,
                            'indikator_terisi_bukti' => 1,
                            'progres_persen' => 100,
                        ],
                    ],
                    'ringkasan_evidence_per_kriteria' => [
                        [
                            'id_kriteria' => 1,
                            'kode_kriteria' => 'K1',
                            'nama_kriteria' => 'Tata Kelola',
                            'jumlah_evidence' => 4,
                            'total_indikator' => 2,
                        ],
                    ],
                    'simulasi_skor' => [
                        'total_skor' => 3.5,
                        'predikat' => 'Baik',
                        'threshold_kriteria_d' => 2.0,
                        'jumlah_total' => 3,
                        'jumlah_dinilai' => 2,
                        'jumlah_belum_dinilai' => 1,
                        'persentase_kelengkapan' => 66.67,
                    ],
                ],
            ]),
            'https://akreditasi.fayra.my.id/api/v1/mapping-tim*' => Http::response([
                'success' => true,
                'data' => [
                    ['id_project' => 42, 'id_indikator' => 11, 'role' => 'PIC', 'user' => ['nama' => 'PIC Project']],
                    ['id_project' => 99, 'id_indikator' => 12, 'role' => 'PIC', 'user' => ['nama' => 'PIC Project Lain']],
                ],
            ]),
            'https://akreditasi.fayra.my.id/api/v1/master/indikator' => Http::response([
                'success' => true,
                'data' => [
                    ['id' => 11, 'kode_indikator' => 'K1'],
                    ['id' => 12, 'kode_indikator' => 'K2'],
                ],
            ]),
            '*' => Http::response(['success' => true, 'data' => []]),
        ]);

        $response = $this->withSession([
            'access_token' => 'kaprodi-token',
            'user' => ['nama' => 'Kaprodi', 'role' => 'KAPRODI'],
            'plotting_assignments_42' => [
                'K1' => ['pj' => null, 'pic' => 'PIC Session', 'id_indikator' => '11'],
            ],
        ])->get(route('kaprodi.project.detail', 42));

        $response->assertOk()
            ->assertSee('Akreditasi Informatika 2026')
            ->assertSee('Teknik Informatika')
            ->assertSee('Sarjana')
            ->assertSee('3.0')
            ->assertSee('66.67%')
            ->assertSee('Identitas project')
            ->assertSee('K1')
            ->assertSee('50%')
            ->assertSee('K2')
            ->assertSee('100%')
            ->assertSee('Tata Kelola')
            ->assertSee('4 evidence')
            ->assertSee('3.5')
            ->assertSee('PIC')
            ->assertSee('Indikator seluruh project dengan PIC')
            ->assertSee('Indikator seluruh project tanpa PIC')
            ->assertSee('modal Readiness Check menghitung subset indikator kritis')
            ->assertDontSee('Kriteria 3');

        $this->assertStringContainsString(
            'Indikator seluruh project dengan PIC</dt><dd class="mt-1 font-bold text-slate-700">1</dd>',
            $response->getContent(),
        );
        $this->assertStringContainsString(
            'Indikator seluruh project tanpa PIC</dt><dd class="mt-1 font-bold text-slate-700">2</dd>',
            $response->getContent(),
        );

        Http::assertSent(fn ($request): bool => $request->url() === 'https://akreditasi.fayra.my.id/api/v1/projects/42/composite-overview');
        Http::assertSent(fn ($request): bool => str_contains($request->url(), '/mapping-tim')
            && str_contains($request->url(), 'project_id=42'));
    }

    public function test_readiness_pic_count_uses_distinct_project_mapping_for_critical_indicators(): void
    {
        Http::fake([
            'https://akreditasi.fayra.my.id/api/v1/projects/42/readiness' => Http::response([
                'success' => true,
                'data' => [
                    'is_ready' => true,
                    'total_indikator' => 2,
                    'ready_indikator' => 2,
                    'unready_indikator' => 0,
                    'percentage' => 100,
                    'missing_pic_count' => 0,
                    'indicators' => [
                        [
                            'id_indikator' => 11,
                            'kode_indikator' => 'C.1',
                            'has_pic' => false,
                            'has_evidence' => true,
                            'has_assessment' => true,
                            'is_ready' => true,
                            'missing_items' => [],
                        ],
                        [
                            'id_indikator' => 12,
                            'kode_indikator' => 'C.2',
                            'has_pic' => true,
                            'has_evidence' => true,
                            'has_assessment' => true,
                            'is_ready' => true,
                            'missing_items' => [],
                        ],
                    ],
                ],
            ]),
            'https://akreditasi.fayra.my.id/api/v1/master/indikator' => Http::response([
                'success' => true,
                'data' => [
                    ['id' => 11, 'kode_indikator' => 'C.1'],
                    ['id' => 12, 'kode_indikator' => 'C.2'],
                ],
            ]),
            'https://akreditasi.fayra.my.id/api/v1/mapping-tim*' => Http::response([
                'success' => true,
                'data' => [
                    ['id_project' => 42, 'id_indikator' => 11, 'role' => 'PIC', 'user' => ['nama' => 'PIC Aktif']],
                    ['id_project' => 99, 'id_indikator' => 12, 'role' => 'PIC', 'user' => ['nama' => 'PIC Project Lain']],
                ],
            ]),
            '*' => Http::response(['success' => true, 'data' => []]),
        ]);

        $response = $this->withSession([
            'access_token' => 'kaprodi-token',
            'user' => ['nama' => 'Kaprodi', 'role' => 'KAPRODI'],
            'plotting_assignments_42' => [
                'C.1' => ['pj' => null, 'pic' => 'PIC Session', 'id_indikator' => '11'],
            ],
        ])->get(route('kaprodi.project.readiness', 42));

        $response->assertOk()
            ->assertJsonPath('data.pic_data_available', true)
            ->assertJsonPath('data.missing_pic_count', 1)
            ->assertJsonPath('data.indicators.0.has_pic', true)
            ->assertJsonPath('data.indicators.1.has_pic', false)
            ->assertJsonPath('data.scope_label', 'Butir indikator kritis dari endpoint readiness');
    }

    public function test_readiness_normalizes_critical_indicators_response_without_losing_total_count(): void
    {
        Http::fake([
            'https://akreditasi.fayra.my.id/api/v1/projects/42/readiness' => Http::response([
                'success' => true,
                'data' => [
                    'project_id' => 42,
                    'total_indikator_kritis' => 4,
                    'indikator_belum_siap' => [
                        [
                            'id_indikator' => 11,
                            'kode_indikator' => 'IND-A.1',
                            'deskripsi_indikator' => 'Indikator pertama',
                            'belum_terpenuhi' => ['link_evidence', 'skor_reviewer'],
                        ],
                        [
                            'id_indikator' => 12,
                            'kode_indikator' => 'IND-A.2',
                            'deskripsi_indikator' => 'Indikator kedua',
                            'belum_terpenuhi' => ['pic', 'link_evidence', 'skor_reviewer'],
                        ],
                        [
                            'id_indikator' => 13,
                            'kode_indikator' => 'IND-A.3',
                            'deskripsi_indikator' => 'Indikator ketiga',
                            'belum_terpenuhi' => ['skor_reviewer'],
                        ],
                    ],
                ],
            ]),
            'https://akreditasi.fayra.my.id/api/v1/master/indikator' => Http::response([
                'success' => true,
                'data' => [
                    ['id' => 11, 'kode_indikator' => 'IND-A.1'],
                    ['id' => 12, 'kode_indikator' => 'IND-A.2'],
                    ['id' => 13, 'kode_indikator' => 'IND-A.3'],
                ],
            ]),
            'https://akreditasi.fayra.my.id/api/v1/mapping-tim*' => Http::response([
                'success' => true,
                'data' => [
                    ['id_project' => 42, 'id_indikator' => 11, 'role' => 'PIC', 'user' => ['nama' => 'PIC Satu']],
                    ['id_project' => 42, 'id_indikator' => 13, 'role' => 'PIC', 'user' => ['nama' => 'PIC Tiga']],
                ],
            ]),
            '*' => Http::response(['success' => true, 'data' => []]),
        ]);

        $response = $this->withSession([
            'access_token' => 'kaprodi-token',
            'user' => ['nama' => 'Kaprodi', 'role' => 'KAPRODI'],
        ])->get(route('kaprodi.project.readiness', 42));

        $response->assertOk()
            ->assertJsonPath('data.total_indikator', 4)
            ->assertJsonPath('data.ready_indikator', 1)
            ->assertJsonPath('data.unready_indikator', 3)
            ->assertJsonPath('data.percentage', 25)
            ->assertJsonPath('data.missing_pic_count', 1)
            ->assertJsonPath('data.missing_evidence_count', 2)
            ->assertJsonPath('data.missing_assessment_count', 3)
            ->assertJsonPath('data.pic_data_available', true)
            ->assertJsonPath('data.indicators.0.has_pic', true)
            ->assertJsonPath('data.indicators.0.has_evidence', false)
            ->assertJsonPath('data.indicators.1.has_pic', false)
            ->assertJsonPath('data.indicators.2.has_assessment', false)
            ->assertJsonPath('data.indicators.0.deskripsi_indikator', 'Indikator pertama');
    }

    public function test_readiness_upstream_failure_is_not_returned_as_empty_or_fallback_data(): void
    {
        Http::fake([
            'https://akreditasi.fayra.my.id/api/v1/projects/42/readiness' => Http::response([
                'success' => false,
                'message' => 'Upstream unavailable',
            ], 503),
            '*' => Http::response(['success' => true, 'data' => []]),
        ]);

        $response = $this->withSession([
            'access_token' => 'kaprodi-token',
            'user' => ['nama' => 'Kaprodi', 'role' => 'KAPRODI'],
        ])->get(route('kaprodi.project.readiness', 42));

        $response->assertStatus(502)
            ->assertJsonPath('success', false)
            ->assertJsonMissingPath('data');

        Http::assertSentCount(1);
    }

    public function test_readiness_keeps_backend_counts_when_pic_mapping_cannot_be_verified(): void
    {
        Http::fake([
            'https://akreditasi.fayra.my.id/api/v1/projects/42/readiness' => Http::response([
                'success' => true,
                'data' => [
                    'total_indikator_kritis' => 1,
                    'indikator_belum_siap' => [
                        [
                            'id_indikator' => 11,
                            'kode_indikator' => 'IND-A.1',
                            'deskripsi_indikator' => 'Indikator kritis',
                            'belum_terpenuhi' => ['pic', 'link_evidence', 'skor_reviewer'],
                        ],
                    ],
                ],
            ]),
            'https://akreditasi.fayra.my.id/api/v1/master/indikator' => Http::response([
                'success' => true,
                'data' => [['id' => 11, 'kode_indikator' => 'IND-A.1']],
            ]),
            'https://akreditasi.fayra.my.id/api/v1/mapping-tim*' => Http::response([
                'success' => true,
            ]),
            '*' => Http::response(['success' => true, 'data' => []]),
        ]);

        $response = $this->withSession([
            'access_token' => 'kaprodi-token',
            'user' => ['nama' => 'Kaprodi', 'role' => 'KAPRODI'],
        ])->get(route('kaprodi.project.readiness', 42));

        $response->assertOk()
            ->assertJsonPath('data.total_indikator', 1)
            ->assertJsonPath('data.unready_indikator', 1)
            ->assertJsonPath('data.missing_evidence_count', 1)
            ->assertJsonPath('data.pic_data_available', false)
            ->assertJsonPath('data.indicators.0.deskripsi_indikator', 'Indikator kritis');
    }

    public function test_plotting_does_not_guess_indicator_or_user_ids_from_codes(): void
    {
        Http::fake([
            '*' => Http::response(['success' => true, 'data' => []]),
        ]);

        $response = $this->withSession([
            'access_token' => 'kaprodi-token',
            'user' => ['nama' => 'Kaprodi', 'role' => 'KAPRODI'],
        ])->postJson(route('kaprodi.plotting.store'), [
            'project_id' => '42',
            'role' => 'pic',
            'assignments' => [
                ['code' => 'K1.1', 'user_name' => 'Dosen Terpilih'],
            ],
        ]);

        $response->assertUnprocessable()
            ->assertJsonPath('success', false);

        Http::assertNotSent(fn ($request): bool => str_contains($request->url(), '/projects/42/mapping-tim'));
    }

    public function test_project_detail_shows_a_distinct_forbidden_message(): void
    {
        Http::fake([
            'https://akreditasi.fayra.my.id/api/v1/projects/42/composite-overview' => Http::response([
                'success' => false,
                'message' => 'Forbidden',
            ], 403),
        ]);

        $response = $this->withSession([
            'access_token' => 'kaprodi-token',
            'user' => ['nama' => 'Kaprodi', 'role' => 'KAPRODI'],
        ])->get(route('kaprodi.project.detail', 42));

        $response->assertOk()
            ->assertSee('Anda tidak memiliki akses untuk melihat project ini.')
            ->assertDontSee('Progress keseluruhan');
    }
}
