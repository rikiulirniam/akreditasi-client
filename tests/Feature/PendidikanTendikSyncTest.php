<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PendidikanTendikSyncTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
    }

    public function test_tendik_without_local_records_shows_api_education_on_both_pages(): void
    {
        $this->withoutExceptionHandling();
        Http::fake([
            'https://tridharma.fayra.my.id/api/tendik/99' => Http::response([
                'success' => true,
                'data' => [
                    'id_tendik' => 99,
                    'nama_tendik' => 'Putri Aulya',
                    'jabatan_tendik' => 'Staff IT',
                    'nip' => '199501012022012001',
                    'id_pendidikan' => 88,
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
            'https://tridharma.fayra.my.id/api/tendik?*' => Http::response([
                'success' => true,
                'data' => [
                    ['id_tendik' => 99, 'nama_tendik' => 'Putri Aulya'],
                ],
            ], 200),
            'https://tridharma.fayra.my.id/api/tendik' => Http::response([
                'success' => true,
                'data' => [
                    ['id_tendik' => 99, 'nama_tendik' => 'Putri Aulya'],
                ],
            ], 200),
            'https://tridharma.fayra.my.id/api/pendidikan/88' => Http::response([
                'success' => true,
                'data' => [
                    'id_pendidikan' => 88,
                    'universitas' => 'Universitas Diponegoro',
                    'program_studi' => 'Teknik Informatika',
                    'jenjang' => 'D4/S1',
                    'tahun_lulus' => 2021,
                    'gelar' => 'S.Kom',
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

    public function test_inputted_education_via_cache_is_synchronized_on_both_pages(): void
    {
        // Pre-populate cache with 2 pendidikan IDs for tendik 99
        Cache::put('tendik_kualifikasi_99', [201, 202], now()->addYears(5));

        Http::fake([
            'https://tridharma.fayra.my.id/api/tendik/99' => Http::response([
                'success' => true,
                'data' => [
                    'id_tendik' => 99,
                    'nama_tendik' => 'Putri Aulya',
                    'jabatan_tendik' => 'Staff IT',
                    'nip' => '199501012022012001',
                    'id_pendidikan' => 202,
                    'kualifikasi' => 'D4/S1',
                    'pendidikan' => null,
                ],
            ], 200),
            'https://tridharma.fayra.my.id/api/tendik?*' => Http::response([
                'success' => true,
                'data' => [
                    ['id_tendik' => 99, 'nama_tendik' => 'Putri Aulya'],
                ],
            ], 200),
            'https://tridharma.fayra.my.id/api/tendik' => Http::response([
                'success' => true,
                'data' => [
                    ['id_tendik' => 99, 'nama_tendik' => 'Putri Aulya'],
                ],
            ], 200),
            'https://tridharma.fayra.my.id/api/pendidikan/201' => Http::response([
                'success' => true,
                'data' => [
                    'id_pendidikan' => 201,
                    'universitas' => 'Universitas Diponegoro',
                    'program_studi' => 'Teknik Sistem Komputer',
                    'jenjang' => 'S3',
                    'tahun_lulus' => 2023,
                    'gelar' => 'Dr',
                ],
            ], 200),
            'https://tridharma.fayra.my.id/api/pendidikan/202' => Http::response([
                'success' => true,
                'data' => [
                    'id_pendidikan' => 202,
                    'universitas' => 'Universitas Indonesia',
                    'program_studi' => 'Ilmu Komputer',
                    'jenjang' => 'D4/S1',
                    'tahun_lulus' => 2015,
                    'gelar' => 'S.Kom',
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

    public function test_adding_education_adds_to_existing_education_and_does_not_replace(): void
    {
        Http::fake([
            'https://tridharma.fayra.my.id/api/tendik/99' => Http::response([
                'success' => true,
                'data' => [
                    'id_tendik' => 99,
                    'nama_tendik' => 'Putri Aulya',
                    'jabatan_tendik' => 'Staff IT',
                    'nip' => '199501012022012001',
                    'id_pendidikan' => 88,
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
            'https://tridharma.fayra.my.id/api/tendik?*' => Http::response([
                'success' => true,
                'data' => [
                    ['id_tendik' => 99, 'nama_tendik' => 'Putri Aulya'],
                ],
            ], 200),
            'https://tridharma.fayra.my.id/api/tendik' => Http::response([
                'success' => true,
                'data' => [
                    ['id_tendik' => 99, 'nama_tendik' => 'Putri Aulya'],
                ],
            ], 200),
            'https://tridharma.fayra.my.id/api/pendidikan/88' => Http::response([
                'success' => true,
                'data' => [
                    'id_pendidikan' => 88,
                    'universitas' => 'Universitas Diponegoro',
                    'program_studi' => 'Teknik Informatika',
                    'jenjang' => 'D4/S1',
                    'tahun_lulus' => 2021,
                    'gelar' => 'S.Kom',
                ],
            ], 200),
            'https://tridharma.fayra.my.id/api/pendidikan/999' => Http::response([
                'success' => true,
                'data' => [
                    'id_pendidikan' => 999,
                    'universitas' => 'Universitas Diponegoro',
                    'program_studi' => 'Teknik Informatika',
                    'jenjang' => 'S2',
                    'tahun_lulus' => 2026,
                    'gelar' => 'M.Kom',
                ],
            ], 200),
            'https://tridharma.fayra.my.id/api/pendidikan' => Http::response([
                'success' => true,
                'data' => [
                    'id_pendidikan' => 999,
                    'universitas' => 'Universitas Diponegoro',
                    'program_studi' => 'Teknik Informatika',
                    'jenjang' => 'S2',
                    'tahun_lulus' => 2026,
                ],
            ], 201),
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

        // Add S2 education for tendik 99
        $storeResp = $this->withSession(['access_token' => 'mock-jwt-token'])
            ->post(route('pendidikan.store'), [
                'universitas' => 'Universitas Diponegoro',
                'program_studi' => 'Teknik Informatika',
                'jenjang' => 'S2',
                'tahun_lulus' => 2026,
                'id_tendik' => 99,
            ]);

        $storeResp->assertRedirect(route('pendidikan.index', ['id_tendik' => 99]));

        // Verify cache has both IDs (88 from API preservation + 999 from new creation)
        $cachedIds = Cache::get('tendik_kualifikasi_99', []);
        $this->assertContains(88, $cachedIds, 'Original API pendidikan ID should be in cache');
        $this->assertContains(999, $cachedIds, 'Newly created pendidikan ID should be in cache');

        // Visit Page 1: MUST see both S2 (2026) AND original D4/S1 (2021)
        $pendidikanResp = $this->withSession(['access_token' => 'mock-jwt-token'])
            ->get(route('pendidikan.index', ['id_tendik' => 99]));

        $pendidikanResp->assertOk()
            ->assertSee('Universitas Diponegoro')
            ->assertSee('Teknik Informatika')
            ->assertSee('S2')
            ->assertSee('2026')
            ->assertSee('D4/S1')
            ->assertSee('2021');

        // Visit Page 2: MUST also see both S2 (2026) AND D4/S1 (2021)
        $detailResp = $this->withSession(['access_token' => 'mock-jwt-token'])
            ->get(route('tenaga-kependidikan.show', 99));

        $detailResp->assertOk()
            ->assertSee('S.Kom')
            ->assertSee('D4/S1')
            ->assertSee('2021')
            ->assertSee('M.Kom')
            ->assertSee('S2')
            ->assertSee('2026');
    }

    public function test_deleting_education_removes_it_from_both_pages(): void
    {
        Cache::put('tendik_kualifikasi_99', [88, 999], now()->addYears(5));

        Http::fake([
            'https://tridharma.fayra.my.id/api/tendik/99' => Http::response([
                'success' => true,
                'data' => [
                    'id_tendik' => 99,
                    'nama_tendik' => 'Putri Aulya',
                    'jabatan_tendik' => 'Staff IT',
                    'nip' => '199501012022012001',
                    'id_pendidikan' => 999,
                    'kualifikasi' => 'S2',
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
            'https://tridharma.fayra.my.id/api/tendik?*' => Http::response([
                'success' => true,
                'data' => [
                    ['id_tendik' => 99, 'nama_tendik' => 'Putri Aulya'],
                ],
            ], 200),
            'https://tridharma.fayra.my.id/api/tendik' => Http::response([
                'success' => true,
                'data' => [
                    ['id_tendik' => 99, 'nama_tendik' => 'Putri Aulya'],
                ],
            ], 200),
            'https://tridharma.fayra.my.id/api/pendidikan/88' => Http::response([
                'success' => true,
                'data' => [
                    'id_pendidikan' => 88,
                    'universitas' => 'Universitas Diponegoro',
                    'program_studi' => 'Teknik Informatika',
                    'jenjang' => 'D4/S1',
                    'tahun_lulus' => 2021,
                    'gelar' => 'S.Kom',
                ],
            ], 200),
            'https://tridharma.fayra.my.id/api/pendidikan/999' => Http::response([
                'success' => true,
                'data' => [
                    'id_pendidikan' => 999,
                    'universitas' => 'Universitas Gadjah Mada',
                    'program_studi' => 'Sistem Informasi',
                    'jenjang' => 'S2',
                    'tahun_lulus' => 2025,
                    'gelar' => 'M.Kom',
                ],
            ], 200),
            'https://tridharma.fayra.my.id/api/pendidikan/999*' => Http::response([
                'success' => true,
                'message' => 'Pendidikan berhasil dihapus',
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

        // Delete pendidikan 999
        $deleteResp = $this->withSession(['access_token' => 'mock-jwt-token'])
            ->delete(route('pendidikan.destroy', ['pendidikan' => 999, 'id_tendik' => 99]));

        $deleteResp->assertRedirect(route('pendidikan.index', ['id_tendik' => 99]));

        // Visit Page 1 (Pendidikan list for this Tendik): 999 should be gone, 88 still present
        $pendidikanResp = $this->withSession(['access_token' => 'mock-jwt-token'])
            ->get(route('pendidikan.index', ['id_tendik' => 99]));

        $pendidikanResp->assertOk()
            ->assertSee('Universitas Diponegoro')
            ->assertSee('Teknik Informatika')
            ->assertDontSee('Universitas Gadjah Mada')
            ->assertDontSee('Sistem Informasi');

        // Visit Page 2 (Detail Tendik Kualifikasi Card): 999 should be gone, 88 still present
        $detailResp = $this->withSession(['access_token' => 'mock-jwt-token'])
            ->get(route('tenaga-kependidikan.show', 99));

        $detailResp->assertOk()
            ->assertSee('Universitas Diponegoro')
            ->assertSee('Teknik Informatika')
            ->assertDontSee('Universitas Gadjah Mada')
            ->assertDontSee('Sistem Informasi');
    }
}
