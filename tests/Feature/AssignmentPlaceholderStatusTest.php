<?php

namespace Tests\Feature;

use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AssignmentPlaceholderStatusTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        config(['services.akreditasi.base_url' => 'https://akreditasi.example.test']);
    }

    public function test_reviewer_role_redirects_to_kaprodi_flow_and_sidebar_has_no_reviewer_menu(): void
    {
        $this->withSession([
            'access_token' => 'reviewer-token',
            'user' => ['id' => 77, 'nama' => 'Kuwat', 'role' => 'Reviewer'],
        ])->get('/')->assertRedirect(route('kaprodi.dashboard'));

        $this->withSession([
            'access_token' => 'reviewer-token',
            'user' => ['id' => 77, 'nama' => 'Kuwat', 'role' => 'Reviewer'],
        ])->get(route('dashboard'))->assertRedirect(route('kaprodi.dashboard'));

        $this->withSession([
            'access_token' => 'reviewer-token',
            'user' => ['id' => 77, 'nama' => 'Kuwat', 'role' => 'Reviewer'],
        ])->get('/reviewer/penilaian')->assertRedirect(route('kaprodi.assessments'));

        $this->withSession([
            'access_token' => 'kaprodi-token',
            'user' => ['id' => 2, 'nama' => 'Kuwat Santoso', 'role' => 'Kaprodi'],
        ])->get(route('dosen.my-assignments'))
            ->assertOk()
            ->assertSee('Penilaian Indikator')
            ->assertDontSee('Ruang kerja reviewer');
    }

    public function test_kaprodi_can_access_self_assessment_and_save_score_for_dosen_indicator(): void
    {
        Http::fake([
            'https://akreditasi.example.test/api/v1/projects' => Http::response([
                'success' => true,
                'data' => [
                    [
                        'id' => 1,
                        'nama_project' => 'Akreditasi LAM INFOKOM D4 TRK 2026',
                        'status_project' => 'review',
                        'tahun_target_submit' => 2026,
                    ],
                ],
            ]),
            'https://akreditasi.example.test/api/v1/mapping-tim' => Http::response([
                'data' => [
                    [
                        'id_project' => 1,
                        'id_user' => 99,
                        'id_indikator' => 14,
                        'indikator' => ['id' => 14, 'kode_indikator' => 'K6.1', 'nama_indikator' => 'Kurikulum OBE'],
                    ],
                ],
            ]),
            'https://akreditasi.example.test/api/v1/master/indikator' => Http::response([
                'success' => true,
                'data' => [
                    [
                        'id' => 14,
                        'kode_indikator' => 'K6.1',
                        'nama_indikator' => 'Kurikulum OBE',
                        'bobot' => 4.0,
                        'rubriks' => [
                            ['id' => 101, 'skor' => 4, 'deskripsi' => 'Sangat baik'],
                        ],
                    ],
                ],
            ]),
            'https://akreditasi.example.test/api/v1/master/rubrik' => Http::response(['success' => true, 'data' => []]),
            'https://akreditasi.example.test/api/v1/self-assessment*' => Http::response(['success' => true, 'data' => []]),
        ]);

        $session = [
            'access_token' => 'kaprodi-token',
            'user' => ['id' => 2, 'nama' => 'Kuwat Santoso', 'role' => 'Kaprodi'],
        ];

        $this->withSession($session)
            ->get(route('kaprodi.assessments'))
            ->assertOk()
            ->assertSee('Penilaian Mandiri Kaprodi')
            ->assertSee('Akreditasi LAM INFOKOM D4 TRK 2026');

        $this->withSession($session)
            ->get(route('kaprodi.assessment-detail', ['projectId' => 1]))
            ->assertOk()
            ->assertSee('Catatan Penilai (Kaprodi)');

        $this->withSession($session)
            ->post(route('kaprodi.assessment-save', ['projectId' => 1, 'indicatorId' => 14]), [
                'score' => 4,
                'notes' => 'Terverifikasi oleh Kaprodi',
                'rubrik_id' => 101,
            ])
            ->assertRedirect(route('kaprodi.assessment-detail', ['projectId' => 1, 'indikator' => 14]));
    }

    public function test_plotting_displays_c2_and_c5_as_read_only_upps_placeholders_without_editable_checkboxes(): void
    {
        Http::fake([
            'https://akreditasi.example.test/api/v1/projects/1/placeholder-modules' => Http::response([
                'success' => true,
                'data' => [
                    [
                        'kode_kriteria' => 'C.2',
                        'nama_kriteria' => 'Tata Pamong, Tata Kelola dan Kerjasama',
                        'keterangan' => 'Dikelola unit institusi Polines (UPPS).',
                    ],
                    [
                        'kode_kriteria' => 'C.5',
                        'nama_kriteria' => 'Keuangan, Sarana dan Prasarana',
                        'keterangan' => 'Dikelola Bagian Keuangan & Sarana Polines (UPPS).',
                    ],
                ],
            ]),
            'https://akreditasi.example.test/api/v1/projects*' => Http::response([
                'success' => true,
                'data' => [
                    ['id' => 1, 'nama_project' => 'Akreditasi LAM INFOKOM D4 TRK 2026', 'tahun_target_submit' => 2026],
                ],
            ]),
            'https://akreditasi.example.test/api/v1/master/kriteria*' => Http::response(['success' => true, 'data' => []]),
            'https://akreditasi.example.test/api/v1/master/indikator*' => Http::response(['success' => true, 'data' => []]),
            'https://akreditasi.example.test/api/v1/mapping-tim*' => Http::response(['success' => true, 'data' => []]),
        ]);

        $response = $this->withSession([
            'access_token' => 'kaprodi-token',
            'user' => ['id' => 2, 'nama' => 'Kuwat Santoso', 'role' => 'Kaprodi'],
        ])->get(route('kaprodi.plotting'));

        $response->assertOk()
            ->assertSee('C.2 · Tata Pamong, Tata Kelola dan Kerjasama')
            ->assertSee('C.5 · Keuangan, Sarana dan Prasarana')
            ->assertSee('Dikelola Institusi')
            ->assertSee('Terintegrasi UPPS')
            ->assertDontSee('value="K2.1"')
            ->assertDontSee('value="K5.1"');
    }

    public function test_my_assignments_excludes_c2_c5_and_readonly_indicators_and_blocks_evidence_store(): void
    {
        Http::fake([
            'https://akreditasi.example.test/api/v1/dosen/my-assignments' => Http::response([
                'success' => true,
                'data' => [
                    [
                        'id_mapping' => 1,
                        'role' => 'PIC',
                        'id_indikator' => 1,
                        'kode_indikator' => 'K1.1',
                        'deskripsi' => 'Visi Misi Prodi',
                        'bobot' => 3.5,
                        'id_project' => 1,
                    ],
                    [
                        'id_mapping' => 2,
                        'role' => 'PIC',
                        'id_indikator' => 2,
                        'kode_indikator' => 'K2.1',
                        'deskripsi' => 'Tata Pamong Institusi',
                        'bobot' => 4.0,
                        'id_project' => 1,
                    ],
                    [
                        'id_mapping' => 3,
                        'role' => 'PIC',
                        'id_indikator' => 12,
                        'kode_indikator' => 'K5.1',
                        'deskripsi' => 'Keuangan Sarana Prasarana',
                        'bobot' => 3.5,
                        'id_project' => 1,
                    ],
                    [
                        'id_mapping' => 4,
                        'role' => 'PIC',
                        'id_indikator' => 99,
                        'kode_indikator' => 'K6.9',
                        'deskripsi' => 'Indikator Read-Only',
                        'is_readonly' => true,
                        'id_project' => 1,
                    ],
                ],
            ]),
            'https://akreditasi.example.test/api/v1/mapping-tim*' => Http::response(['success' => true, 'data' => []]),
        ]);

        $session = [
            'access_token' => 'dosen-token',
            'user' => ['id' => 10, 'nama' => 'Sarno', 'role' => 'Dosen'],
        ];

        $this->withSession($session)
            ->get(route('dosen.my-assignments'))
            ->assertOk()
            ->assertSee('K1.1')
            ->assertDontSee('K2.1')
            ->assertDontSee('K5.1')
            ->assertDontSee('K6.9');

        $this->withSession($session)
            ->postJson(route('dosen.evidence.store'), [
                'project_id' => 1,
                'indicator_id' => 2,
                'code' => 'K2.1',
                'evidence_url' => 'https://drive.google.com/drive/folders/abc',
            ])
            ->assertStatus(422)
            ->assertJson(['success' => false]);
    }

    public function test_project_status_supports_all_lifecycle_statuses_and_selesai_without_sk_or_date(): void
    {
        Http::fake([
            'https://akreditasi.example.test/api/v1/projects/1' => Http::response([
                'success' => true,
                'data' => [
                    'id' => 1,
                    'id_prodi' => 101,
                    'id_versi_instrumen' => 1,
                    'id_jenjang' => 1,
                    'id_jenis_instrumen' => 1,
                    'nama_project' => 'Akreditasi TRK 2026',
                    'tahun_target_submit' => 2026,
                ],
            ]),
            'https://akreditasi.example.test/api/v1/projects/1/status' => Http::response([
                'success' => true,
                'message' => 'Status diperbarui',
            ]),
        ]);

        $session = [
            'access_token' => 'kaprodi-token',
            'user' => ['id' => 2, 'nama' => 'Kuwat', 'role' => 'Kaprodi'],
        ];

        foreach (['Draft', 'On Progress', 'Pending', 'Cancel', 'Selesai'] as $status) {
            $this->withSession($session)
                ->patchJson(route('kaprodi.project.update-status', ['id' => 1]), [
                    'status_project' => $status,
                ])
                ->assertOk()
                ->assertJson([
                    'success' => true,
                    'message' => "Status project berhasil diubah menjadi {$status}.",
                ]);
        }

        // Pastikan SK dan tanggal verifikasi tetap dikirim ketika diisi oleh pengguna
        $this->withSession($session)
            ->patchJson(route('kaprodi.project.update-status', ['id' => 1]), [
                'status_project' => 'Selesai',
                'nomor_sk_akreditasi' => '0048/SK/LAM-INFOKOM/2026',
                'tanggal_verifikasi_akreditasi' => '2026-11-28',
            ])
            ->assertOk();

        Http::assertSent(fn (HttpRequest $request): bool => $request->url() === 'https://akreditasi.example.test/api/v1/projects/1/status'
            && $request->method() === 'PATCH'
            && ($request['nomor_sk_akreditasi'] ?? null) === '0048/SK/LAM-INFOKOM/2026'
            && ($request['tanggal_verifikasi_akreditasi'] ?? null) === '2026-11-28');
    }
}
