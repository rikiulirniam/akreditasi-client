<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class LaporanAkhirTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_laporan_akhir_page_renders_successfully_with_consistent_theme(): void
    {
        Http::fake([
            '*/api/v1/projects*' => Http::response([
                'success' => true,
                'data' => [
                    [
                        'id' => 1,
                        'nama_project' => 'Akreditasi LAM INFOKOM D4 TRK 2027',
                        'status_project' => 'dinilai',
                        'tahun_target_submit' => 2027,
                    ],
                ],
            ]),
            '*/api/v1/master/kriteria*' => Http::response([
                'success' => true,
                'data' => [
                    ['id' => 1, 'kode_kriteria' => 'K1', 'nama_kriteria' => 'Visi, Misi, Tujuan, Strategi'],
                    ['id' => 2, 'kode_kriteria' => 'K2', 'nama_kriteria' => 'Tata Pamong dan Kerja Sama'],
                ],
            ]),
        ]);

        $response = $this->withSession([
            'access_token' => 'mock-jwt-token',
            'user' => [
                'nama' => 'Kuwat',
                'email' => 'kuwat@polines.ac.id',
                'role' => 'Kaprodi',
            ],
        ])->get(route('laporan-akhir.index'));

        $response->assertOk()
            ->assertSee('Laporan Akhir')
            ->assertSee('RINGKASAN KESIAPAN AKREDITASI')
            ->assertSee('Pilih Isi Laporan')
            ->assertSee('Ringkasan eksekutif')
            ->assertSee('Rekap skor per kriteria')
            ->assertSee('Cetak / Simpan PDF')
            ->assertSee('TAHUN AUDIT')
            ->assertSee('Pratinjau Lembar Laporan')
            ->assertSee('text-primary')
            ->assertSee('border-[#EAEAEA]');
    }

    public function test_laporan_akhir_download_stream_excel_and_word(): void
    {
        Http::fake([
            '*/api/v1/master/kriteria*' => Http::response([
                'success' => true,
                'data' => [
                    ['id' => 1, 'kode_kriteria' => 'K1', 'nama_kriteria' => 'Visi, Misi, Tujuan, Strategi'],
                ],
            ]),
        ]);

        $responseExcel = $this->withSession([
            'access_token' => 'mock-jwt-token',
            'user' => ['nama' => 'Kuwat', 'role' => 'Kaprodi'],
        ])->get(route('laporan-akhir.download', ['format' => 'excel', 'tahun' => 2026]));

        $responseExcel->assertOk()
            ->assertHeader('Content-Disposition', 'attachment; filename=Laporan_Akhir_Akreditasi_2026.csv');

        $responseWord = $this->withSession([
            'access_token' => 'mock-jwt-token',
            'user' => ['nama' => 'Kuwat', 'role' => 'Kaprodi'],
        ])->get(route('laporan-akhir.download', ['format' => 'word', 'tahun' => 2026]));

        $responseWord->assertOk()
            ->assertHeader('Content-Disposition', 'attachment; filename=Laporan_Akhir_Akreditasi_2026.doc');
    }
}
