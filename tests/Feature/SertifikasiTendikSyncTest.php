<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SertifikasiTendikSyncTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    public function test_detail_page_and_sertifikasi_page_display_synced_tendik_certifications(): void
    {
        Http::fake([
            'https://tridharma.fayra.my.id/api/tendik/55' => Http::response([
                'success' => true,
                'data' => [
                    'id_tendik' => 55,
                    'nama_tendik' => 'Budi Santoso',
                    'nip' => '198501012010011001',
                    'jabatan' => 'Laboran Komputer',
                    'kualifikasi' => 'S1',
                ],
            ], 200),
            'https://tridharma.fayra.my.id/api/tendik*' => Http::response([
                'success' => true,
                'data' => [
                    ['id_tendik' => 55, 'nama_tendik' => 'Budi Santoso'],
                ],
            ], 200),
            'https://tridharma.fayra.my.id/api/sertifikasi*' => Http::response([
                'success' => true,
                'code' => 200,
                'data' => [
                    [
                        'id' => 101,
                        'id_tendik' => 55,
                        'jenis_sertifikasi' => 'Sertifikat Mikrotik MTCNA',
                        'nomor_sertifikat' => 'MTCNA-2025-01',
                        'lembaga_penerbit' => 'MikroTik Academy',
                        'tahun_sertifikasi' => '2025',
                        'status_sertifikasi' => 'Aktif',
                    ],
                ],
            ], 200),
            'https://tridharma.fayra.my.id/api/pendidikan*' => Http::response([
                'success' => true,
                'data' => [],
            ], 200),
        ]);

        // 1. Visit Detail Tendik (Page 1)
        $detailResp = $this->withSession(['access_token' => 'mock-jwt-token'])
            ->get(route('tenaga-kependidikan.show', 55));

        $detailResp->assertOk()
            ->assertSee('Budi Santoso')
            ->assertSee('Sertifikat Mikrotik MTCNA')
            ->assertSee('MikroTik Academy')
            ->assertSee('2025');

        // 2. Visit Sertifikasi Page for this Tendik (Page 2)
        $sertifikasiResp = $this->withSession(['access_token' => 'mock-jwt-token'])
            ->get(route('sertifikasi.index', ['tipe_pemilik' => 'tendik', 'id_tendik' => 55]));

        $sertifikasiResp->assertOk()
            ->assertSee('Budi Santoso')
            ->assertSee('Sertifikasi')
            ->assertSee('MTCNA-2025-01');
    }

    public function test_adding_certificate_via_tendik_store_syncs_to_both_pages(): void
    {
        Http::fake([
            'https://tridharma.fayra.my.id/api/tendik/55' => Http::response([
                'success' => true,
                'data' => [
                    'id_tendik' => 55,
                    'nama_tendik' => 'Budi Santoso',
                ],
            ], 200),
            'https://tridharma.fayra.my.id/api/sertifikasi' => Http::response([
                'success' => true,
                'code' => 201,
                'message' => 'Sertifikasi berhasil ditambahkan.',
                'data' => ['id' => 202],
            ], 201),
            'https://tridharma.fayra.my.id/api/sertifikasi*' => Http::response([
                'success' => true,
                'code' => 200,
                'data' => [],
            ], 200),
            'https://tridharma.fayra.my.id/api/pendidikan*' => Http::response([
                'success' => true,
                'data' => [],
            ], 200),
        ]);

        // Post new certification via tendik route
        $storeResp = $this->withSession(['access_token' => 'mock-jwt-token'])
            ->post(route('tenaga-pendidikan.sertifikasi.store', 55), [
                'jenis_sertifikasi' => 'Sertifikat Cisco CCNA',
                'tahun_sertifikasi' => '2026',
                'nomor_sertifikat' => 'CCNA-9988',
                'lembaga_penerbit' => 'Cisco Systems',
                'status_sertifikasi' => 'Aktif',
            ]);

        $storeResp->assertRedirect(route('tenaga-pendidikan.show', 55));

        // Check Detail page
        $detailResp = $this->withSession(['access_token' => 'mock-jwt-token'])
            ->get(route('tenaga-pendidikan.show', 55));
        $detailResp->assertOk()
            ->assertSee('Sertifikat Cisco CCNA')
            ->assertSee('Cisco Systems');

        // Check Sertifikasi table page
        $sertifikasiResp = $this->withSession(['access_token' => 'mock-jwt-token'])
            ->get(route('sertifikasi.index', ['tipe_pemilik' => 'tendik', 'id_tendik' => 55]));
        $sertifikasiResp->assertOk()
            ->assertSee('CCNA-9988');
    }

    public function test_deleting_certificate_syncs_to_both_pages(): void
    {
        Http::fake([
            'https://tridharma.fayra.my.id/api/tendik/55' => Http::response([
                'success' => true,
                'data' => [
                    'id_tendik' => 55,
                    'nama_tendik' => 'Budi Santoso',
                ],
            ], 200),
            'https://tridharma.fayra.my.id/api/sertifikasi/303' => Http::response([
                'success' => true,
                'code' => 200,
                'message' => 'Sertifikasi berhasil dihapus.',
            ], 200),
            'https://tridharma.fayra.my.id/api/sertifikasi*' => Http::response([
                'success' => true,
                'code' => 200,
                'data' => [
                    [
                        'id' => 303,
                        'id_tendik' => 55,
                        'jenis_sertifikasi' => 'Sertifikat AWS Cloud Practitioner',
                        'nomor_sertifikat' => 'AWS-303',
                        'lembaga_penerbit' => 'Amazon Web Services',
                        'tahun_sertifikasi' => '2024',
                    ],
                ],
            ], 200),
            'https://tridharma.fayra.my.id/api/pendidikan*' => Http::response([
                'success' => true,
                'data' => [],
            ], 200),
        ]);

        // Delete the certificate
        $deleteResp = $this->withSession(['access_token' => 'mock-jwt-token'])
            ->delete(route('tenaga-pendidikan.sertifikasi.destroy', [55, 303]));

        $deleteResp->assertRedirect(route('tenaga-pendidikan.show', 55));

        // Ensure it is no longer shown on Detail page
        $detailResp = $this->withSession(['access_token' => 'mock-jwt-token'])
            ->get(route('tenaga-pendidikan.show', 55));
        $detailResp->assertOk()
            ->assertDontSee('Sertifikat AWS Cloud Practitioner');

        // Ensure it is also not shown on Sertifikasi page
        $sertifikasiResp = $this->withSession(['access_token' => 'mock-jwt-token'])
            ->get(route('sertifikasi.index', ['tipe_pemilik' => 'tendik', 'id_tendik' => 55]));
        $sertifikasiResp->assertOk()
            ->assertDontSee('AWS-303');
    }
}
