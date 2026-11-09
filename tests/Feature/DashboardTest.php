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
        Http::fake([
            'https://akademik.fayra.my.id/api/*' => Http::response([], 500),
            'https://tridharma.fayra.my.id/api/*' => Http::response([], 500),
        ]);

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

    public function test_dashboard_displays_live_akademik_metrics_and_prestasi_from_api(): void
    {
        Http::fake([
            'https://akademik.fayra.my.id/api/mahasiswa*' => Http::response([
                'success' => true,
                'data' => [
                    ['id' => 1, 'nim' => 101, 'nama_mahasiswa' => 'Mhs 1', 'tahun_masuk' => 2026, 'tanggal_kelulusan' => null, 'masa_studi_bulan' => 0],
                    ['id' => 2, 'nim' => 102, 'nama_mahasiswa' => 'Mhs 2', 'tahun_masuk' => 2026, 'tanggal_kelulusan' => null, 'masa_studi_bulan' => 0],
                    ['id' => 3, 'nim' => 103, 'nama_mahasiswa' => 'Mhs 3', 'tahun_masuk' => 2025, 'tanggal_kelulusan' => null, 'masa_studi_bulan' => 0],
                    ['id' => 4, 'nim' => 104, 'nama_mahasiswa' => 'Mhs 4', 'tahun_masuk' => 2020, 'tanggal_kelulusan' => '2024-08-01', 'masa_studi_bulan' => 48],
                    ['id' => 5, 'nim' => 105, 'nama_mahasiswa' => 'Mhs 5', 'tahun_masuk' => 2020, 'tanggal_kelulusan' => '2024-08-01', 'masa_studi_bulan' => 60],
                ],
            ]),
            'https://akademik.fayra.my.id/api/nilai-mahasiswa*' => Http::response([
                'success' => true,
                'data' => [
                    ['id' => 1, 'bobot' => 3.80],
                    ['id' => 2, 'bobot' => 3.90],
                ],
            ]),
            'https://akademik.fayra.my.id/api/prestasi-mahasiswa*' => Http::response([
                'success' => true,
                'data' => [
                    [
                        'id' => 1,
                        'nama_aktivitas' => 'Juara 1 Hackathon Nasional IoT',
                        'tahun' => 2026,
                        'mahasiswa' => ['id' => 1, 'nama_mahasiswa' => 'Dasi Hayu Permana'],
                    ],
                    [
                        'id' => 2,
                        'nama_aktivitas' => 'Finalis Huawei ICT Competition Regional Asia Pacific',
                        'tahun' => 2026,
                        'mahasiswa' => ['id' => 2, 'nama_mahasiswa' => 'Pranaja Luthfi Belian'],
                    ],
                ],
            ]),
            'https://tridharma.fayra.my.id/api/*' => Http::response([], 500),
        ]);

        $response = $this->withSession($this->authSession())->get(route('dashboard'));

        $response->assertOk();
        // Mahasiswa Aktif = 3 (Mhs 1, 2, 3)
        $response->assertSee('3');
        // Mahasiswa Baru (Angkatan 2026) = 2 (Mhs 1, 2)
        $response->assertSee('2');
        // Lulus Tepat Waktu = 50% (1 dari 2 lulus masa_studi_bulan <= 48)
        $response->assertSee('50 %');
        // Rata-Rata IPK = 3.85
        $response->assertSee('3.85');
        // Prestasi
        $response->assertSee('Dasi Hayu Permana');
        $response->assertSee('Juara 1 Hackathon Nasional IoT');
        $response->assertSee('Pranaja Luthfi Belian');
        $response->assertSee('Finalis Huawei ICT Competition Regional Asia Pacific');
    }

    public function test_dashboard_displays_dynamic_tren_ipk_and_kelulusan_from_api(): void
    {
        Http::fake([
            'https://akademik.fayra.my.id/api/mahasiswa/tren*' => Http::response([
                'success' => true,
                'data' => [
                    'years' => ['2022', '2023', '2024', '2025', '2026'],
                    'ipk' => [3.25, 3.45, 3.60, 3.65, 3.70],
                    'kelulusan' => [70, 85, 80, 90, 88],
                ],
            ]),
            'https://akademik.fayra.my.id/api/*' => Http::response([], 500),
            'https://tridharma.fayra.my.id/api/*' => Http::response([], 500),
        ]);

        $response = $this->withSession($this->authSession())->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('3.25');
        $response->assertSee('3.45');
        $response->assertSee('3.60');
        $response->assertSee('3.65');
        $response->assertSee('3.70');
        $response->assertSee('70%');
        $response->assertSee('85%');
        $response->assertSee('80%');
        $response->assertSee('90%');
        $response->assertSee('88%');
    }
}
