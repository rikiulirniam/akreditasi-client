<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class DashboardTest extends TestCase
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
                'nama' => 'Superadmin',
                'role' => 'SUPERADMIN',
                'email' => 'superadmin@example.com',
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
                'nama' => 'Superadmin',
                'role' => 'SUPERADMIN',
                'email' => 'superadmin@example.com',
            ],
        ];
    }

    public function test_dashboard_redirects_unauthenticated_user_to_login(): void
    {
        $response = $this->get(route('dashboard'));

        $response->assertRedirect(route('login'));
    }

    public function test_dashboard_renders_successfully_with_figma_layout_and_data(): void
    {
        $response = $this->withSession($this->authSession())->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('PUSAT KENDALI SISTEM');
        $response->assertSee('Selamat datang', false);
        $response->assertSee('STATUS SISTEM');
        $response->assertSee('Semua layanan normal');
        $response->assertSee('Pantau indikator kerja utama dan kesiapan data akreditasi Program Studi dari satu tempat');

        // 6 Top KPI
        $response->assertSee('Mahasiswa Aktif');
        $response->assertSee('425');
        $response->assertSee('SEMESTER BERJALAN');
        $response->assertSee('Mahasiswa Baru');
        $response->assertSee('112');
        $response->assertSee('ANGKATAN 2026');
        $response->assertSee('Lulus Tepat Waktu');
        $response->assertSee('87 %');
        $response->assertSee('TARGET MINIMUM : 85%');
        $response->assertSee('Rata-Rata IPK');
        $response->assertSee('3.78');
        $response->assertSee('SKALA 4.00');
        $response->assertSee('Prestasi Nasional');
        $response->assertSee('97');
        $response->assertSee('Prestasi Internasional');
        $response->assertSee('34');
        $response->assertSee('TAHUN 2026');

        // Ringkasan Tri Dharma
        $response->assertSee('RINGKASAN TRI DHARMA');
        $response->assertSee('DTPS');
        $response->assertSee('Dosen S3');
        $response->assertSee('Lektor Kepala');
        $response->assertSee('Penelitian');
        $response->assertSee('Publikasi Scopus');
        $response->assertSee('HKI');
        $response->assertSee('PKM');
        $response->assertSee('Kerja Sama Aktif');

        // Tren Chart & Prestasi Table
        $response->assertSee('TREN RATA-RATA IPK', false);
        $response->assertSee('PRESTASI MAHASISWA TERBARU');
        $response->assertSee('Action');
        $response->assertSee('3.30');
        $response->assertSee('3.40');
        $response->assertSee('3.52');
        $response->assertSee('73%');
        $response->assertSee('88%');
        $response->assertSee('77%');
        $response->assertSee('85%');
        $response->assertSee('Gerald Manunggal Fauzi');
        $response->assertSee('UI/UX Miaw Miaw');
        $response->assertSee('2026');
        $response->assertSee('aria-label="Lihat prestasi Gerald Manunggal Fauzi"', false);
    }

    public function test_dashboard_displays_live_tridharma_metrics_from_api(): void
    {
        Http::fake([
            'https://tridharma.fayra.my.id/api/v1/dosen/statistik' => Http::response([
                'success' => true,
                'data' => [
                    'total_aktif' => 28,
                    'sebaran_pendidikan' => [
                        ['jenjang' => 'S2', 'total_dosen' => 12],
                        ['jenjang' => 'S3', 'total_dosen' => 11],
                    ],
                    'sebaran_jabatan' => [
                        ['nama_jabatan' => 'Asisten Ahli', 'total_dosen' => 6],
                        ['nama_jabatan' => 'Lektor Kepala', 'total_dosen' => 7],
                    ],
                ],
            ]),
            'https://tridharma.fayra.my.id/api/v1/tridharma/summary*' => Http::response([
                'success' => true,
                'data' => [
                    'kegiatan' => [
                        'penelitian' => ['total' => 48],
                        'pkm' => ['total' => 31],
                    ],
                    'luaran' => [
                        'by_jenis' => [
                            'HKI' => 19,
                            'Jurnal Internasional' => 25,
                        ],
                    ],
                ],
            ]),
            'https://tridharma.fayra.my.id/api/v1/kerjasama/aktif*' => Http::response([
                'success' => true,
                'data' => [
                    'total_aktif' => 24,
                ],
            ]),
        ]);

        $response = $this->withSession($this->authSession())->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('28'); // DTPS
        $response->assertSee('11'); // Dosen S3
        $response->assertSee('7');  // Lektor Kepala
        $response->assertSee('48'); // Penelitian
        $response->assertSee('25'); // Publikasi Scopus / Internasional
        $response->assertSee('19'); // HKI
        $response->assertSee('31'); // PKM
        $response->assertSee('24'); // Kerja Sama Aktif
    }
}
