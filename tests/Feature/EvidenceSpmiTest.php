<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class EvidenceSpmiTest extends TestCase
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
                'nama' => 'Kaprodi TRPL',
                'role' => 'KAPRODI',
                'email' => 'kaprodi@example.com',
            ],
        ]));
        $signature = base64_encode('fake_signature');

        return "{$header}.{$payload}.{$signature}";
    }

    protected function authSession(): array
    {
        return [
            'access_token' => $this->getFakeToken(),
            'user' => [
                'nama' => 'Kaprodi TRPL',
                'role' => 'KAPRODI',
                'email' => 'kaprodi@example.com',
            ],
        ];
    }

    public function test_unauthenticated_user_redirected_to_login(): void
    {
        $response = $this->get(route('spmi.evidence'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_evidence_spmi_page(): void
    {
        $response = $this->withSession($this->authSession())
            ->get(route('spmi.evidence'));

        $response->assertStatus(200);
        $response->assertSee('Evidence SPMI');
        $response->assertSee('PILIH PROJECT AKREDITASI');
        $response->assertSee('Bukti Evaluasi');
        $response->assertSee('Bukti Pengendalian &amp; Peningkatan', false);
        $response->assertSee('Bukti Dampak Tridharma');
        $response->assertSee('Dokumen Implementasi Harian');
    }

    public function test_user_can_submit_evidence(): void
    {
        Http::fake([
            '*/evidence-spmi*' => Http::response(['message' => 'Success', 'data' => ['id' => 999]], 201),
            '*/spmi-evidence*' => Http::response(['message' => 'Success', 'data' => ['id' => 999]], 201),
        ]);

        $response = $this->withSession($this->authSession())
            ->post(route('spmi.evidence.store'), [
                'project_id' => 1,
                'kategori' => 'dokumen_implementasi',
                'deskripsi' => 'SOP praktikum dan lembar persetujuan',
                'link_dokumen' => 'https://drive.google.com/file/d/sop-praktikum',
            ]);

        $response->assertRedirect(route('spmi.evidence', ['project_id' => 1]));
    }

    public function test_user_can_submit_all_categories_in_one_action(): void
    {
        Http::fake([
            '*/evidence-spmi*' => Http::response(['message' => 'Success', 'data' => ['id' => 999]], 201),
            '*/spmi-evidence*' => Http::response(['message' => 'Success', 'data' => ['id' => 999]], 201),
        ]);

        $response = $this->withSession($this->authSession())
            ->post(route('spmi.evidence.store'), [
                'project_id' => 1,
                'evidences' => [
                    'bukti_evaluasi' => [
                        'id' => '',
                        'deskripsi' => 'Evaluasi MoU dan MoA kerja sama magang',
                        'link_dokumen' => 'https://drive.google.com/file/d/eval-kerjasama',
                    ],
                    'bukti_pengendalian' => [
                        'id' => '',
                        'deskripsi' => 'Perpanjangan dokumen IA industri',
                        'link_dokumen' => 'https://drive.google.com/file/d/ia-industri',
                    ],
                ],
            ]);

        $response->assertRedirect(route('spmi.evidence', ['project_id' => 1]));
    }

    public function test_user_can_update_evidence(): void
    {
        Http::fake([
            '*/evidence-spmi*' => Http::response(['message' => 'Updated', 'data' => ['id' => 101]], 200),
            '*/spmi-evidence*' => Http::response(['message' => 'Updated', 'data' => ['id' => 101]], 200),
        ]);

        $response = $this->withSession($this->authSession())
            ->put(route('spmi.evidence.update', 101), [
                'project_id' => 1,
                'kategori' => 'bukti_evaluasi',
                'deskripsi' => 'Pembaruan deskripsi evidence project',
                'link_dokumen' => 'https://drive.google.com/file/d/updated-doc',
            ]);

        $response->assertRedirect(route('spmi.evidence', ['project_id' => 1]));
    }

    public function test_evidence_statistics_update_and_persist(): void
    {
        // 1. Simpan evidence untuk Project 2 kategori dokumen_implementasi
        $this->withSession($this->authSession())
            ->post(route('spmi.evidence.store'), [
                'project_id' => 2,
                'evidences' => [
                    'dokumen_implementasi' => [
                        'id' => '',
                        'deskripsi' => 'Logbook perkuliahan mingguan project 2',
                        'link_dokumen' => 'https://drive.google.com/file/d/logbook-p2',
                    ],
                ],
            ]);

        // 2. Akses halaman kembali dan pastikan data tercatat di view
        $viewResponse = $this->withSession($this->authSession())
            ->get(route('spmi.evidence', ['project_id' => 2]));

        $viewResponse->assertStatus(200);
        $viewResponse->assertSee('Logbook perkuliahan mingguan project 2');
        $viewResponse->assertSee('https://drive.google.com/file/d/logbook-p2');

        // 3. Simpan evidence untuk kategori bukti_dampak_tridharma pada project yang sama
        $this->withSession($this->authSession())
            ->post(route('spmi.evidence.store'), [
                'project_id' => 2,
                'evidences' => [
                    'bukti_dampak_tridharma' => [
                        'id' => '',
                        'deskripsi' => 'Publikasi artikel ilmiah mahasiswa di jurnal terakreditasi',
                        'link_dokumen' => 'https://drive.google.com/file/d/jurnal-mhs',
                    ],
                ],
            ]);

        $afterSubmit = $this->withSession($this->authSession())
            ->get(route('spmi.evidence', ['project_id' => 2]));

        $afterSubmit->assertStatus(200);
        $afterSubmit->assertSee('Publikasi artikel ilmiah mahasiswa di jurnal terakreditasi');
    }
}
