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
            'user' => ['nama' => 'Kuwat', 'role' => 'Administrator'],
        ])->get(route('dashboard'));

        $response->assertOk()
            ->assertSee('PUSAT KENDALI SISTEM')
            ->assertDontSee('PORTAL REVIEWER AKREDITASI');
    }

    public function test_reviewer_dashboard_lists_all_projects_that_need_review(): void
    {
        Http::fake([
            'https://akreditasi.example.test/api/v1/projects' => Http::response([
                'success' => true,
                'data' => [
                    [
                        'id' => 1,
                        'nama_project' => 'Akreditasi Teknik Informatika',
                        'tahun_target_submit' => 2025,
                        'status_project' => 'review',
                        'prodi' => ['nama_prodi' => 'Teknik Informatika', 'jenjang' => ['nama_jenjang' => 'S1']],
                        'jenis_instrumen' => ['nama_jenis_instrumen' => 'LAM INFOKOM'],
                        'progress_percentage' => 53,
                    ],
                    [
                        'id' => 2,
                        'nama_project' => 'Akreditasi Sistem Informasi',
                        'tahun_target_submit' => 2025,
                        'status_project' => 'review',
                        'prodi' => ['nama_prodi' => 'Sistem Informasi', 'jenjang' => ['nama_jenjang' => 'S1']],
                        'jenis_instrumen' => ['nama_jenis_instrumen' => 'LAM INFOKOM'],
                    ],
                    [
                        'id' => 3,
                        'nama_project' => 'Project yang masih draft',
                        'tahun_target_submit' => 2025,
                        'status_project' => 'draft',
                    ],
                ],
            ]),
            'https://akreditasi.example.test/api/v1/mapping-tim' => Http::response([
                'data' => [
                    ['id_project' => 1, 'id_user' => 77, 'id_indikator' => 51, 'indikator' => ['id' => 51, 'kode_indikator' => 'C.2.1.1', 'nama_indikator' => 'Visi program studi']],
                    ['id_project' => 2, 'id_user' => 88, 'id_indikator' => 52, 'indikator' => ['id' => 52, 'kode_indikator' => 'C.2.1.2', 'nama_indikator' => 'Misi program studi']],
                    ['id_project' => 3, 'id_user' => 77, 'id_indikator' => 53, 'indikator' => ['id' => 53, 'kode_indikator' => 'C.2.1.3', 'nama_indikator' => 'Indikator draft']],
                ],
            ]),
        ]);

        $response = $this->withSession([
            'access_token' => 'reviewer-token',
            'user' => ['id' => 77, 'nama' => 'Kuwat', 'role' => 'Reviewer'],
        ])->get(route('reviewer.dashboard'));

        $response->assertOk()
            ->assertSee('Halo, Kuwat!')
            ->assertSee('Akreditasi Teknik Informatika')
            ->assertSee('Akreditasi Sistem Informasi')
            ->assertSee('Project perlu direview')
            ->assertSee('53%')
            ->assertDontSee('Project yang masih draft');

        Http::assertSent(fn (HttpRequest $request): bool => $request->url() === 'https://akreditasi.example.test/api/v1/projects'
            && $request->hasHeader('Authorization', 'Bearer reviewer-token'));
        Http::assertSent(fn (HttpRequest $request): bool => $request->url() === 'https://akreditasi.example.test/api/v1/mapping-tim'
            && $request->hasHeader('Authorization', 'Bearer reviewer-token'));
    }

    public function test_legacy_demo_query_does_not_replace_api_project_data(): void
    {
        Http::fake([
            'https://akreditasi.example.test/api/v1/projects' => Http::response([
                'success' => true,
                'data' => [
                    ['id' => 77, 'nama_project' => 'Project asli dari API', 'tahun_target_submit' => 2026, 'status_project' => 'review'],
                ],
            ]),
            'https://akreditasi.example.test/api/v1/mapping-tim' => Http::response([
                'data' => [
                    ['id_project' => 77, 'id_user' => 77, 'id_indikator' => 51],
                ],
            ]),
        ]);

        $response = $this->withSession([
            'access_token' => 'reviewer-token',
            'user' => ['id' => 77, 'nama' => 'Kuwat', 'role' => 'Reviewer'],
        ])->get(route('reviewer.dashboard', ['demo' => 1]));

        $response->assertOk()
            ->assertSee('Project asli dari API')
            ->assertDontSee('DATA DUMMY')
            ->assertDontSee('reviewer-dummy-data.php');
    }

    public function test_reviewer_loads_api_indicators_for_an_assigned_project(): void
    {
        Http::fake([
            'https://akreditasi.example.test/api/v1/projects' => Http::response([
                'success' => true,
                'data' => [
                    ['id' => 9, 'nama_project' => 'Akreditasi Teknik Informatika', 'tahun_target_submit' => 2025, 'status_project' => 'review'],
                ],
            ]),
            'https://akreditasi.example.test/api/v1/mapping-tim' => Http::response([
                'data' => [
                    ['id_project' => 9, 'id_user' => 77, 'id_indikator' => 51],
                ],
            ]),
            'https://akreditasi.example.test/api/v1/master/indikator' => Http::response([
                'success' => true,
                'data' => [
                    ['id' => 51, 'kode_indikator' => 'C.2.1.1', 'nama_indikator' => 'Kesesuaian visi program studi'],
                    ['id' => 52, 'kode_indikator' => 'C.2.1.2', 'nama_indikator' => 'Indikator yang tidak ditugaskan'],
                ],
            ]),
            'https://akreditasi.example.test/api/v1/master/rubrik*' => Http::response(['success' => true, 'data' => []]),
            'https://akreditasi.example.test/api/v1/self-assessment*' => Http::response(['success' => true, 'data' => []]),
        ]);

        $response = $this->withSession([
            'access_token' => 'reviewer-token',
            'user' => ['id' => 77, 'nama' => 'Kuwat', 'role' => 'Reviewer'],
        ])->get(route('reviewer.assessment-detail', ['projectId' => 9]));

        $indicatorRequest = Http::recorded(
            fn (HttpRequest $request): bool => $request->url() === 'https://akreditasi.example.test/api/v1/master/indikator',
        )->first();
        $this->assertNotNull($indicatorRequest);
        $this->assertTrue($indicatorRequest[1]->successful(), $indicatorRequest[1]->body());
        $this->assertNotEmpty($indicatorRequest[1]->json('data'));

        $response->assertOk()
            ->assertSee('C.2.1.1')
            ->assertSee('Kesesuaian visi program studi')
            ->assertDontSee('Indikator yang tidak ditugaskan');

        Http::assertSent(fn (HttpRequest $request): bool => $request->url() === 'https://akreditasi.example.test/api/v1/master/indikator'
            && $request->hasHeader('Authorization', 'Bearer reviewer-token'));
    }

    public function test_reviewer_shows_api_error_instead_of_dummy_projects_when_api_is_unavailable(): void
    {
        Http::fake([
            'https://akreditasi.example.test/api/v1/projects' => Http::response([
                'message' => 'Endpoint belum tersedia.',
            ], 403),
        ]);

        $response = $this->withSession([
            'access_token' => 'reviewer-token',
            'user' => ['nama' => 'Kuwat', 'role' => 'Reviewer'],
        ])->get(route('reviewer.assessments'));

        $response->assertOk()
            ->assertSee('Endpoint belum tersedia.')
            ->assertSee('Belum ada project penilaian')
            ->assertDontSee('Data dummy')
            ->assertDontSee('Akreditasi Teknik Informatika 2025');
    }

    public function test_reviewer_assessments_filter_api_projects_by_year(): void
    {
        Http::fake([
            'https://akreditasi.example.test/api/v1/projects' => Http::response([
                'data' => [
                    ['id' => 10, 'nama_project' => 'Project review 2025', 'tahun_target_submit' => 2025, 'status_project' => 'review'],
                    ['id' => 11, 'nama_project' => 'Project review 2024', 'tahun_target_submit' => 2024, 'status_project' => 'review'],
                    ['id' => 12, 'nama_project' => 'Project draft', 'tahun_target_submit' => 2025, 'status_project' => 'draft'],
                ],
            ]),
            'https://akreditasi.example.test/api/v1/mapping-tim' => Http::response(['data' => []]),
        ]);

        $response = $this->withSession([
            'access_token' => 'reviewer-token',
            'user' => ['nama' => 'Kuwat', 'role' => 'Reviewer'],
        ])->get(route('reviewer.assessments', [
            'status' => 'sedang_dinilai',
            'tahun' => '2025',
        ]));

        $response->assertOk()
            ->assertSee('Semua status')
            ->assertSee('Semua tahun')
            ->assertSee('value="sedang_dinilai" selected', false)
            ->assertSee('value="2025" selected', false)
            ->assertSee('1 dari 2 project perlu direview')
            ->assertSee('Project review 2025')
            ->assertDontSee('Project review 2024')
            ->assertDontSee('Project draft');

        $allStatuses = $this->withSession([
            'access_token' => 'reviewer-token',
            'user' => ['nama' => 'Kuwat', 'role' => 'Reviewer'],
        ])->get(route('reviewer.assessments'));

        $allStatuses->assertOk()
            ->assertSee('bg-blue-50 text-blue-700')
            ->assertDontSee('bg-amber-50 text-amber-700')
            ->assertDontSee('bg-emerald-50 text-emerald-700');
    }

    public function test_reviewer_can_open_indicator_detail_and_save_a_score_and_notes(): void
    {
        $this->fakeReviewProject(1, 51);

        $session = [
            'access_token' => 'reviewer-token',
            'user' => ['id' => 77, 'nama' => 'Kuwat', 'role' => 'Reviewer'],
        ];

        $detail = $this->withSession($session)->get(route('reviewer.assessment-detail', [
            'projectId' => 1,
            'indikator' => 51,
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
            'indicatorId' => 51,
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
            'indikator' => 51,
        ]));
        $this->assertSame(3, session('reviewer_assessments.1.51.score'));
        $this->assertSame('Perjelas evaluasi bersama pemangku kepentingan.', session('reviewer_assessments.1.51.notes'));
        $this->assertSame('Disetujui', session('reviewer_assessments.1.51.evidence_status.1'));
    }

    public function test_reviewer_cannot_save_a_score_outside_the_rubric(): void
    {
        $this->fakeReviewProject(1, 51);

        $response = $this->withSession([
            'access_token' => 'reviewer-token',
            'user' => ['id' => 77, 'nama' => 'Kuwat', 'role' => 'Reviewer'],
        ])->post(route('reviewer.assessment-save', [
            'projectId' => 1,
            'indicatorId' => 51,
        ]), [
            'score' => 5,
        ]);

        $response->assertSessionHasErrors('score');
    }

    public function test_legacy_demo_session_does_not_prevent_saving_assessments_to_the_api(): void
    {
        $this->fakeReviewProject(1, 51);

        $response = $this->withSession([
            'reviewer_demo_mode' => true,
            'access_token' => 'reviewer-token',
            'user' => ['id' => 77, 'nama' => 'Kuwat', 'role' => 'Reviewer'],
        ])->post(route('reviewer.assessment-save', [
            'projectId' => 1,
            'indicatorId' => 51,
        ]), [
            'score' => 3,
            'notes' => 'Catatan untuk data contoh.',
        ]);

        $response->assertRedirect(route('reviewer.assessment-detail', [
            'projectId' => 1,
            'indikator' => 51,
        ]));
        $this->assertSame(3, session('reviewer_assessments.1.51.score'));
        Http::assertSent(fn (HttpRequest $request): bool => $request->url() === 'https://akreditasi.example.test/api/v1/self-assessment'
            && $request->method() === 'POST');
    }

    public function test_reviewer_can_load_dynamic_projects_and_indicators_from_akreditasi_api(): void
    {
        Http::fake([
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
            'https://akreditasi.example.test/api/v1/mapping-tim' => Http::response([
                'data' => [
                    ['id_project' => 8, 'id_user' => 10, 'id_indikator' => 5],
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
            'https://akreditasi.example.test/api/v1/mapping-tim' => Http::response([
                'data' => [
                    ['id_project' => 8, 'id_user' => 10, 'id_indikator' => 5],
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
            && $request['id_rubrik'] === 15
            && $request['reviewer_id'] === 10);
    }

    private function fakeReviewProject(int|string $projectId, int|string $indicatorId): void
    {
        Http::fake([
            'https://akreditasi.example.test/api/v1/projects' => Http::response([
                'data' => [
                    [
                        'id' => $projectId,
                        'nama_project' => 'Project review',
                        'status_project' => 'review',
                        'completed_indicators' => 0,
                        'total_indicators' => 1,
                    ],
                ],
            ]),
            'https://akreditasi.example.test/api/v1/mapping-tim' => Http::response([
                'data' => [
                    [
                        'id_project' => $projectId,
                        'id_indikator' => $indicatorId,
                        'indikator' => [
                            'id' => $indicatorId,
                            'kode_indikator' => 'C.2.1.1',
                            'nama_indikator' => 'Visi program studi',
                            'narasi_prodi' => 'Narasi program studi',
                            'evidence' => [
                                ['name' => 'Bukti pertama', 'status' => 'Disetujui'],
                                ['name' => 'Bukti kedua', 'status' => 'Pending'],
                                ['name' => 'Bukti ketiga', 'status' => 'Perlu revisi'],
                            ],
                        ],
                    ],
                ],
            ]),
            'https://akreditasi.example.test/api/v1/self-assessment*' => Http::response([
                'success' => true,
                'data' => [],
            ]),
        ]);
    }
}
