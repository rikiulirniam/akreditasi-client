<?php

namespace Tests\Feature;

use Illuminate\Http\Client\Request as ClientRequest;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SertifikasiPageTest extends TestCase
{
    public function test_index_displays_api_certifications_and_sends_filters(): void
    {
        Http::fake([
            'https://tridharma.fayra.my.id/api/sertifikasi*' => Http::response([
                'success' => true,
                'data' => [
                    'current_page' => 2,
                    'data' => [[
                        'id' => 15,
                        'jenis_sertifikasi' => 'Sertifikasi Jaringan Komputer',
                        'nomor_sertifikat' => 'SK-2025-00125',
                        'status_sertifikasi' => 'Aktif',
                        'tahun_sertifikasi' => 2025,
                    ]],
                    'from' => 11,
                    'to' => 11,
                    'total' => 11,
                    'per_page' => 10,
                    'last_page' => 2,
                ],
            ]),
        ]);

        $response = $this->withSession(['access_token' => 'test-token'])
            ->get(route('sertifikasi.index', [
                'search' => 'jaringan',
                'tahun_sertifikasi' => 2025,
                'status_sertifikasi' => 'Aktif',
                'tipe_pemilik' => 'tendik',
                'id_tendik' => 7,
                'page' => 2,
            ]));

        $response->assertOk()
            ->assertSee('Sertifikasi Jaringan Komputer')
            ->assertSee('SK-2025-00125')
            ->assertSee('2025');

        Http::assertSent(fn (ClientRequest $request): bool => $request->method() === 'GET'
            && str_starts_with($request->url(), 'https://tridharma.fayra.my.id/api/sertifikasi?')
            && $request->hasHeader('Authorization', 'Bearer test-token')
            && $request['search'] === 'jaringan'
            && (int) $request['id_tendik'] === 7
            && (int) $request['page'] === 2);
    }

    public function test_empty_api_list_displays_the_empty_state_without_filters(): void
    {
        Http::fake([
            'https://tridharma.fayra.my.id/api/sertifikasi*' => Http::response([
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
            ->get(route('sertifikasi.index'));

        $response->assertOk()
            ->assertSee('Belum Ada Data Sertifikasi')
            ->assertSee('Tambah Sertifikasi')
            ->assertDontSee('Cari sertifikasi');
    }

    public function test_api_failures_are_displayed_instead_of_as_empty_data(): void
    {
        Http::fake([
            'https://tridharma.fayra.my.id/api/sertifikasi*' => Http::response([
                'success' => false,
                'message' => 'Server sertifikasi sedang tidak tersedia.',
            ], 503),
        ]);

        $response = $this->withSession(['access_token' => 'test-token'])
            ->get(route('sertifikasi.index'));

        $response->assertOk()
            ->assertSee('Server sertifikasi sedang tidak tersedia.')
            ->assertDontSee('Belum Ada Data Sertifikasi');
    }

    public function test_create_form_accepts_owner_context_from_link(): void
    {
        $response = $this->withSession(['access_token' => 'test-token'])
            ->get(route('sertifikasi.create', ['tipe_pemilik' => 'tendik', 'id_tendik' => 42]));

        $response->assertOk()
            ->assertSee('Tambah Sertifikasi')
            ->assertSee('name="owner_type" value="tendik"', false)
            ->assertSee('name="owner_id" value="42"', false);
    }

    public function test_show_displays_api_certification_details(): void
    {
        Http::fake([
            'https://tridharma.fayra.my.id/api/sertifikasi/18' => Http::response([
                'success' => true,
                'data' => [
                    'id' => 18,
                    'jenis_sertifikasi' => 'Sertifikasi Kompetensi',
                    'bidang_studi' => 'Teknik Informatika',
                    'nomor_registrasi_pendidik' => 'REG-2025-01',
                    'nomor_sk' => 'SK-2025-01',
                    'nomor_sertifikat' => 'CERT-2025-01',
                    'lembaga_penerbit' => 'BNSP',
                    'status_sertifikasi' => 'Aktif',
                    'tahun_sertifikasi' => 2025,
                    'tanggal_kedaluwarsa' => '2028-12-31',
                    'url_drive' => 'https://drive.google.com/file/d/test/view',
                ],
            ]),
        ]);

        $response = $this->withSession(['access_token' => 'test-token'])
            ->get(route('sertifikasi.show', 18));

        $response->assertOk()
            ->assertSee('Sertifikasi Kompetensi')
            ->assertSee('CERT-2025-01')
            ->assertSee('BNSP')
            ->assertSee('2028-12-31')
            ->assertSee('data-confirm-delete', false);
    }

    public function test_edit_form_loads_certification_from_api(): void
    {
        Http::fake([
            'https://tridharma.fayra.my.id/api/sertifikasi/18' => Http::response([
                'success' => true,
                'data' => [
                    'id' => 18,
                    'jenis_sertifikasi' => 'Sertifikasi Kompetensi',
                    'bidang_studi' => 'Teknik Informatika',
                    'lembaga_penerbit' => 'BNSP',
                    'status_sertifikasi' => 'Aktif',
                    'tahun_sertifikasi' => 2025,
                ],
            ]),
        ]);

        $response = $this->withSession(['access_token' => 'test-token'])
            ->get(route('sertifikasi.edit', 18));

        $response->assertOk()
            ->assertSee('Perbarui Sertifikasi')
            ->assertSee('value="Sertifikasi Kompetensi"', false)
            ->assertSee('value="2025"', false);
    }

    public function test_store_sends_certification_to_tridharma_api_without_local_database(): void
    {
        Http::fake([
            'https://tridharma.fayra.my.id/api/sertifikasi' => Http::response([
                'success' => true,
                'message' => 'Sertifikasi berhasil ditambahkan.',
                'data' => ['id' => 18],
            ], 201),
        ]);

        $response = $this->withSession(['access_token' => 'test-token'])
            ->post(route('sertifikasi.store'), [
                'owner_type' => 'tendik',
                'owner_id' => 42,
                'jenis_sertifikasi' => 'Sertifikasi Kompetensi',
                'bidang_studi' => 'Teknik Informatika',
                'nomor_registrasi_pendidik' => 'REG-2025-01',
                'nomor_sk' => 'SK-2025-01',
                'nomor_sertifikat' => 'CERT-2025-01',
                'lembaga_penerbit' => 'BNSP',
                'status_sertifikasi' => 'Aktif',
                'tahun_sertifikasi' => 2025,
                'tanggal_kedaluwarsa' => '2028-12-31',
                'url_drive' => 'https://drive.google.com/file/d/test/view',
            ]);

        $response->assertRedirect(route('sertifikasi.index', [
            'tipe_pemilik' => 'tendik',
            'id_tendik' => 42,
        ]));

        Http::assertSent(fn (ClientRequest $request): bool => $request->method() === 'POST'
            && $request->hasHeader('Authorization', 'Bearer test-token')
            && $request['id_tendik'] === 42
            && $request['id_dosen'] === null
            && $request['jenis_sertifikasi'] === 'Sertifikasi Kompetensi'
            && $request['tahun_sertifikasi'] === 2025);
    }

    public function test_update_sends_changes_to_tridharma_api(): void
    {
        Http::fake([
            'https://tridharma.fayra.my.id/api/sertifikasi/18' => Http::response([
                'success' => true,
                'message' => 'Sertifikasi berhasil diperbarui.',
            ]),
        ]);

        $response = $this->withSession(['access_token' => 'test-token'])
            ->put(route('sertifikasi.update', 18), [
                'jenis_sertifikasi' => 'Sertifikasi Diperbarui',
                'bidang_studi' => 'Teknik Informatika',
                'lembaga_penerbit' => 'BNSP',
                'status_sertifikasi' => 'Aktif',
                'tahun_sertifikasi' => 2025,
            ]);

        $response->assertRedirect(route('sertifikasi.show', 18));

        Http::assertSent(fn (ClientRequest $request): bool => $request->method() === 'PUT'
            && $request['jenis_sertifikasi'] === 'Sertifikasi Diperbarui'
            && $request->hasHeader('Authorization', 'Bearer test-token'));
    }

    public function test_destroy_deletes_certification_from_tridharma_api(): void
    {
        Http::fake([
            'https://tridharma.fayra.my.id/api/sertifikasi/18' => Http::response([
                'success' => true,
                'message' => 'Sertifikasi berhasil dihapus.',
            ]),
        ]);

        $response = $this->withSession(['access_token' => 'test-token'])
            ->delete(route('sertifikasi.destroy', 18), [
                'tipe_pemilik' => 'tendik',
                'id_tendik' => 42,
            ]);

        $response->assertRedirect(route('sertifikasi.index', [
            'tipe_pemilik' => 'tendik',
            'id_tendik' => 42,
        ]));

        Http::assertSent(fn (ClientRequest $request): bool => $request->method() === 'DELETE'
            && $request->hasHeader('Authorization', 'Bearer test-token'));
    }

    public function test_store_validates_fields_before_calling_api(): void
    {
        Http::fake();

        $response = $this->withSession(['access_token' => 'test-token'])
            ->from(route('sertifikasi.create'))
            ->post(route('sertifikasi.store'), []);

        $response->assertRedirect(route('sertifikasi.create'))
            ->assertSessionHasErrors([
                'owner_type',
                'owner_id',
                'jenis_sertifikasi',
                'bidang_studi',
                'lembaga_penerbit',
                'status_sertifikasi',
                'tahun_sertifikasi',
            ]);

        Http::assertNothingSent();
    }

    public function test_unauthorized_api_response_redirects_user_to_login(): void
    {
        Http::fake([
            'https://tridharma.fayra.my.id/api/sertifikasi*' => Http::response([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401),
        ]);

        $response = $this->withSession(['access_token' => 'expired-token'])
            ->get(route('sertifikasi.index'));

        $response->assertRedirect(route('login'))
            ->assertSessionHas('toast.message', 'Sesi Anda telah kedaluwarsa. Silakan login kembali.');
    }
}
