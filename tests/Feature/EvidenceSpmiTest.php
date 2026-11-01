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
        $response->assertSee('Bukti Evaluasi');
        $response->assertSee('Bukti Pengendalian &amp; Peningkatan', false);
        $response->assertSee('Bukti Dampak Tridharma');
        $response->assertSee('Dokumen Implementasi Harian');
        $response->assertDontSee('id="toast-success"', false);
    }

    public function test_user_can_submit_evidence(): void
    {
        Http::fake([
            '*/spmi-evidence*' => Http::response(['message' => 'Success', 'data' => ['id' => 999]], 201),
        ]);

        $response = $this->withSession($this->authSession())
            ->post(route('spmi.evidence.store'), [
                'standar_id' => 1,
                'kategori' => 'dokumen_implementasi',
                'deskripsi' => 'SOP praktikum dan lembar persetujuan',
                'link_dokumen' => 'https://drive.google.com/file/d/sop-praktikum',
                'tahun' => 2026,
            ]);

        $response->assertRedirect(route('spmi.evidence', ['tahun' => 2026, 'standar' => 1]));
    }

    public function test_user_can_submit_all_categories_in_one_action(): void
    {
        Http::fake([
            '*/spmi-evidence*' => Http::response(['message' => 'Success', 'data' => ['id' => 999]], 201),
        ]);

        $response = $this->withSession($this->authSession())
            ->post(route('spmi.evidence.store'), [
                'standar_id' => 2,
                'tahun' => 2026,
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

        $response->assertRedirect(route('spmi.evidence', ['tahun' => 2026, 'standar' => 2]));
    }

    public function test_user_can_update_evidence(): void
    {
        Http::fake([
            '*/spmi-evidence/*' => Http::response(['message' => 'Updated', 'data' => ['id' => 101]], 200),
        ]);

        $response = $this->withSession($this->authSession())
            ->put(route('spmi.evidence.update', 101), [
                'standar_id' => 1,
                'kategori' => 'bukti_evaluasi',
                'deskripsi' => 'Pembaruan deskripsi evidence',
                'link_dokumen' => 'https://drive.google.com/file/d/updated-doc',
                'tahun' => 2026,
            ]);

        $response->assertRedirect(route('spmi.evidence', ['tahun' => 2026, 'standar' => 1]));
    }

    public function test_evidence_statistics_update_and_persist(): void
    {
        // 1. Simpan evidence untuk Standar 3 kategori dokumen_implementasi
        $this->withSession($this->authSession())
            ->post(route('spmi.evidence.store'), [
                'standar_id' => 3,
                'tahun' => 2026,
                'evidences' => [
                    'dokumen_implementasi' => [
                        'id' => '',
                        'deskripsi' => 'Logbook perkuliahan mingguan',
                        'link_dokumen' => 'https://drive.google.com/file/d/logbook-3',
                    ],
                ],
            ]);

        // 2. Akses halaman kembali dan pastikan data tercatat di view
        $viewResponse = $this->withSession($this->authSession())
            ->get(route('spmi.evidence', ['tahun' => 2026, 'standar' => 3]));

        $viewResponse->assertStatus(200);
        $viewResponse->assertSee('Logbook perkuliahan mingguan');
        $viewResponse->assertSee('https://drive.google.com/file/d/logbook-3');

        // 3. Simpan evidence untuk kategori bukti_dampak_tridharma pada standar yang sama
        $this->withSession($this->authSession())
            ->post(route('spmi.evidence.store'), [
                'standar_id' => 3,
                'tahun' => 2026,
                'evidences' => [
                    'bukti_dampak_tridharma' => [
                        'id' => '',
                        'deskripsi' => 'Publikasi artikel ilmiah mahasiswa di jurnal terakreditasi',
                        'link_dokumen' => 'https://drive.google.com/file/d/jurnal-mhs',
                    ],
                ],
            ]);

        $afterSubmit = $this->withSession($this->authSession())
            ->get(route('spmi.evidence', ['tahun' => 2026, 'standar' => 3]));

        $afterSubmit->assertStatus(200);
        $afterSubmit->assertSee('Publikasi artikel ilmiah mahasiswa di jurnal terakreditasi');
    }
}
