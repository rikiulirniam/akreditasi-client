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
}
