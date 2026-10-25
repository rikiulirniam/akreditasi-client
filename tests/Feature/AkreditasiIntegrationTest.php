<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AkreditasiIntegrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        config(['services.akreditasi.base_url' => 'https://akreditasi.fayra.my.id']);
    }

    public function test_superadmin_dashboard_renders_live_data_successfully(): void
    {
        Http::fake([
            'https://akreditasi.fayra.my.id/api/v1/projects*' => Http::response([
                'success' => true,
                'data' => [
                    ['id' => 1, 'nama_project' => 'Project Akreditasi RPL 2026', 'status_project' => 'draft'],
                ],
            ]),
            'https://akreditasi.fayra.my.id/api/v1/master/lembaga-akreditasi*' => Http::response([
                'success' => true,
                'data' => [
                    ['id' => 1, 'nama_lembaga' => 'LAM-INFOKOM'],
                ],
            ]),
            'https://akreditasi.fayra.my.id/api/v1/master/versi-instrumen*' => Http::response([
                'success' => true,
                'data' => [
                    ['id' => 1, 'nomor_versi' => '2.1', 'status_aktif' => true],
                ],
            ]),
            'https://akreditasi.fayra.my.id/api/v1/master/kriteria*' => Http::response([
                'success' => true,
                'data' => [
                    ['id' => 1, 'kode_kriteria' => 'K-1', 'nama_kriteria' => 'Visi, Misi, Tujuan, dan Strategi'],
                ],
            ]),
            'https://akreditasi.fayra.my.id/api/v1/master/indikator*' => Http::response([
                'success' => true,
                'data' => [
                    [
                        'id' => 1,
                        'kode_indikator' => 'IND-1.1.1',
                        'deskripsi_indikator' => 'Kesesuaian VMTS',
                        'subkriteria' => ['kode_subkriteria' => 'SK-1.1'],
                    ],
                ],
            ]),
            'https://akreditasi.fayra.my.id/api/v1/master/standar-mutu*' => Http::response([
                'success' => true,
                'data' => [
                    ['id' => 1, 'kode_standar' => 'STD-01', 'nama_standar' => 'Standar VMTS'],
                ],
            ]),
        ]);

        $response = $this->withSession([
            'access_token' => 'admin-token',
            'user' => ['nama' => 'Superadmin', 'role' => 'SUPERADMIN'],
        ])->get(route('akreditasi.superadmin.dashboard'));

        $response->assertOk()
            ->assertSee('Selamat datang, Superadmin')
            ->assertSee('Lembaga Akreditasi')
            ->assertSee('Total Kriteria')
            ->assertSee('Project Akreditasi')
            ->assertSee('IND-1.1.1');
    }

    public function test_superadmin_lembaga_akreditasi_displays_live_data(): void
    {
        Http::fake([
            'https://akreditasi.fayra.my.id/api/v1/master/lembaga-akreditasi*' => Http::response([
                'success' => true,
                'data' => [
                    [
                        'id' => 1,
                        'nama_lembaga' => 'LAM-INFOKOM',
                        'keterangan' => 'Lembaga Akreditasi Mandiri Informatika',
                        'versi_instrumen' => [
                            ['id' => 1, 'nomor_versi' => '2.1'],
                        ],
                    ],
                ],
            ]),
        ]);

        $response = $this->withSession([
            'access_token' => 'admin-token',
            'user' => ['nama' => 'Superadmin', 'role' => 'SUPERADMIN'],
        ])->get(route('akreditasi.superadmin.lembaga_akreditasi'));

        $response->assertOk()
            ->assertSee('LAM-INFOKOM')
            ->assertSee('Lembaga Akreditasi Mandiri Informatika')
            ->assertSee('v2.1');
    }

    public function test_superadmin_kriteria_displays_live_data(): void
    {
        Http::fake([
            'https://akreditasi.fayra.my.id/api/v1/master/kriteria*' => Http::response([
                'success' => true,
                'data' => [
                    [
                        'id' => 1,
                        'kode_kriteria' => 'K-1',
                        'nama_kriteria' => 'Visi, Misi, Tujuan, dan Strategi',
                        'subkriteria' => [
                            ['id' => 1, 'kode_subkriteria' => 'SK-1.1'],
                        ],
                    ],
                ],
            ]),
            'https://akreditasi.fayra.my.id/api/v1/master/versi-instrumen*' => Http::response([
                'success' => true,
                'data' => [],
            ]),
        ]);

        $response = $this->withSession([
            'access_token' => 'admin-token',
            'user' => ['nama' => 'Superadmin', 'role' => 'SUPERADMIN'],
        ])->get(route('akreditasi.superadmin.kriteria'));

        $response->assertOk()
            ->assertSee('K-1')
            ->assertSee('Visi, Misi, Tujuan, dan Strategi')
            ->assertSee('1 Subkriteria');
    }

    public function test_kaprodi_project_page_renders_and_handles_creation(): void
    {
        Http::fake([
            'https://akreditasi.fayra.my.id/api/v1/projects*' => Http::response([
                'success' => true,
                'data' => [
                    [
                        'id' => 1,
                        'nama_project' => 'Project Akreditasi RPL 2026',
                        'tahun_target_submit' => 2026,
                        'status_project' => 'draft',
                        'jenjang' => ['nama_jenjang' => 'S1'],
                        'jenis_instrumen' => ['nama_jenis' => 'APS'],
                        'versi_instrumen' => [
                            'nomor_versi' => '2.1',
                            'lembaga' => ['nama_lembaga' => 'LAM INFOKOM'],
                        ],
                    ],
                ],
            ]),
            'https://akreditasi.fayra.my.id/api/v1/master/lembaga-akreditasi*' => Http::response([
                'success' => true,
                'data' => [],
            ]),
            'https://akreditasi.fayra.my.id/api/v1/master/versi-instrumen*' => Http::response([
                'success' => true,
                'data' => [],
            ]),
        ]);

        $response = $this->withSession([
            'access_token' => 'kaprodi-token',
            'user' => ['nama' => 'Kaprodi', 'role' => 'Kaprodi', 'id_prodi' => 1],
        ])->get(route('kaprodi.project'));

        $response->assertOk()
            ->assertSee('Dashboard Project Akreditasi')
            ->assertSee('Project Akreditasi RPL 2026')
            ->assertSee('v2.1');
    }
}
