<?php

namespace Tests\Feature;

use App\Services\AkademikService;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AkademikEndpointOptimizationTest extends TestCase
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
                'nama' => 'Admin Akreditasi',
                'role' => 'KAPRODI',
                'email' => 'admin@example.com',
            ],
            'permissions' => [
                'kurikulum.read',
                'kurikulum.create',
                'kurikulum.update',
                'kurikulum.delete',
                'kelas.read',
                'tahun_akademik.read',
            ],
        ]));
        $signature = base64_encode('fake_signature');

        return "{$header}.{$payload}.{$signature}";
    }

    public function test_service_calls_correct_optimized_endpoints(): void
    {
        Http::fake([
            '*/api/kelas/15/mahasiswa*' => Http::response(['success' => true, 'data' => []], 200),
            '*/api/kelas/15/mata-kuliah*' => Http::response(['success' => true, 'data' => []], 200),
            '*/api/kurikulum/7/mata-kuliah*' => Http::response(['success' => true, 'data' => []], 200),
        ]);

        $service = app(AkademikService::class)->withToken('test-token');

        $resMahasiswa = $service->getMahasiswaByKelasId(15);
        $resMkKelas = $service->getMataKuliahByKelasId(15);
        $resMkKurikulum = $service->getMataKuliahByKurikulumId(7);

        $this->assertTrue($resMahasiswa->successful());
        $this->assertTrue($resMkKelas->successful());
        $this->assertTrue($resMkKurikulum->successful());

        Http::assertSent(fn (HttpRequest $request) => str_contains($request->url(), '/api/kelas/15/mahasiswa'));
        Http::assertSent(fn (HttpRequest $request) => str_contains($request->url(), '/api/kelas/15/mata-kuliah'));
        Http::assertSent(fn (HttpRequest $request) => str_contains($request->url(), '/api/kurikulum/7/mata-kuliah'));
    }

    public function test_detail_kelas_uses_mahasiswa_and_mata_kuliah_per_kelas_endpoints(): void
    {
        Http::fake([
            '*/api/tahun-akademik/1' => Http::response([
                'success' => true,
                'data' => ['id' => 1, 'tahun' => '2025/2026', 'semester' => 'Ganjil'],
            ], 200),
            '*/api/kelas/5' => Http::response([
                'success' => true,
                'data' => ['id' => 5, 'nama' => 'TI 2-A'],
            ], 200),
            '*/api/kelas/5/mahasiswa*' => Http::response([
                'success' => true,
                'data' => [
                    [
                        'id' => 101,
                        'nim' => '230101',
                        'nama' => 'Budi Santoso',
                        'status' => 'Aktif',
                        'ipk' => 3.85,
                        'prestasi' => 'Juara 1 Web Design',
                    ],
                ],
            ], 200),
            '*/api/kelas/5/mata-kuliah*' => Http::response([
                'success' => true,
                'data' => [
                    [
                        'id' => 201,
                        'kode_mk' => 'TRK201',
                        'nama_mk' => 'Pemrograman Web Lanjut',
                        'sks' => 3,
                        'dosen' => 'Dr. Bambang',
                    ],
                ],
            ], 200),
        ]);

        $response = $this->withSession([
            'access_token' => $this->getFakeToken(),
            'user' => ['nama' => 'Admin', 'role' => 'KAPRODI'],
            'permissions' => ['kelas.read', 'tahun_akademik.read'],
        ])->get(route('tahun-akademik.detail-kelas', ['id' => 1, 'kelasId' => 5]));

        $response->assertStatus(200);
        $response->assertSee('Budi Santoso');
        $response->assertSee('230101');
        $response->assertSee('Pemrograman Web Lanjut');
        $response->assertSee('TRK201');

        Http::assertSent(fn (HttpRequest $request) => str_contains($request->url(), '/api/kelas/5/mahasiswa'));
        Http::assertSent(fn (HttpRequest $request) => str_contains($request->url(), '/api/kelas/5/mata-kuliah'));
    }

    public function test_detail_kurikulum_uses_mata_kuliah_per_kurikulum_endpoint(): void
    {
        Http::fake([
            '*/api/kurikulum/3' => Http::response([
                'success' => true,
                'data' => [
                    'id' => 3,
                    'nama_kurikulum' => 'Kurikulum TRK 2025',
                    'sk_kurikulum' => 'SK-TRK-2025',
                    'berlaku_sampai' => 2029,
                    'id_prodi' => 1,
                ],
            ], 200),
            '*/api/cpl*' => Http::response([
                'success' => true,
                'data' => [],
            ], 200),
            '*/api/kurikulum/3/mata-kuliah*' => Http::response([
                'success' => true,
                'data' => [
                    [
                        'id' => 301,
                        'kode_mata_kuliah' => 'TRK301',
                        'nama_mata_kuliah' => 'Kecerdasan Buatan',
                        'sks' => 3,
                        'cpl' => [
                            ['kategori' => 'Keterampilan Khusus', 'deskripsi' => 'Mampu membuat AI model'],
                        ],
                    ],
                ],
            ], 200),
            '*/api/prodi/1' => Http::response([
                'success' => true,
                'data' => ['id' => 1, 'nama_prodi' => 'Teknologi Rekayasa Komputer'],
            ], 200),
        ]);

        $response = $this->withSession([
            'access_token' => $this->getFakeToken(),
            'user' => ['nama' => 'Admin', 'role' => 'KAPRODI'],
            'permissions' => ['kurikulum.read'],
        ])->get(route('kurikulum.show', 3));

        $response->assertStatus(200);
        $response->assertSee('Kurikulum TRK 2025');
        $response->assertSee('Kecerdasan Buatan');
        $response->assertSee('TRK301');

        Http::assertSent(fn (HttpRequest $request) => str_contains($request->url(), '/api/kurikulum/3/mata-kuliah'));
    }
}
