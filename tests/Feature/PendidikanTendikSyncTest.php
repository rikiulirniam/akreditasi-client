<?php

namespace Tests\Feature;

use App\Models\Kualifikasi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PendidikanTendikSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_tendik_without_local_records_shows_api_education_on_both_pages(): void
    {
        Http::fake([
            'https://tridharma.fayra.my.id/api/tendik/99' => Http::response([
                'success' => true,
                'data' => [
                    'id_tendik' => 99,
                    'nama_tendik' => 'Putri Aulya',
                    'jabatan_tendik' => 'Staff IT',
                    'nip' => '199501012022012001',
                    'kualifikasi' => 'D4/S1',
                    'pendidikan' => [
                        'id_pendidikan' => 88,
                        'universitas' => 'Universitas Diponegoro',
                        'program_studi' => 'Teknik Informatika',
                        'jenjang' => 'D4/S1',
                        'tahun_lulus' => 2021,
                        'gelar' => 'S.Kom',
                    ],
                ],
            ], 200),
            'https://tridharma.fayra.my.id/api/tendik*' => Http::response([
                'success' => true,
                'data' => [
                    ['id_tendik' => 99, 'nama_tendik' => 'Putri Aulya'],
                ],
            ], 200),
            'https://tridharma.fayra.my.id/api/pendidikan*' => Http::response([
                'success' => true,
                'data' => [],
            ], 200),
            'https://tridharma.fayra.my.id/api/dosen*' => Http::response([
                'success' => true,
                'data' => [],
            ], 200),
            'https://tridharma.fayra.my.id/api/sertifikasi*' => Http::response([
                'success' => true,
                'data' => [],
            ], 200),
        ]);

        // 1. Visit Page 2 (Detail Tendik)
        $detailResp = $this->withSession(['access_token' => 'mock-jwt-token'])
            ->get(route('tenaga-kependidikan.show', 99));

        $detailResp->assertOk()
            ->assertSee('Putri Aulya')
            ->assertSee('S.Kom')
            ->assertSee('D4/S1')
            ->assertSee('Universitas Diponegoro')
            ->assertSee('Teknik Informatika')
            ->assertSee('2021');

        // 2. Visit Page 1 (Pendidikan list for this Tendik)
        $pendidikanResp = $this->withSession(['access_token' => 'mock-jwt-token'])
            ->get(route('pendidikan.index', ['id_tendik' => 99]));

        $pendidikanResp->assertOk()
            ->assertSee('Putri Aulya')
            ->assertSee('Universitas Diponegoro')
            ->assertSee('Teknik Informatika')
            ->assertSee('D4/S1')
            ->assertSee('2021');
    }

    public function test_inputted_local_education_is_synchronized_on_both_pages(): void
    {
        Http::fake([
            'https://tridharma.fayra.my.id/api/tendik/99' => Http::response([
                'success' => true,
                'data' => [
                    'id_tendik' => 99,
                    'nama_tendik' => 'Putri Aulya',
                    'jabatan_tendik' => 'Staff IT',
                    'nip' => '199501012022012001',
                    'kualifikasi' => 'D4/S1',
                    'pendidikan' => null,
                ],
            ], 200),
            'https://tridharma.fayra.my.id/api/tendik*' => Http::response([
                'success' => true,
                'data' => [
                    ['id_tendik' => 99, 'nama_tendik' => 'Putri Aulya'],
                ],
            ], 200),
            'https://tridharma.fayra.my.id/api/pendidikan*' => Http::response([
                'success' => true,
                'data' => [],
            ], 200),
            'https://tridharma.fayra.my.id/api/dosen*' => Http::response([
                'success' => true,
                'data' => [],
            ], 200),
            'https://tridharma.fayra.my.id/api/sertifikasi*' => Http::response([
                'success' => true,
                'data' => [],
            ], 200),
        ]);

        // Input 2 education records for tendik 99 into local database
        Kualifikasi::create([
            'tendik_api_id' => 99,
            'gelar' => 'Dr',
            'jenjang' => 'S3',
            'institusi' => 'Universitas Diponegoro',
            'program_studi' => 'Teknik Sistem Komputer',
            'tahun_lulus' => '2023',
        ]);

        Kualifikasi::create([
            'tendik_api_id' => 99,
            'gelar' => 'S.Kom',
            'jenjang' => 'D4/S1',
            'institusi' => 'Universitas Indonesia',
            'program_studi' => 'Ilmu Komputer',
            'tahun_lulus' => '2015',
        ]);

        // 1. Visit Page 1 (Pendidikan list for Tendik)
        $pendidikanResp = $this->withSession(['access_token' => 'mock-jwt-token'])
            ->get(route('pendidikan.index', ['id_tendik' => 99]));

        $pendidikanResp->assertOk()
            ->assertSee('Putri Aulya')
            ->assertSee('Universitas Diponegoro')
            ->assertSee('Teknik Sistem Komputer')
            ->assertSee('S3')
            ->assertSee('2023')
            ->assertSee('Universitas Indonesia')
            ->assertSee('Ilmu Komputer')
            ->assertSee('D4/S1')
            ->assertSee('2015');

        // 2. Visit Page 2 (Detail Tendik Kualifikasi Card)
        $detailResp = $this->withSession(['access_token' => 'mock-jwt-token'])
            ->get(route('tenaga-kependidikan.show', 99));

        $detailResp->assertOk()
            ->assertSee('Putri Aulya')
            ->assertSee('Universitas Diponegoro')
            ->assertSee('Teknik Sistem Komputer')
            ->assertSee('2023')
            ->assertSee('Universitas Indonesia')
            ->assertSee('Ilmu Komputer')
            ->assertSee('2015');
    }
}
