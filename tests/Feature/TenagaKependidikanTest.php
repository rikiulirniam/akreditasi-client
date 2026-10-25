<?php

namespace Tests\Feature;

use Illuminate\Http\Client\Request as ClientRequest;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TenagaKependidikanTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['services.tridharma.base_url' => 'https://tridharma.fayra.my.id/api']);
        $this->withSession(['access_token' => 'test-jwt-token']);
    }

    public function test_tendik_routes_do_not_use_the_local_fake_token_bypass(): void
    {
        $this->app->detectEnvironment(fn () => 'local');
        $this->flushSession();

        $response = $this->get(route('tenaga-kependidikan.index'));

        $response->assertRedirect(route('login'));
        Http::assertNothingSent();
    }

    public function test_index_uses_tendik_and_education_api_data(): void
    {
        $this->fakeTendikApi([
            $this->tendikRecord(41, 'Bambang Triyono, S.E.', '197805122003121001', 'Kepala Tata Usaha', 2),
        ]);

        $response = $this->get(route('tenaga-kependidikan.index'));

        $response->assertOk()
            ->assertSee('Bambang Triyono, S.E.')
            ->assertSee('S1 Manajemen')
            ->assertSee('Tambah Tendik');

        Http::assertSent(fn (ClientRequest $request) => $request->url() === 'https://tridharma.fayra.my.id/api/tendik?per_page=100'
            && $request->hasHeader('Authorization', 'Bearer test-jwt-token'));
        Http::assertSent(fn (ClientRequest $request) => $request->url() === 'https://tridharma.fayra.my.id/api/pendidikan'
            && $request->hasHeader('Authorization', 'Bearer test-jwt-token'));
    }

    public function test_index_deduplicates_education_options_by_label(): void
    {
        Http::fake([
            'https://tridharma.fayra.my.id/api/tendik*' => Http::response([
                'success' => true,
                'code' => 200,
                'data' => [],
            ], 200),
            'https://tridharma.fayra.my.id/api/pendidikan' => Http::response([
                'success' => true,
                'code' => 200,
                'data' => [
                    ['id' => 2, 'jenjang' => 'S2'],
                    ['id' => 9, 'jenjang' => 'S2'],
                    ['id' => 3, 'jenjang' => 'S3'],
                ],
            ], 200),
        ]);

        $response = $this->get(route('tenaga-kependidikan.index'));

        $response->assertOk()
            ->assertSee('value="2"', false)
            ->assertSee('value="3"', false);
        $response->assertDontSee('value="9"', false);
    }

    public function test_index_passes_search_to_api_and_applies_supported_jabatan_filter_to_returned_records(): void
    {
        $this->fakeTendikApi([
            $this->tendikRecord(41, 'Bambang Triyono, S.E.', '197805122003121001', 'Kepala Tata Usaha', 2),
            $this->tendikRecord(42, 'Bambang Nugraha, S.T.', '197805122003121002', 'Laboran', 3),
        ]);

        $response = $this->get(route('tenaga-kependidikan.index', [
            'cari' => 'Bambang',
            'jabatan' => 'Laboran',
        ]));

        $response->assertOk()
            ->assertSee('Bambang Nugraha, S.T.')
            ->assertDontSee('Bambang Triyono, S.E.');

        Http::assertSent(fn (ClientRequest $request) => $request->url() === 'https://tridharma.fayra.my.id/api/tendik?search=Bambang&per_page=100');
    }

    public function test_index_applies_kualifikasi_filter_to_returned_records(): void
    {
        $this->fakeTendikApi([
            $this->tendikRecord(41, 'Bambang Triyono, S.E.', '197805122003121001', 'Kepala Tata Usaha', 2),
            $this->tendikRecord(42, 'Bambang Nugraha, S.T.', '197805122003121002', 'Laboran', 3),
        ]);

        $response = $this->get(route('tenaga-kependidikan.index', [
            'kualifikasi' => 'S1 Teknik Informatika',
        ]));

        $response->assertOk()
            ->assertSee('Bambang Nugraha, S.T.')
            ->assertDontSee('Bambang Triyono, S.E.');
    }

    public function test_create_posts_api_field_names_and_only_reports_success_when_confirmed(): void
    {
        Http::fake([
            'https://tridharma.fayra.my.id/api/tendik' => Http::response([
                'success' => true,
                'code' => 201,
                'message' => 'Tendik berhasil ditambahkan.',
                'data' => $this->tendikRecord(50, 'Tendik Baru', '199901012024011099', 'Pranata Komputer', 2),
            ], 201),
        ]);

        $response = $this->post(route('tenaga-kependidikan.store'), [
            'nama' => 'Tendik Baru',
            'nip' => '199901012024011099',
            'jabatan' => 'Pranata Komputer',
            'id_pendidikan' => '2',
        ]);

        $response->assertRedirect(route('tenaga-kependidikan.index'));
        Http::assertSent(fn (ClientRequest $request) => $request->method() === 'POST'
            && $request->data() === [
                'id_pendidikan' => 2,
                'nama_tendik' => 'Tendik Baru',
                'jabatan_tendik' => 'Pranata Komputer',
                'nip' => '199901012024011099',
            ]);
    }

    public function test_api_validation_errors_are_returned_to_matching_form_fields(): void
    {
        Http::fake([
            'https://tridharma.fayra.my.id/api/tendik' => Http::response([
                'success' => false,
                'code' => 422,
                'message' => 'Validasi gagal.',
                'errors' => ['nama_tendik' => ['Nama sudah digunakan.']],
            ], 422),
        ]);

        $response = $this->from(route('tenaga-kependidikan.index'))
            ->post(route('tenaga-kependidikan.store'), [
                'nama' => 'Tendik Baru',
                'nip' => '199901012024011099',
                'jabatan' => 'Pranata Komputer',
                'id_pendidikan' => '2',
            ]);

        $response->assertRedirect(route('tenaga-kependidikan.index'))
            ->assertSessionHasErrors(['nama']);
    }

    public function test_server_error_does_not_report_a_successful_create(): void
    {
        Http::fake([
            'https://tridharma.fayra.my.id/api/tendik' => Http::response([
                'success' => false,
                'code' => 500,
                'message' => 'Internal Server Error',
            ], 500),
        ]);

        $response = $this->from(route('tenaga-kependidikan.index'))
            ->post(route('tenaga-kependidikan.store'), [
                'nama' => 'Tendik Baru',
                'nip' => '199901012024011099',
                'jabatan' => 'Pranata Komputer',
                'id_pendidikan' => '2',
            ]);

        $response->assertRedirect(route('tenaga-kependidikan.index'))
            ->assertSessionHas('toast.type', 'error')
            ->assertSessionMissing('success');
    }

    public function test_update_uses_put_and_documented_api_payload(): void
    {
        Http::fake([
            'https://tridharma.fayra.my.id/api/tendik/41' => Http::response([
                'success' => true,
                'code' => 200,
                'message' => 'Tendik berhasil diperbarui.',
                'data' => $this->tendikRecord(41, 'Bambang Baru', '197805122003121001', 'Kepala Tata Usaha', 2),
            ], 200),
        ]);

        $response = $this->put(route('tenaga-kependidikan.update', 41), [
            'nama' => 'Bambang Baru',
            'nip' => '197805122003121001',
            'jabatan' => 'Kepala Tata Usaha',
            'id_pendidikan' => '2',
        ]);

        $response->assertRedirect(route('tenaga-kependidikan.index'));
        Http::assertSent(fn (ClientRequest $request) => $request->method() === 'PUT'
            && $request->url() === 'https://tridharma.fayra.my.id/api/tendik/41'
            && $request->data()['id_pendidikan'] === 2
            && $request->data()['nama_tendik'] === 'Bambang Baru');
    }

    public function test_delete_calls_documented_api_endpoint(): void
    {
        Http::fake([
            'https://tridharma.fayra.my.id/api/tendik/41' => Http::response([
                'success' => true,
                'code' => 200,
                'message' => 'Tendik berhasil dihapus.',
                'data' => [],
            ], 200),
        ]);

        $response = $this->delete(route('tenaga-kependidikan.destroy', 41));

        $response->assertRedirect(route('tenaga-kependidikan.index'));
        Http::assertSent(fn (ClientRequest $request) => $request->method() === 'DELETE'
            && $request->url() === 'https://tridharma.fayra.my.id/api/tendik/41');
    }

    public function test_detail_modal_endpoint_returns_api_record_as_ui_fields(): void
    {
        Http::fake([
            'https://tridharma.fayra.my.id/api/tendik/41' => Http::response([
                'success' => true,
                'code' => 200,
                'message' => 'Detail Tendik.',
                'data' => $this->tendikRecord(41, 'Bambang Triyono, S.E.', '197805122003121001', 'Kepala Tata Usaha', 2),
            ], 200),
            'https://tridharma.fayra.my.id/api/pendidikan' => Http::response([
                'success' => true,
                'code' => 200,
                'data' => [
                    ['id' => 2, 'jenjang' => 'S1 Manajemen'],
                    ['id' => 3, 'jenjang' => 'S3'],
                ],
            ], 200),
        ]);

        $response = $this->getJson(route('tenaga-kependidikan.show', 41));

        $response->assertOk()
            ->assertJsonPath('data.nama', 'Bambang Triyono, S.E.')
            ->assertJsonPath('data.kualifikasi', 'S1 Manajemen');
    }

    public function test_detail_page_renders_api_identity_qualification_and_certifications(): void
    {
        Http::fake([
            'https://tridharma.fayra.my.id/api/tendik/41' => Http::response([
                'success' => true,
                'code' => 200,
                'data' => [
                    ...$this->tendikRecord(41, 'Ari Mursandi, S.Kom.', '198801012010011001', 'Laboran', 4),
                    'pendidikan' => ['id' => 4, 'jenjang' => 'D4/S1'],
                    'bidang_keahlian' => 'Teknik Elektro',
                    'email' => 'ari@example.test',
                    'status' => 'Aktif',
                ],
            ], 200),
            'https://tridharma.fayra.my.id/api/sertifikasi*' => Http::response([
                'success' => true,
                'code' => 200,
                'data' => [[
                    'id' => 81,
                    'id_tendik' => 41,
                    'jenis_sertifikasi' => 'Sertifikat Kompetensi',
                    'lembaga_penerbit' => 'BNSP',
                    'nomor_sertifikat' => 'CERT-81',
                    'tahun_sertifikasi' => 2025,
                ]],
            ], 200),
        ]);

        $response = $this->get(route('tenaga-pendidikan.show', 41));

        $response->assertOk()
            ->assertViewIs('tenaga-kependidikan.show')
            ->assertSee('Ari Mursandi, S.Kom.')
            ->assertSee('Teknik Elektro')
            ->assertSee('D4/S1')
            ->assertSee('Sertifikat Kompetensi')
            ->assertSee('BNSP');

        Http::assertSent(fn (ClientRequest $request) => str_starts_with($request->url(), 'https://tridharma.fayra.my.id/api/sertifikasi?')
            && $request['tipe_pemilik'] === 'tendik'
            && $request['id_tendik'] === 41);
    }

    public function test_add_certificate_posts_multipart_fields_to_api(): void
    {
        Http::fake([
            'https://tridharma.fayra.my.id/api/sertifikasi' => Http::response([
                'success' => true,
                'code' => 201,
                'message' => 'Sertifikasi berhasil ditambahkan.',
                'data' => ['id' => 81],
            ], 201),
        ]);

        $response = $this->post(route('tenaga-pendidikan.sertifikasi.store', 41), [
            'jenis_sertifikasi' => 'Sertifikat Kompetensi',
            'tahun_sertifikasi' => 2025,
            'bidang_studi' => 'Teknik Elektro',
            'nomor_sertifikat' => 'CERT-81',
            'lembaga_penerbit' => 'BNSP',
        ]);

        $response->assertRedirect(route('tenaga-pendidikan.show', 41));
        Http::assertSent(fn (ClientRequest $request) => $request->method() === 'POST'
            && $request->url() === 'https://tridharma.fayra.my.id/api/sertifikasi'
            && str_contains((string) $request->header('Content-Type')[0], 'multipart/form-data')
            && str_contains($request->body(), 'name="id_tendik"')
            && str_contains($request->body(), 'name="jenis_sertifikasi"'));
    }

    public function test_delete_certificate_calls_api(): void
    {
        Http::fake([
            'https://tridharma.fayra.my.id/api/sertifikasi/81' => Http::response([
                'success' => true,
                'code' => 200,
                'message' => 'Sertifikasi berhasil dihapus.',
                'data' => [],
            ], 200),
        ]);

        $response = $this->delete(route('tenaga-pendidikan.sertifikasi.destroy', [41, 81]));

        $response->assertRedirect(route('tenaga-pendidikan.show', 41));
        Http::assertSent(fn (ClientRequest $request) => $request->method() === 'DELETE'
            && $request->url() === 'https://tridharma.fayra.my.id/api/sertifikasi/81');
    }

    public function test_api_unauthorized_response_expires_local_session(): void
    {
        Http::fake([
            'https://tridharma.fayra.my.id/api/tendik?*' => Http::response([
                'success' => false,
                'code' => 401,
                'message' => 'Unauthenticated.',
            ], 401),
        ]);

        $response = $this->get(route('tenaga-kependidikan.index'));

        $response->assertRedirect(route('login'))
            ->assertSessionMissing('access_token');
    }

    public function test_index_shows_empty_state_matching_screenshot_when_no_data(): void
    {
        $this->fakeTendikApi([]);

        $response = $this->get(route('tenaga-kependidikan.index'));

        $response->assertOk()
            ->assertSee('Belum Ada Data Tendik')
            ->assertSee('Tekan tombol di bawah untuk menambah data')
            ->assertSee('Tambah Tendik')
            ->assertDontSee('0 Data ditemukan');
    }

    public function test_show_displays_qualification_institution_from_api_pendidikan(): void
    {
        Http::fake([
            'https://tridharma.fayra.my.id/api/tendik/3' => Http::response([
                'success' => true,
                'code' => 200,
                'data' => [
                    'id_tendik' => 3,
                    'nama_tendik' => 'Bambang Triyono, S.T',
                    'jabatan_tendik' => 'Laboran Komputer',
                    'nip' => '199103152014021002',
                    'id_pendidikan' => 1,
                    'pendidikan' => [
                        'id_pendidikan' => 1,
                        'jenjang' => 'S2',
                        'universitas' => 'Institut Teknologi Bandung',
                        'program_studi' => 'Teknik Elektro dan Informatika',
                        'tahun_lulus' => 2020,
                    ],
                ],
            ], 200),
            'https://tridharma.fayra.my.id/api/sertifikasi*' => Http::response([
                'success' => true,
                'code' => 200,
                'data' => [],
            ], 200),
        ]);

        $response = $this->get(route('tenaga-pendidikan.show', 3));

        $response->assertOk()
            ->assertSee('Bambang Triyono, S.T')
            ->assertSee('Institut Teknologi Bandung')
            ->assertSee('Teknik Elektro dan Informatika')
            ->assertSee('Belum ada sertifikasi');
    }

    /** @param array<int, array<string, mixed>> $records */
    private function fakeTendikApi(array $records): void
    {
        Http::fake([
            'https://tridharma.fayra.my.id/api/tendik*' => Http::response([
                'success' => true,
                'code' => 200,
                'message' => 'Data Tendik.',
                'data' => $records,
                'pagination' => [
                    'total' => count($records),
                    'per_page' => 100,
                    'current_page' => 1,
                    'last_page' => 1,
                ],
            ], 200),
            'https://tridharma.fayra.my.id/api/pendidikan' => Http::response([
                'success' => true,
                'code' => 200,
                'message' => 'Data pendidikan.',
                'data' => [
                    ['id' => 2, 'jenjang' => 'S1 Manajemen'],
                    ['id' => 3, 'jenjang' => 'S1 Teknik Informatika'],
                ],
            ], 200),
        ]);
    }

    /** @return array{id: int, id_pendidikan: int, nama_tendik: string, jabatan_tendik: string, nip: string} */
    private function tendikRecord(int $id, string $name, string $nip, string $position, int $educationId): array
    {
        return [
            'id' => $id,
            'id_pendidikan' => $educationId,
            'nama_tendik' => $name,
            'jabatan_tendik' => $position,
            'nip' => $nip,
        ];
    }
}
