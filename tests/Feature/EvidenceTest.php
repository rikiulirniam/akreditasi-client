<?php

namespace Tests\Feature;

use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class EvidenceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    private function authenticate(): static
    {
        return $this->withSession(['access_token' => 'test-token']);
    }

    public function test_create_sends_only_fields_supported_by_the_api(): void
    {
        Http::fake([
            '*/api/evidence' => Http::response(['success' => true, 'message' => 'Evidence dibuat.'], 201),
        ]);

        $response = $this->authenticate()->from(route('evidence.index'))->post(route('evidence.store'), [
            'nama_file_display' => 'Proposal Kegiatan 2026',
            'tanggal_upload' => '2026-11-13',
            'nama_file' => 'https://drive.google.com/file/d/evidence-1/view',
            'jenis_dokumen' => 'Proposal',
            'status_validasi' => 'Menunggu Validasi',
        ]);

        $response->assertRedirect(route('evidence.index'));
        Http::assertSent(fn (HttpRequest $request): bool => $request->method() === 'POST'
            && str_ends_with($request->url(), '/api/evidence')
            && str_contains(implode(' ', $request->header('Content-Type')), 'application/json')
            && $request->data() === [
                'nama_file' => 'https://drive.google.com/file/d/evidence-1/view',
                'jenis_dokumen' => 'Proposal',
                'status_validasi' => 'Menunggu Validasi',
            ]);
        $response->assertSessionHas('toast.message', 'Tautan, jenis dokumen, dan status diteruskan ke API. Kontrak API saat ini tidak mencantumkan Nama File tampilan atau Tanggal Upload sebagai field yang dapat disimpan.');
    }

    public function test_search_toolbar_order_and_edit_modal_reflect_api_data_without_claiming_metadata_updates(): void
    {
        Http::fake([
            '*/api/evidence*' => Http::response([
                'success' => true,
                'data' => [
                    'current_page' => 1,
                    'data' => [[
                        'id' => 10,
                        'file_name' => 'Proposal_Kegiatan_2026.pdf',
                        'nama_file' => 'https://drive.google.com/file/d/evidence-10/view',
                        'jenis_dokumen' => 'Proposal',
                        'tanggal_upload' => '2026-11-13',
                        'status_validasi' => 'Perlu Ditinjau',
                    ]],
                    'total' => 1,
                    'per_page' => 10,
                    'last_page' => 1,
                ],
            ]),
        ]);

        $response = $this->authenticate()->get(route('evidence.index'));

        $response->assertOk()
            ->assertSeeInOrder(['name="search"', 'name="status_validasi"', 'name="jenis_dokumen"'])
            ->assertSee('Cari nama file...')
            ->assertSee('data-evidence-name="Proposal_Kegiatan_2026.pdf"', false)
            ->assertSee('data-evidence-type="Proposal"', false)
            ->assertSee('data-evidence-date="2026-11-13"', false)
            ->assertSee('data-evidence-status="Perlu Ditinjau"', false)
            ->assertSee('Perlu Ditinjau')
            ->assertSee('evidenceEditName', false)
            ->assertSee('evidenceEditType', false)
            ->assertSee('evidenceEditDate', false)
            ->assertSee('evidenceEditStatus', false)
            ->assertSee('API saat ini hanya mendukung penggantian tautan.');
    }

    public function test_search_uses_the_documented_api_filter_and_falls_back_to_name_matching_on_server_error(): void
    {
        Http::fake(function (HttpRequest $request) {
            parse_str((string) parse_url($request->url(), PHP_URL_QUERY), $query);

            if (isset($query['search'])) {
                return Http::response(['success' => false, 'message' => 'Search sementara gagal.'], 500);
            }

            $items = (int) ($query['page'] ?? 1) === 1
                ? [[
                    'id' => 10,
                    'file_name' => 'Proposal Kegiatan 2026.pdf',
                    'nama_file' => 'https://drive.google.com/file/d/proposal/view',
                    'jenis_dokumen' => 'Proposal',
                    'status_validasi' => 'Valid',
                ]]
                : [[
                    'id' => 11,
                    'file_name' => 'Laporan Tahunan.pdf',
                    'nama_file' => 'https://drive.google.com/file/d/report/view',
                    'jenis_dokumen' => 'Laporan',
                    'status_validasi' => 'Valid',
                ]];

            return Http::response([
                'success' => true,
                'data' => [
                    'current_page' => (int) ($query['page'] ?? 1),
                    'data' => $items,
                    'from' => 1,
                    'to' => count($items),
                    'total' => 2,
                    'per_page' => (int) ($query['per_page'] ?? 10),
                    'last_page' => 2,
                ],
            ]);
        });

        $response = $this->authenticate()->get(route('evidence.index', [
            'search' => 'Proposal',
            'status_validasi' => 'Valid',
        ]));

        $response->assertOk()
            ->assertSee('Proposal Kegiatan 2026.pdf')
            ->assertDontSee('Laporan Tahunan.pdf');

        Http::assertSent(fn (HttpRequest $request): bool => $request->method() === 'GET'
            && str_contains($request->url(), '/api/evidence?')
            && ($request['search'] ?? null) === 'Proposal');
        Http::assertSent(fn (HttpRequest $request): bool => $request->method() === 'GET'
            && ($request['per_page'] ?? null) === 100
            && ($request['page'] ?? null) === 2);
    }

    public function test_detail_displays_file_name_and_drive_link_as_separate_values(): void
    {
        Http::fake([
            '*/api/evidence/10' => Http::response([
                'success' => true,
                'data' => [
                    'id' => 10,
                    'file_name' => 'Proposal Kegiatan 2026.pdf',
                    'nama_file' => 'https://drive.google.com/file/d/evidence-10/view',
                    'jenis_dokumen' => 'Proposal',
                    'tanggal_upload' => '2026-11-13',
                    'status_validasi' => 'Valid',
                ],
            ]),
        ]);

        $response = $this->authenticate()->get(route('evidence.show', 10));

        $response->assertOk()
            ->assertSee('Proposal Kegiatan 2026.pdf')
            ->assertSee('Proposal')
            ->assertSee('13/11/2026')
            ->assertSee('https://drive.google.com/file/d/evidence-10/view')
            ->assertSee('https://drive.google.com/file/d/evidence-10/preview');
    }

    public function test_delete_and_link_change_call_the_documented_api_routes(): void
    {
        Http::fake([
            '*/api/evidence/10' => Http::response(['success' => true], 200),
            '*/api/evidence/10/ganti-link' => Http::response(['success' => true], 201),
        ]);

        $this->authenticate()->delete(route('evidence.destroy', 10))
            ->assertRedirect(route('evidence.index'));
        $this->authenticate()->post(route('evidence.update-link', 10), [
            'url_drive_baru' => 'https://drive.google.com/file/d/evidence-new/view',
            'alasan_perubahan' => 'Mengganti tautan dokumen.',
        ])->assertRedirect(route('evidence.index'));

        Http::assertSent(fn (HttpRequest $request): bool => $request->method() === 'DELETE'
            && str_ends_with($request->url(), '/api/evidence/10'));
        Http::assertSent(fn (HttpRequest $request): bool => $request->method() === 'POST'
            && str_ends_with($request->url(), '/api/evidence/10/ganti-link')
            && $request->data() === [
                'url_drive_baru' => 'https://drive.google.com/file/d/evidence-new/view',
                'alasan_perubahan' => 'Mengganti tautan dokumen.',
            ]);
    }

    public function test_api_create_error_is_shown_without_claiming_success(): void
    {
        Http::fake([
            '*/api/evidence' => Http::response([
                'success' => false,
                'message' => "SQLSTATE[42S22]: Unknown column 'kode_indikator'",
            ], 500),
        ]);

        $response = $this->authenticate()->from(route('evidence.index'))->post(route('evidence.store'), [
            'nama_file_display' => 'Proposal Kegiatan 2026',
            'tanggal_upload' => '2026-11-13',
            'nama_file' => 'https://drive.google.com/file/d/evidence-1/view',
            'jenis_dokumen' => 'Proposal',
            'status_validasi' => 'Ditolak',
        ]);

        $response->assertRedirect(route('evidence.index'))
            ->assertSessionHas('toast.message', 'Server API gagal menyimpan evidence: query database merujuk kolom "kode_indikator" yang tidak tersedia. Periksa mapping/query endpoint POST /api/evidence di backend; frontend tidak mengirim kolom tersebut.');
    }
}
