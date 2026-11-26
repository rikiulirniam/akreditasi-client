<?php

namespace Tests\Feature;

use App\Services\TridharmaService;
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

    private function authenticate(?string $role = 'KAPRODI'): static
    {
        return $this->withSession([
            'access_token' => 'test-token',
            'user' => [
                'id' => 1,
                'nama' => 'Test User',
                'role' => $role,
            ],
        ]);
    }

    public function test_evidence_uses_its_scoped_api_url_without_changing_other_tridharma_requests(): void
    {
        config([
            'services.tridharma.base_url' => 'https://tridharma.fayra.my.id/api',
            'services.evidence.base_url' => 'http://localhost:8000/api',
        ]);

        Http::fake([
            '*' => Http::response(['success' => true, 'data' => []]),
        ]);

        $this->authenticate()->get(route('evidence.index'))->assertOk();

        $tridharmaService = app(TridharmaService::class);
        $tridharmaService->getEvidenceById(10);
        $tridharmaService->createEvidence([
            'nama_file' => 'Proposal Penelitian 2026',
            'url_drive' => 'https://drive.google.com/file/d/evidence-10/view',
            'jenis_dokumen' => 'Proposal',
            'tanggal_upload' => '2026-11-20',
        ]);
        $tridharmaService->updateEvidence(10, [
            'nama_file' => 'Proposal Penelitian 2026 Revisi',
            'jenis_dokumen' => 'Proposal',
        ]);
        $tridharmaService->deleteEvidence(10);
        $tridharmaService->changeEvidenceLink(10, [
            'url_drive_baru' => 'https://onedrive.live.com/edit.aspx?cid=newlink',
            'alasan_perubahan' => 'Pindah ke OneDrive.',
        ]);
        $tridharmaService->validateEvidence(10, [
            'status_validasi' => 'Valid',
            'catatan_validasi' => 'Dokumen sesuai.',
        ]);
        $tridharmaService->getDosenList();

        Http::assertSent(fn (HttpRequest $request): bool => $request->method() === 'GET'
            && str_starts_with($request->url(), 'http://localhost:8000/api/evidence'));
        Http::assertSent(fn (HttpRequest $request): bool => $request->method() === 'GET'
            && str_ends_with($request->url(), '/api/evidence/10')
            && str_starts_with($request->url(), 'http://localhost:8000/'));
        Http::assertSent(fn (HttpRequest $request): bool => $request->method() === 'POST'
            && str_ends_with($request->url(), '/api/evidence')
            && str_starts_with($request->url(), 'http://localhost:8000/'));
        Http::assertSent(fn (HttpRequest $request): bool => $request->method() === 'PUT'
            && str_ends_with($request->url(), '/api/evidence/10')
            && str_starts_with($request->url(), 'http://localhost:8000/'));
        Http::assertSent(fn (HttpRequest $request): bool => $request->method() === 'DELETE'
            && str_ends_with($request->url(), '/api/evidence/10')
            && str_starts_with($request->url(), 'http://localhost:8000/'));
        Http::assertSent(fn (HttpRequest $request): bool => $request->method() === 'POST'
            && str_ends_with($request->url(), '/api/evidence/10/ganti-link')
            && str_starts_with($request->url(), 'http://localhost:8000/'));
        Http::assertSent(fn (HttpRequest $request): bool => $request->method() === 'POST'
            && str_ends_with($request->url(), '/api/evidence/10/validasi')
            && str_starts_with($request->url(), 'http://localhost:8000/'));
        Http::assertSent(fn (HttpRequest $request): bool => $request->method() === 'GET'
            && str_starts_with($request->url(), 'https://tridharma.fayra.my.id/api/dosen'));
    }

    public function test_create_sends_fields_supported_by_the_latest_api(): void
    {
        Http::fake([
            '*/api/evidence' => Http::response([
                'success' => true,
                'message' => 'Evidence berhasil disimpan dan menunggu validasi.',
                'data' => [
                    'id_evidence' => 10,
                    'nama_file' => 'Proposal Kegiatan 2026',
                    'jenis_dokumen' => 'Proposal',
                    'tanggal_upload' => '2026-11-13',
                    'url_drive' => 'https://example.com/dokumen/proposal.pdf',
                    'status_validasi' => 'Menunggu Validasi',
                ],
            ], 201),
        ]);

        $response = $this->authenticate()->from(route('evidence.index'))->post(route('evidence.store'), [
            'nama_file' => 'Proposal Kegiatan 2026',
            'jenis_dokumen' => 'Proposal',
            'tanggal_upload' => '2026-11-13',
            'url_drive' => 'https://example.com/dokumen/proposal.pdf',
        ]);

        $response->assertRedirect(route('evidence.index'));
        Http::assertSent(fn (HttpRequest $request): bool => $request->method() === 'POST'
            && str_ends_with($request->url(), '/api/evidence')
            && str_contains(implode(' ', $request->header('Content-Type')), 'application/json')
            && $request->data() === [
                'nama_file' => 'Proposal Kegiatan 2026',
                'url_drive' => 'https://example.com/dokumen/proposal.pdf',
                'jenis_dokumen' => 'Proposal',
                'tanggal_upload' => '2026-11-13',
            ]);
        $response->assertSessionHas('toast.title', 'Evidence Berhasil Ditambahkan');
    }

    public function test_search_toolbar_and_table_render_cleanly(): void
    {
        Http::fake([
            '*/api/evidence*' => Http::response([
                'success' => true,
                'data' => [
                    'current_page' => 1,
                    'data' => [[
                        'id_evidence' => 10,
                        'nama_file' => 'Proposal Penelitian 2026',
                        'url_drive' => 'https://example.com/proposal.pdf',
                        'jenis_dokumen' => 'Proposal',
                        'tanggal_upload' => '2026-11-13',
                        'status_validasi' => 'Menunggu Validasi',
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
            ->assertSee('Cari nama file atau jenis dokumen...')
            ->assertSee('Proposal Penelitian 2026')
            ->assertSee('Proposal')
            ->assertSee('13/11/2026')
            ->assertSee('Menunggu Validasi');
    }

    public function test_detail_displays_metadata_and_universal_link(): void
    {
        Http::fake([
            '*/api/evidence/10' => Http::response([
                'success' => true,
                'data' => [
                    'id_evidence' => 10,
                    'nama_file' => 'Laporan Kerjasama PT Maju 2026',
                    'url_drive' => 'https://dropbox.com/s/12345/laporan.pdf',
                    'jenis_dokumen' => 'Laporan',
                    'tanggal_upload' => '2026-11-13',
                    'status_validasi' => 'Valid',
                    'catatan_validasi' => 'Dokumen sah dan terverifikasi.',
                    'history' => [],
                ],
            ]),
        ]);

        $response = $this->authenticate()->get(route('evidence.show', 10));

        $response->assertOk()
            ->assertSee('Laporan Kerjasama PT Maju 2026')
            ->assertSee('Laporan')
            ->assertSee('13/11/2026')
            ->assertSee('https://dropbox.com/s/12345/laporan.pdf')
            ->assertSee('Dokumen sah dan terverifikasi.')
            ->assertDontSee('Edit Evidence')
            ->assertDontSee('Pilih Keputusan Validasi')
            ->assertDontSee('Simpan Hasil Validasi');
    }

    public function test_delete_and_link_change_call_api(): void
    {
        Http::fake([
            '*/api/evidence/10' => Http::response(['success' => true, 'message' => 'Evidence dihapus.'], 200),
            '*/api/evidence/10/ganti-link' => Http::response(['success' => true, 'message' => 'Tautan diganti.'], 200),
        ]);

        $this->authenticate()->delete(route('evidence.destroy', 10))
            ->assertRedirect(route('evidence.index'));

        $this->authenticate()->post(route('evidence.update-link', 10), [
            'url_drive_baru' => 'https://github.com/org/repo/blob/main/doc.pdf',
            'alasan_perubahan' => 'Pindah repositori ke GitHub.',
        ])->assertRedirect(route('evidence.show', 10));

        Http::assertSent(fn (HttpRequest $request): bool => $request->method() === 'DELETE'
            && str_ends_with($request->url(), '/api/evidence/10'));
        Http::assertSent(fn (HttpRequest $request): bool => $request->method() === 'POST'
            && str_ends_with($request->url(), '/api/evidence/10/ganti-link')
            && $request->data() === [
                'url_drive_baru' => 'https://github.com/org/repo/blob/main/doc.pdf',
                'alasan_perubahan' => 'Pindah repositori ke GitHub.',
            ]);
    }

    public function test_update_metadata_calls_api(): void
    {
        Http::fake([
            '*/api/evidence/10' => Http::response(['success' => true, 'message' => 'Metadata diperbarui.'], 200),
        ]);

        $response = $this->authenticate()->put(route('evidence.update', 10), [
            'nama_file' => 'Proposal Revisi 2026',
            'jenis_dokumen' => 'Proposal',
            'tanggal_upload' => '2026-11-25',
        ]);

        $response->assertRedirect(route('evidence.index'));
        $response->assertSessionHas('toast.type', 'update');
        $response->assertSessionHas('toast.title', 'Evidence Berhasil Diperbarui');

        Http::assertSent(fn (HttpRequest $request): bool => $request->method() === 'PUT'
            && str_ends_with($request->url(), '/api/evidence/10')
            && $request->data() === [
                'nama_file' => 'Proposal Revisi 2026',
                'jenis_dokumen' => 'Proposal',
                'tanggal_upload' => '2026-11-25',
            ]);
    }

    public function test_validasi_action_by_authorized_role(): void
    {
        Http::fake([
            '*/api/evidence/10/validasi' => Http::response(['success' => true, 'message' => 'Validasi berhasil.'], 200),
        ]);

        $this->authenticate('KAPRODI')->post(route('evidence.validasi', 10), [
            'status_validasi' => 'Valid',
        ])->assertRedirect();

        Http::assertSent(fn (HttpRequest $request): bool => $request->method() === 'POST'
            && str_ends_with($request->url(), '/api/evidence/10/validasi')
            && $request->data() === [
                'status_validasi' => 'Valid',
                'catatan_validasi' => null,
            ]);
    }
}
