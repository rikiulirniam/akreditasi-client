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
        $response->assertSee('Laporan Audit Mutu Internal (AMI &amp; Monev)', false);
        $response->assertSee('Rapat Tinjauan Manajemen (RTM &amp; RTL)', false);
        $response->assertSee('Capaian IKU &amp; Survei Kepuasan Stakeholder', false);
        $response->assertSee('Dokumen Implementasi (Buku Kebijakan SPMI, Manual Mutu, SOP PPEPP)');
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

    public function test_user_can_view_evidence_overview_asesor(): void
    {
        $response = $this->withSession($this->authSession())
            ->get(route('spmi.overview', 1));

        $response->assertStatus(200);
        $response->assertSee('Rekapitulasi Dokumen Evidence');
        $response->assertSee('Dokumen Mutu Institusi (4 Pilar Evidence SPMI)');
        $response->assertSee('Daftar Indikator &amp; Status Bukti Dukung', false);
        $response->assertSee('Sudah Memiliki Evidence');
        $response->assertSee('Belum Memiliki Evidence');
        $response->assertSee('Data tidak tersedia');
        $response->assertSee('Simulasi');
        $response->assertSee('target="_blank"', false);
        $response->assertSee('rel="noopener noreferrer"', false);
        $response->assertDontSee('Kesiapan Bukti Indikator Dosen PIC');
        $response->assertSee('Salin Token Akses Asesor Eksternal');
    }

    public function test_external_asesor_can_access_public_read_only_overview(): void
    {
        $raw = '1:20261122';
        $token = rtrim(strtr(base64_encode($raw), '+/', '-_'), '=');

        $response = $this->get(route('spmi.public-overview', ['token' => $token]));

        $response->assertStatus(200);
        $response->assertSee('Mode Asesor Eksternal (Read-Only)');
        $response->assertSee('Read-Only Asesor');
        $response->assertSee('Dokumen Mutu Institusi (4 Pilar Evidence SPMI)');
        $response->assertDontSee('Kesiapan Bukti Indikator Dosen PIC');
        // Mode publik read-only tidak boleh menampilkan tombol kelola atau salin token
        $response->assertDontSee('Kelola Dokumen');
        $response->assertDontSee('Salin Token Akses Asesor Eksternal');
    }

    public function test_evidence_overview_integrates_api_and_shows_error_state_on_failure(): void
    {
        Http::fake([
            '*/projects/*/evidence-overview*' => Http::response(['message' => 'Server Error'], 500),
            '*/evidence-spmi*' => Http::response(['message' => 'Server Error'], 500),
            '*/spmi-evidence*' => Http::response(['message' => 'Server Error'], 500),
        ]);

        $response = $this->withSession($this->authSession())
            ->get(route('spmi.overview', 1));

        $response->assertStatus(200);
        $response->assertSee('Gagal memuat rekapitulasi evidence dari server');
        $response->assertSee('Simulasi');
    }

    public function test_sidebar_menus_are_grouped_for_admin_kaprodi_and_dosen_without_reviewer(): void
    {
        // 1. Administrator
        $adminSession = [
            'access_token' => $this->getFakeToken(),
            'user' => ['nama' => 'Admin Sistem', 'role' => 'Administrator', 'email' => 'admin@example.com'],
        ];
        $adminRes = $this->withSession($adminSession)->get(route('spmi.evidence'));
        $adminRes->assertStatus(200);
        $adminRes->assertSee('Instrumen Akreditasi');
        $adminRes->assertSee('Evidence SPMI');
        $adminRes->assertDontSee('Menu Reviewer');

        // 2. Kaprodi
        $kaprodiRes = $this->withSession($this->authSession())->get(route('spmi.evidence'));
        $kaprodiRes->assertStatus(200);
        $kaprodiRes->assertSee('Plotting Tim Akreditasi');
        $kaprodiRes->assertSee('Evidence SPMI');
        $kaprodiRes->assertSee('Overview Dokumen');

        // 3. Dosen
        $dosenSession = [
            'access_token' => $this->getFakeToken(),
            'user' => ['nama' => 'Dosen Biasa', 'role' => 'Dosen', 'email' => 'dosen@example.com'],
        ];
        $dosenRes = $this->withSession($dosenSession)->get(route('spmi.overview', 1));
        $dosenRes->assertStatus(200);
        $dosenRes->assertSee('Tugas Saya');
        $dosenRes->assertSee('Overview Dokumen');
        $dosenRes->assertDontSee('Plotting Tim Akreditasi');
    }
}
