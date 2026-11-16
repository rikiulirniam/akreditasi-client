<?php

namespace Tests\Feature;

use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ReviewerDashboardTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        config(['services.akreditasi.base_url' => 'https://akreditasi.example.test']);
    }

    public function test_dashboard_redirects_reviewer_role_to_reviewer_dashboard(): void
    {
        $response = $this->withSession([
            'access_token' => 'reviewer-token',
            'user' => ['nama' => 'Kuwat', 'role' => 'Reviewer'],
        ])->get(route('dashboard'));

        $response->assertRedirect(route('reviewer.dashboard'));
    }

    public function test_existing_dashboard_remains_available_to_non_reviewer_users(): void
    {
        $response = $this->withSession([
            'access_token' => 'admin-token',
            'user' => ['nama' => 'Kuwat', 'role' => 'Kaprodi'],
        ])->get(route('dashboard'));

        $response->assertOk()
            ->assertSee('PUSAT KENDALI SISTEM')
            ->assertDontSee('PORTAL REVIEWER AKREDITASI');
    }

    public function test_reviewer_dashboard_displays_api_project_data_and_summary(): void
    {
        Http::fake([
            'https://akreditasi.example.test/api/reviewer/projects' => Http::response([
                'success' => true,
                'data' => [
                    ['nama_project' => 'Akreditasi Teknik Informatika', 'program_studi' => 'Teknik Informatika', 'status' => 'Sedang dinilai', 'progress_percentage' => 53],
                    ['nama_project' => 'Akreditasi Sistem Informasi', 'program_studi' => 'Sistem Informasi', 'status' => 'Menunggu', 'progress_percentage' => 0],
                    ['nama_project' => 'Akreditasi Manajemen Informatika', 'program_studi' => 'Manajemen Informatika', 'status' => 'Selesai', 'progress_percentage' => 100],
                ],
            ]),
        ]);

        $response = $this->withSession([
            'access_token' => 'reviewer-token',
            'user' => ['nama' => 'Kuwat', 'role' => 'Reviewer'],
        ])->get(route('reviewer.dashboard'));

        $response->assertOk()
            ->assertSee('Halo, Kuwat!')
            ->assertSee('Akreditasi Teknik Informatika')
            ->assertSee('Project ditugaskan')
            ->assertSee('53%');

        Http::assertSent(fn (HttpRequest $request): bool => $request->url() === 'https://akreditasi.example.test/api/reviewer/projects'
            && $request->hasHeader('Authorization', 'Bearer reviewer-token'));
    }

    public function test_reviewer_uses_the_single_dummy_data_file_when_api_is_unavailable(): void
    {
        Http::fake([
            'https://akreditasi.example.test/api/reviewer/projects' => Http::response([
                'message' => 'Endpoint belum tersedia.',
            ], 403),
        ]);

        $response = $this->withSession([
            'access_token' => 'reviewer-token',
            'user' => ['nama' => 'Kuwat', 'role' => 'Reviewer'],
        ])->get(route('reviewer.assessments'));

        $response->assertOk()
            ->assertSee('Data dummy')
            ->assertSee('Akreditasi Teknik Informatika 2025')
            ->assertSee('Reakreditasi Sistem Informasi 2025')
            ->assertSee('Akreditasi Manajemen Informatika');
    }

    public function test_reviewer_assessments_have_colored_statuses_and_filter_by_status_and_year(): void
    {
        Http::fake([
            'https://akreditasi.example.test/api/reviewer/projects' => Http::response([
                'message' => 'Endpoint belum tersedia.',
            ], 403),
        ]);

        $response = $this->withSession([
            'access_token' => 'reviewer-token',
            'user' => ['nama' => 'Kuwat', 'role' => 'Reviewer'],
        ])->get(route('reviewer.assessments', [
            'status' => 'menunggu',
            'tahun' => '2025',
        ]));

        $response->assertOk()
            ->assertSee('Semua status')
            ->assertSee('Semua tahun')
            ->assertSee('value="menunggu" selected', false)
            ->assertSee('value="2025" selected', false)
            ->assertSee('1 dari 3 project ditugaskan')
            ->assertSee('Reakreditasi Sistem Informasi 2025')
            ->assertDontSee('Akreditasi Teknik Informatika 2025')
            ->assertDontSee('Akreditasi Manajemen Informatika');

        $allStatuses = $this->withSession([
            'access_token' => 'reviewer-token',
            'user' => ['nama' => 'Kuwat', 'role' => 'Reviewer'],
        ])->get(route('reviewer.assessments'));

        $allStatuses->assertOk()
            ->assertSee('bg-blue-50 text-blue-700')
            ->assertSee('bg-amber-50 text-amber-700')
            ->assertSee('bg-emerald-50 text-emerald-700');
    }

    public function test_reviewer_can_open_indicator_detail_and_save_a_score_and_notes(): void
    {
        Http::fake([
            'https://akreditasi.example.test/api/reviewer/projects' => Http::response([
                'message' => 'Endpoint belum tersedia.',
            ], 403),
        ]);

        $session = [
            'access_token' => 'reviewer-token',
            'user' => ['nama' => 'Kuwat', 'role' => 'Reviewer'],
        ];

        $detail = $this->withSession($session)->get(route('reviewer.assessment-detail', [
            'projectId' => 1,
            'indikator' => '2-1-1',
        ]));

        $detail->assertOk()
            ->assertSee('C.2.1.1')
            ->assertSee('Narasi program studi')
            ->assertSee('Bukti pendukung')
            ->assertSee('Panel penilaian')
            ->assertSee('Catatan reviewer')
            ->assertSee('Simpan penilaian');

        $save = $this->withSession($session)->post(route('reviewer.assessment-save', [
            'projectId' => 1,
            'indicatorId' => '2-1-1',
        ]), [
            'score' => 3,
            'notes' => 'Perjelas evaluasi bersama pemangku kepentingan.',
            'evidence_status' => [
                0 => 'Disetujui',
                1 => 'Pending',
                2 => 'Perlu revisi',
            ],
            'evidence_action' => '1:Disetujui',
        ]);

        $save->assertRedirect(route('reviewer.assessment-detail', [
            'projectId' => 1,
            'indikator' => '2-1-1',
        ]));
        $this->assertSame(3, session('reviewer_assessments.1.2-1-1.score'));
        $this->assertSame('Perjelas evaluasi bersama pemangku kepentingan.', session('reviewer_assessments.1.2-1-1.notes'));
        $this->assertSame('Disetujui', session('reviewer_assessments.1.2-1-1.evidence_status.1'));
    }

    public function test_reviewer_cannot_save_a_score_outside_the_rubric(): void
    {
        Http::fake([
            'https://akreditasi.example.test/api/reviewer/projects' => Http::response([
                'message' => 'Endpoint belum tersedia.',
            ], 403),
        ]);

        $response = $this->withSession([
            'access_token' => 'reviewer-token',
            'user' => ['nama' => 'Kuwat', 'role' => 'Reviewer'],
        ])->post(route('reviewer.assessment-save', [
            'projectId' => 1,
            'indicatorId' => '2-1-1',
        ]), [
            'score' => 5,
        ]);

        $response->assertSessionHasErrors('score');
    }

    public function test_reviewer_can_load_dynamic_projects_and_indicators_from_akreditasi_api(): void
    {
        Http::fake([
            'https://akreditasi.example.test/api/reviewer/projects' => Http::response(['message' => 'Not Found'], 404),
            'https://akreditasi.example.test/api/v1/projects' => Http::response([
                'success' => true,
                'data' => [
                    [
                        'id' => 8,
                        'nama_project' => 'Akreditasi Program Studi D3 Teknik Informatika 2026',
                        'tahun_target_submit' => 2026,
                        'status_project' => 'In Progress',
                        'jenjang' => ['nama_jenjang' => 'Program Diploma'],
                        'jenis_instrumen' => ['nama_jenis' => 'LAM-INFOKOM 2.1'],
                    ],
                ],
            ]),
            'https://akreditasi.example.test/api/v1/master/indikator' => Http::response([
                'success' => true,
                'data' => [
                    [
                        'id' => 5,
                        'kode_indikator' => 'IND-C.1.1.1',
                        'deskripsi_indikator' => 'Konsistensi dan efektivitas pelaksanaan siklus PPEPP',
                        'parameter_penilaian' => 'Dokumen standar SPMI dan laporan AMI',
                        'subkriteria' => [
                            'kriteria' => [
                                'kode_kriteria' => 'C1',
                                'nama_kriteria' => 'Tata Pamong',
                            ],
                        ],
                    ],
                ],
            ]),
            'https://akreditasi.example.test/api/v1/master/rubrik*' => Http::response([
                'success' => true,
                'data' => [
                    [
                        'id' => 15,
                        'id_indikator' => 5,
                        'skor' => 4,
                        'deskripsi_rubrik' => 'Sangat Baik: Siklus PPEPP berjalan secara konsisten',
                    ],
                ],
            ]),
            'https://akreditasi.example.test/api/v1/self-assessment*' => Http::response([
                'success' => true,
                'data' => [
                    [
                        'id' => 1,
                        'id_project' => 8,
                        'id_indikator' => 5,
                        'id_rubrik' => 15,
                        'catatan_reviewer' => 'Dokumen bukti sahih sangat lengkap.',
                        'rubrik' => ['skor' => 4],
                    ],
                ],
            ]),
        ]);

        $session = [
            'access_token' => 'reviewer-token',
            'user' => ['id' => 10, 'nama' => 'Kuwat', 'role' => 'Reviewer'],
        ];

        // 1. Dashboard project dynamic display
        $dashboard = $this->withSession($session)->get(route('reviewer.dashboard'));
        $dashboard->assertOk()
            ->assertSee('Akreditasi Program Studi D3 Teknik Informatika 2026')
            ->assertSee('D3 Teknik Informatika');

        // 2. Assessment detail loads dynamic indicators & rubriks
        $detail = $this->withSession($session)->get(route('reviewer.assessment-detail', [
            'projectId' => 8,
            'indikator' => 5,
        ]));

        $detail->assertOk()
            ->assertSee('IND-C.1.1.1')
            ->assertSee('Konsistensi dan efektivitas pelaksanaan siklus PPEPP')
            ->assertSee('Dokumen standar SPMI dan laporan AMI')
            ->assertSee('Sangat Baik: Siklus PPEPP berjalan secara konsisten')
            ->assertSee('Dokumen bukti sahih sangat lengkap.');
    }

    public function test_reviewer_can_save_assessment_and_posts_to_api_self_assessment(): void
    {
        Http::fake([
            'https://akreditasi.example.test/api/reviewer/projects' => Http::response(['message' => 'Not Found'], 404),
            'https://akreditasi.example.test/api/v1/projects' => Http::response([
                'success' => true,
                'data' => [
                    [
                        'id' => 8,
                        'nama_project' => 'Akreditasi Program Studi D3 Teknik Informatika 2026',
                        'tahun_target_submit' => 2026,
                        'status_project' => 'In Progress',
                    ],
                ],
            ]),
            'https://akreditasi.example.test/api/v1/master/indikator' => Http::response([
                'success' => true,
                'data' => [
                    [
                        'id' => 5,
                        'kode_indikator' => 'IND-C.1.1.1',
                        'deskripsi_indikator' => 'Konsistensi dan efektivitas pelaksanaan siklus PPEPP',
                        'parameter_penilaian' => 'Dokumen SPMI',
                    ],
                ],
            ]),
            'https://akreditasi.example.test/api/v1/master/rubrik*' => Http::response([
                'success' => true,
                'data' => [
                    [
                        'id' => 15,
                        'id_indikator' => 5,
                        'skor' => 4,
                        'deskripsi_rubrik' => 'Sangat Baik',
                    ],
                ],
            ]),
            'https://akreditasi.example.test/api/v1/self-assessment*' => Http::response([
                'success' => true,
                'data' => [],
            ]),
        ]);

        $session = [
            'access_token' => 'reviewer-token',
            'user' => ['id' => 10, 'nama' => 'Kuwat', 'role' => 'Reviewer'],
        ];

        $response = $this->withSession($session)->post(route('reviewer.assessment-save', [
            'projectId' => 8,
            'indicatorId' => 5,
        ]), [
            'score' => 4,
            'rubrik_id' => 15,
            'notes' => 'Penilaian dinamis tersimpan ke API.',
        ]);

        $response->assertRedirect(route('reviewer.assessment-detail', [
            'projectId' => 8,
            'indikator' => 5,
        ]));

        $this->assertSame(4, session('reviewer_assessments.8.5.score'));
        $this->assertSame('Penilaian dinamis tersimpan ke API.', session('reviewer_assessments.8.5.notes'));

        Http::assertSent(fn (HttpRequest $request): bool => $request->url() === 'https://akreditasi.example.test/api/v1/self-assessment'
            && $request->method() === 'POST'
            && $request['id_project'] === 8
            && $request['id_indikator'] === 5
            && $request['id_rubrik'] === 15);
    }
}
