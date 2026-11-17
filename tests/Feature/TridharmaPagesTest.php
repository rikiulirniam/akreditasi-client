<?php

namespace Tests\Feature;

use Illuminate\Http\Client\Request as ClientRequest;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TridharmaPagesTest extends TestCase
{
    private const API = 'https://tridharma.fayra.my.id/api';

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @return array<string, mixed>
     */
    private function paginated(array $items, ?int $total = null): array
    {
        return [
            'success' => true,
            'data' => [
                'current_page' => 1,
                'data' => $items,
                'from' => $items === [] ? null : 1,
                'to' => count($items),
                'total' => $total ?? count($items),
                'per_page' => 10,
                'last_page' => 1,
            ],
        ];
    }

    public function test_penelitian_index_lists_only_penelitian_and_sends_filters(): void
    {
        Http::fake([
            self::API.'/kegiatan*' => Http::response($this->paginated([[
                'id_kegiatan' => 7,
                'judul_kegiatan' => 'Sistem Deteksi Dini Banjir Berbasis IoT',
                'tahun_kegiatan' => 2025,
                'status_kegiatan' => 'Selesai',
                'anggota' => [
                    ['peran' => 'Anggota', 'dosen' => ['nama_dosen' => 'Dosen Anggota']],
                    ['peran' => 'Ketua', 'dosen' => ['nama_dosen' => 'Dr. Ketua Peneliti']],
                ],
                'luaran' => [['id_luaran' => 1], ['id_luaran' => 2]],
            ]])),
        ]);

        $this->withSession(['access_token' => 'test-token'])
            ->get(route('penelitian.index', ['search' => 'banjir', 'tahun' => 2025, 'status_kegiatan' => 'Selesai']))
            ->assertOk()
            ->assertSee('Sistem Deteksi Dini Banjir Berbasis IoT')
            ->assertSee('Dr. Ketua Peneliti')
            ->assertSee('Judul Penelitian')
            ->assertSee(route('penelitian.show', 7), false);

        Http::assertSent(fn (ClientRequest $request): bool => str_starts_with($request->url(), self::API.'/kegiatan?')
            && $request->hasHeader('Authorization', 'Bearer test-token')
            && $request['jenis'] === 'penelitian'
            && $request['search'] === 'banjir'
            && (int) $request['tahun_kegiatan'] === 2025
            && $request['status_kegiatan'] === 'Selesai');
    }

    public function test_pkm_index_requests_pkm_kegiatan(): void
    {
        Http::fake([self::API.'/kegiatan*' => Http::response($this->paginated([]))]);

        $this->withSession(['access_token' => 'test-token'])
            ->get(route('pkm.index'))
            ->assertOk()
            ->assertSee('Belum Ada Data PkM');

        Http::assertSent(fn (ClientRequest $request): bool => $request['jenis'] === 'pkm');
    }

    public function test_index_shows_api_error_message(): void
    {
        Http::fake([self::API.'/kegiatan*' => Http::response(['success' => false, 'message' => 'Akses ditolak.'], 403)]);

        $this->withSession(['access_token' => 'test-token'])
            ->get(route('penelitian.index'))
            ->assertOk()
            ->assertSee('Akses ditolak.');
    }

    public function test_index_redirects_to_login_when_token_is_rejected(): void
    {
        Http::fake([self::API.'/kegiatan*' => Http::response(['message' => 'Unauthorized'], 401)]);

        $this->withSession(['access_token' => 'expired-token'])
            ->get(route('pkm.index'))
            ->assertRedirect(route('login'));
    }

    public function test_show_displays_members_funding_and_linked_outputs(): void
    {
        Http::fake([
            self::API.'/kegiatan/7' => Http::response(['success' => true, 'data' => [
                'id_kegiatan' => 7,
                'judul_kegiatan' => 'Sistem Deteksi Dini Banjir Berbasis IoT',
                'tahun_kegiatan' => 2025,
                'status_kegiatan' => 'Selesai',
                'jenis_kegiatan' => ['nama_jenis_kegiatan' => 'Penelitian'],
                'anggota' => [['peran' => 'Ketua', 'dosen' => ['nama_dosen' => 'Dr. Ketua Peneliti']]],
                'pendanaan' => [['sumber_dana' => 'DIPA Polines', 'jumlah_dana' => 15000000, 'tahun_pendanaan' => 2025]],
                'luaran' => [[
                    'nama_jurnal' => 'Jurnal IoT Indonesia',
                    'jenis_luaran' => ['nama_jenis_luaran' => 'Jurnal'],
                    'dosen' => ['nama_dosen' => 'Dr. Ketua Peneliti'],
                ]],
            ]]),
        ]);

        $this->withSession(['access_token' => 'test-token'])
            ->get(route('penelitian.show', 7))
            ->assertOk()
            ->assertSee('Dr. Ketua Peneliti')
            ->assertSee('DIPA Polines')
            ->assertSee('Rp 15.000.000')
            ->assertSee('Jurnal IoT Indonesia');
    }

    public function test_show_redirects_to_the_correct_menu_when_jenis_does_not_match(): void
    {
        Http::fake([
            self::API.'/kegiatan/9' => Http::response(['success' => true, 'data' => [
                'id_kegiatan' => 9,
                'judul_kegiatan' => 'Pelatihan IoT UMKM',
                'jenis_kegiatan' => ['nama_jenis_kegiatan' => 'PkM'],
            ]]),
        ]);

        $this->withSession(['access_token' => 'test-token'])
            ->get(route('penelitian.show', 9))
            ->assertRedirect(route('pkm.show', 9));
    }

    public function test_show_returns_404_when_kegiatan_not_found(): void
    {
        Http::fake([self::API.'/kegiatan/404' => Http::response(['success' => false, 'message' => 'Data kegiatan tidak ditemukan.'], 404)]);

        $this->withSession(['access_token' => 'test-token'])
            ->get(route('pkm.show', 404))
            ->assertNotFound();
    }

    public function test_luaran_defaults_to_artikel_tab_and_maps_columns(): void
    {
        Http::fake([
            self::API.'/luaran*' => Http::response($this->paginated([[
                'id_luaran' => 3,
                'nama_jurnal' => 'IEEE Transactions on Industrial Informatics',
                'indeksasi' => 'Scopus Q1',
                'doi' => '10.1109/TII.2026.3129841',
                'tahun_terbit' => 2026,
                'tautan_jurnal' => 'https://doi.org/10.1109/TII.2026.3129841',
                'dosen' => ['nama_dosen' => 'Dr. Penulis'],
            ]])),
        ]);

        $this->withSession(['access_token' => 'test-token'])
            ->get(route('luaran.index'))
            ->assertOk()
            ->assertSee('IEEE Transactions on Industrial Informatics')
            ->assertSee('Scopus Q1')
            ->assertSee('Dr. Penulis')
            ->assertSee('2026')
            ->assertSee('href="https://doi.org/10.1109/TII.2026.3129841"', false)
            ->assertSee(route('luaran.index', ['kategori' => 'hki']), false);

        Http::assertSent(fn (ClientRequest $request): bool => $request['kategori'] === 'artikel'
            && $request->hasHeader('Authorization', 'Bearer test-token'));
    }

    public function test_luaran_hki_tab_uses_hki_columns_and_ignores_unsafe_links(): void
    {
        Http::fake([
            self::API.'/luaran*' => Http::response($this->paginated([[
                'id_luaran' => 4,
                'judul_hak_cipta' => 'Program Komputer Edge-AI Smart Sensor',
                'jenis_hki' => 'Hak Cipta Perangkat Lunak',
                'nomor_sertifikat_hki' => '000492812',
                'status_hki' => 'Bersertifikat',
                'tanggal_terbit_sertifikat_hki' => '2025-08-14',
                'tautan_hki' => 'javascript:alert(1)',
                'dosen' => ['nama_dosen' => 'Dr. Pencipta'],
            ]])),
        ]);

        $this->withSession(['access_token' => 'test-token'])
            ->get(route('luaran.index', ['kategori' => 'hki', 'search' => 'sensor', 'tahun' => 2025]))
            ->assertOk()
            ->assertSee('Program Komputer Edge-AI Smart Sensor')
            ->assertSee('000492812')
            ->assertSee('2025')
            ->assertDontSee('javascript:alert(1)', false);

        Http::assertSent(fn (ClientRequest $request): bool => $request['kategori'] === 'hki'
            && $request['search'] === 'sensor'
            && (int) $request['tahun'] === 2025);
    }

    private const XLSX = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';

    public function test_penelitian_export_proxies_the_excel_file_with_active_filters(): void
    {
        Http::fake([self::API.'/kegiatan/export*' => Http::response('FAKE-XLSX-BYTES', 200, ['Content-Type' => self::XLSX])]);

        $response = $this->withSession(['access_token' => 'test-token'])
            ->get(route('penelitian.export', ['search' => 'banjir', 'tahun' => 2025, 'status_kegiatan' => 'Selesai', 'page' => 3]));

        $response->assertOk()
            ->assertHeader('Content-Type', self::XLSX);
        $this->assertSame('FAKE-XLSX-BYTES', $response->getContent());
        $this->assertMatchesRegularExpression('/^attachment; filename="?penelitian_\d{8}\.xlsx"?$/', $response->headers->get('Content-Disposition'));

        Http::assertSent(fn (ClientRequest $request): bool => str_starts_with($request->url(), self::API.'/kegiatan/export?')
            && $request->hasHeader('Authorization', 'Bearer test-token')
            && $request['jenis'] === 'penelitian'
            && $request['search'] === 'banjir'
            && (int) $request['tahun_kegiatan'] === 2025
            && $request['status_kegiatan'] === 'Selesai'
            && ! isset($request['page']));
    }

    public function test_pkm_export_requests_pkm_kegiatan(): void
    {
        Http::fake([self::API.'/kegiatan/export*' => Http::response('x', 200, ['Content-Type' => self::XLSX])]);

        $this->withSession(['access_token' => 'test-token'])
            ->get(route('pkm.export'))
            ->assertOk();

        Http::assertSent(fn (ClientRequest $request): bool => $request['jenis'] === 'pkm');
    }

    public function test_luaran_export_sends_kategori_and_filters(): void
    {
        Http::fake([self::API.'/luaran/export*' => Http::response('FAKE-XLSX-BYTES', 200, ['Content-Type' => self::XLSX])]);

        $response = $this->withSession(['access_token' => 'test-token'])
            ->get(route('luaran.export', ['kategori' => 'hki', 'search' => 'sensor', 'tahun' => 2025]));

        $response->assertOk();
        $this->assertMatchesRegularExpression('/filename="?luaran-hki_\d{8}\.xlsx"?/', $response->headers->get('Content-Disposition'));

        Http::assertSent(fn (ClientRequest $request): bool => str_starts_with($request->url(), self::API.'/luaran/export?')
            && $request['kategori'] === 'hki'
            && $request['search'] === 'sensor'
            && (int) $request['tahun'] === 2025);
    }

    public function test_export_backend_failure_returns_back_with_error_toast(): void
    {
        Http::fake([self::API.'/luaran/export*' => Http::response(['success' => false, 'message' => 'Ekstensi zip tidak aktif.'], 500)]);

        $this->from(route('luaran.index', ['kategori' => 'artikel']))
            ->withSession(['access_token' => 'test-token'])
            ->get(route('luaran.export', ['kategori' => 'artikel']))
            ->assertRedirect(route('luaran.index', ['kategori' => 'artikel']))
            ->assertSessionHas('toast.message', 'Ekstensi zip tidak aktif.');
    }

    public function test_export_redirects_to_login_when_token_is_rejected(): void
    {
        Http::fake([self::API.'/kegiatan/export*' => Http::response(['message' => 'Unauthorized'], 401)]);

        $this->withSession(['access_token' => 'expired-token'])
            ->get(route('penelitian.export'))
            ->assertRedirect(route('login'));
    }

    public function test_list_pages_show_download_button_with_active_filters(): void
    {
        Http::fake([
            self::API.'/kegiatan*' => Http::response($this->paginated([['id_kegiatan' => 1, 'judul_kegiatan' => 'Judul A', 'anggota' => [], 'luaran' => []]])),
            self::API.'/luaran*' => Http::response($this->paginated([['id_luaran' => 1, 'nama_jurnal' => 'Jurnal A', 'dosen' => ['nama_dosen' => 'X']]])),
        ]);

        $this->withSession(['access_token' => 'test-token'])
            ->get(route('penelitian.index', ['search' => 'abc', 'page' => 2]))
            ->assertOk()
            ->assertSee('Download Excel')
            ->assertSee('href="'.route('penelitian.export', ['search' => 'abc']).'"', false);

        $this->withSession(['access_token' => 'test-token'])
            ->get(route('luaran.index', ['kategori' => 'buku', 'tahun' => 2024]))
            ->assertOk()
            ->assertSee('href="'.route('luaran.export', ['kategori' => 'buku', 'tahun' => 2024]).'"', false);
    }

    public function test_luaran_rejects_unknown_kategori(): void
    {
        Http::fake();

        $this->withSession(['access_token' => 'test-token'])
            ->get('/luaran/tidak-ada')
            ->assertNotFound();

        Http::assertNothingSent();
    }
}
