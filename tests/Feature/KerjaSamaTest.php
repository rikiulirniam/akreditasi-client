<?php

namespace Tests\Feature;

use Illuminate\Http\Client\Request as ClientRequest;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class KerjaSamaTest extends TestCase
{
    public function test_index_displays_cooperations_and_sends_documented_filters(): void
    {
        Http::fake([
            'https://tridharma.fayra.my.id/api/kerja-sama*' => Http::response([
                'success' => true,
                'data' => [
                    'current_page' => 1,
                    'data' => [[
                        'id_kerja_sama' => 5,
                        'judul_kerja_sama' => 'Kerja Sama Penelitian IoT',
                        'nama_mitra' => 'PT Teknologi Indonesia',
                        'jenis_dokumen' => 'MoU',
                        'tanggal_mulai' => '2025-01-01',
                        'tanggal_selesai' => '2027-12-31',
                        'status' => 'Aktif',
                    ]],
                    'from' => 1,
                    'to' => 1,
                    'total' => 1,
                    'per_page' => 10,
                    'last_page' => 1,
                ],
            ]),
        ]);

        $response = $this->withSession(['access_token' => 'test-token'])
            ->get(route('kerja-sama.index', [
                'search' => 'IoT',
                'status' => 'Aktif',
                'tingkat' => 'Nasional',
                'page' => 1,
            ]));

        $response->assertOk()
            ->assertSee('<span class="block break-words">Kerja Sama</span>', false)
            ->assertSee('<span class="block break-words">Penelitian IoT</span>', false)
            ->assertSee('PT Teknologi Indonesia')
            ->assertSee('MoU')
            ->assertSee('2025–2027')
            ->assertSee('Aktif')
            ->assertSee('id="kerja-sama-table"', false)
            ->assertSee('h-[34px] w-[248px]', false)
            ->assertSee('bg-[#ffc800]', false)
            ->assertSee('id="kerja-sama-status-menu"', false)
            ->assertSee('id="kerja-sama-level-menu"', false)
            ->assertSee('data-search-button', false)
            ->assertSee("searchButton.classList.toggle('bg-[#ffc800]', isSearching)", false);

        Http::assertSent(function (ClientRequest $request): bool {
            parse_str((string) parse_url($request->url(), PHP_URL_QUERY), $query);

            return $request->method() === 'GET'
                && str_starts_with($request->url(), 'https://tridharma.fayra.my.id/api/kerja-sama?')
                && ($query['search'] ?? null) === 'IoT'
                && ($query['status'] ?? null) === 'Aktif'
                && ($query['tingkat'] ?? null) === 'Nasional'
                && (int) ($query['page'] ?? 0) === 1;
        });
    }

    public function test_empty_api_list_displays_the_designed_empty_state(): void
    {
        Http::fake([
            'https://tridharma.fayra.my.id/api/kerja-sama*' => Http::response([
                'success' => true,
                'data' => [
                    'current_page' => 1,
                    'data' => [],
                    'last_page' => 1,
                    'per_page' => 10,
                    'total' => 0,
                ],
            ]),
        ]);

        $response = $this->withSession(['access_token' => 'test-token'])
            ->get(route('kerja-sama.index'));

        $response->assertOk()
            ->assertSee('Belum Ada Kerja Sama')
            ->assertSee('Tekan tombol di bawah untuk menambah data')
            ->assertSee('Tambah Kerja Sama')
            ->assertDontSee('id="kerja-sama-table"', false);
    }

    public function test_create_modal_loads_partner_lecturer_and_document_type_options(): void
    {
        Http::fake([
            'https://tridharma.fayra.my.id/api/kerja-sama*' => Http::response([
                'success' => true,
                'data' => ['data' => [], 'total' => 0, 'last_page' => 1, 'per_page' => 10],
            ]),
            'https://tridharma.fayra.my.id/api/mitra*' => Http::response([
                'success' => true,
                'data' => [
                    'data' => [['id_mitra' => 3, 'nama_instansi' => 'PT Mitra Nusantara']],
                    'last_page' => 1,
                ],
            ]),
            'https://tridharma.fayra.my.id/api/dosen*' => function (ClientRequest $request) {
                return Http::response([
                    'success' => true,
                    'data' => [
                        'data' => [['id_dosen' => (int) $request['page'] + 10, 'nama_dosen' => 'Dr. Siti Dosen']],
                        'last_page' => 1,
                    ],
                ]);
            },
            'https://tridharma.fayra.my.id/api/jenis-kerja-sama*' => Http::response([
                'success' => true,
                'data' => [
                    ['id_jenis_kerjasama' => 4, 'nama_jenis_kerjasama' => 'MoU'],
                ],
            ]),
        ]);

        $response = $this->withSession(['access_token' => 'test-token'])
            ->get(route('kerja-sama.index', ['create' => 1]));

        $response->assertOk()
            ->assertSee('Tambahkan Kerja Sama')
            ->assertSee('PT Mitra Nusantara')
            ->assertSee('Dr. Siti Dosen')
            ->assertSee('MoU')
            ->assertSee('Tanggal mulai')
            ->assertSee('Tanggal selesai')
            ->assertSee('Status Kerja Sama');
    }

    public function test_store_sends_the_documented_payload_to_the_api(): void
    {
        Http::fake([
            'https://tridharma.fayra.my.id/api/kerja-sama' => Http::response([
                'success' => true,
                'message' => 'Kerja sama berhasil ditambahkan.',
            ], 201),
        ]);

        $response = $this->withSession(['access_token' => 'test-token'])
            ->from(route('kerja-sama.index', ['create' => 1]))
            ->post(route('kerja-sama.store'), [
                'judul_kerja_sama' => 'Kerja Sama Penelitian IoT',
                'id_mitra' => 3,
                'id_jenis_kerjasama' => 4,
                'id_dosen' => 12,
                'jenis_dokumen' => 'MoU',
                'tingkat' => 'Nasional',
                'bentuk_kegiatan' => 'Penelitian bersama',
                'tanggal_mulai' => '2025-01-01',
                'tanggal_selesai' => '2027-12-31',
                'bukti_dokumen' => 'https://example.test/mou.pdf',
                'nomor_dokumen' => 'KS/001/2025',
                'status' => 'Aktif',
            ]);

        $response->assertRedirect(route('kerja-sama.index'));

        Http::assertSent(fn (ClientRequest $request): bool => $request->method() === 'POST'
            && str_starts_with($request->url(), 'https://tridharma.fayra.my.id/api/kerja-sama')
            && (int) $request['id_mitra'] === 3
            && (int) $request['id_jenis_kerjasama'] === 4
            && (int) $request['id_dosen'] === 12
            && $request['jenis_dokumen'] === 'MoU'
            && $request['bukti_dokumen'] === 'https://example.test/mou.pdf');
    }

    public function test_update_uses_the_fields_supported_by_the_documented_update_endpoint(): void
    {
        Http::fake([
            'https://tridharma.fayra.my.id/api/kerja-sama/5' => Http::response([
                'success' => true,
                'message' => 'Kerja sama berhasil diperbarui.',
            ]),
        ]);

        $response = $this->withSession(['access_token' => 'test-token'])
            ->from(route('kerja-sama.edit', 5))
            ->put(route('kerja-sama.update', 5), [
                'judul_kerja_sama' => 'Kerja Sama IoT Diperbarui',
                'id_mitra' => 3,
                'id_jenis_kerjasama' => 4,
                'id_dosen' => 12,
                'jenis_dokumen' => 'MoU',
                'tingkat' => 'Nasional',
                'bentuk_kegiatan' => 'Penelitian bersama',
                'tanggal_mulai' => '2025-01-01',
                'tanggal_selesai' => '2027-12-31',
                'bukti_dokumen' => 'https://example.test/mou.pdf',
                'nomor_dokumen' => 'KS/001/2025',
                'status' => 'Aktif',
            ]);

        $response->assertRedirect(route('kerja-sama.index'));

        Http::assertSent(fn (ClientRequest $request): bool => $request->method() === 'PUT'
            && $request->url() === 'https://tridharma.fayra.my.id/api/kerja-sama/5'
            && $request['judul_kerja_sama'] === 'Kerja Sama IoT Diperbarui'
            && ! array_key_exists('jenis_dokumen', $request->data())
            && ! array_key_exists('bukti_dokumen', $request->data()));
    }

    public function test_detail_displays_partnership_information_and_document_link(): void
    {
        Http::fake([
            'https://tridharma.fayra.my.id/api/kerja-sama/5' => Http::response([
                'success' => true,
                'data' => [
                    'id_kerja_sama' => 5,
                    'judul_kerja_sama' => 'Kerja Sama Penelitian IoT',
                    'mitra' => ['nama_instansi' => 'PT Teknologi Indonesia'],
                    'dosen' => ['nama_dosen' => 'Dr. Siti Dosen'],
                    'jenis_kerjasama' => ['nama_jenis_kerjasama' => 'MoU'],
                    'tingkat' => 'Nasional',
                    'tanggal_mulai' => '2025-01-01',
                    'tanggal_selesai' => '2027-12-31',
                    'nomor_dokumen' => 'KS/001/2025',
                    'bentuk_kegiatan' => 'Penelitian bersama',
                    'bukti_dokumen' => 'https://example.test/Mou_Kerja_Sama.pdf',
                    'status' => 'Aktif',
                ],
            ]),
        ]);

        $response = $this->withSession(['access_token' => 'test-token'])
            ->get(route('kerja-sama.show', 5));

        $response->assertOk()
            ->assertSee('Kerja Sama Penelitian IoT')
            ->assertSee('PT Teknologi Indonesia')
            ->assertSee('Dr. Siti Dosen')
            ->assertSee('MoU')
            ->assertSee('Nasional')
            ->assertSee('KS/001/2025')
            ->assertSee('Mou_Kerja_Sama.pdf')
            ->assertSee('href="https://example.test/Mou_Kerja_Sama.pdf"', false);
    }

    public function test_delete_calls_the_api_and_returns_to_the_list(): void
    {
        Http::fake([
            'https://tridharma.fayra.my.id/api/kerja-sama/5' => Http::response([
                'success' => true,
                'message' => 'Kerja sama berhasil dihapus.',
            ]),
        ]);

        $response = $this->withSession(['access_token' => 'test-token'])
            ->delete(route('kerja-sama.destroy', 5));

        $response->assertRedirect(route('kerja-sama.index'));

        Http::assertSent(fn (ClientRequest $request): bool => $request->method() === 'DELETE'
            && $request->url() === 'https://tridharma.fayra.my.id/api/kerja-sama/5');
    }

    public function test_unauthorized_api_response_redirects_to_login(): void
    {
        Http::fake([
            'https://tridharma.fayra.my.id/api/kerja-sama*' => Http::response([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401),
        ]);

        $response = $this->withSession(['access_token' => 'expired-token'])
            ->get(route('kerja-sama.index'));

        $response->assertRedirect(route('login'))
            ->assertSessionHas('toast.message', 'Sesi Anda telah kedaluwarsa. Silakan login kembali.');
    }
}
