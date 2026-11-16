<?php

namespace Tests\Feature;

use Illuminate\Http\Client\Request as ClientRequest;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class LuaranCrudPayloadTest extends TestCase
{
    public function test_create_sends_integer_master_ids_and_backend_field_names(): void
    {
        config(['services.tridharma.base_url' => 'https://tridharma.fayra.my.id/api']);

        Http::fake([
            'https://tridharma.fayra.my.id/api/jenis-luaran' => Http::response([
                'success' => true,
                'data' => [
                    ['id_jenis_luaran' => 12, 'nama_jenis_luaran' => 'Jurnal', 'indeks' => 'Scopus'],
                    ['id_jenis_luaran' => 13, 'nama_jenis_luaran' => 'Jurnal', 'indeks' => 'Sinta'],
                ],
            ]),
            'https://tridharma.fayra.my.id/api/luaran' => Http::response([
                'success' => true,
                'message' => 'Data luaran berhasil ditambahkan.',
                'data' => ['id_luaran' => 1],
            ], 201),
        ]);

        $response = $this->withSession([
            'access_token' => 'test-jwt-token',
            'user' => ['role' => 'kaprodi'],
        ])->postJson(route('luaran.store'), [
            'jenis_luaran' => 'Jurnal',
            'id_dosen' => '23',
            'nama_jurnal' => 'Jurnal Uji',
            'issn' => '1234-5678',
            'tanggal_terbit' => '2025-07-14',
            'halaman_jurnal' => '8',
            'indeksasi' => 'SCOPUS',
            'tautan_jurnal' => 'https://drive.google.com/file/d/jurnal',
            'id_kegiatan' => '41',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true);

        Http::assertSent(fn (ClientRequest $request): bool => $request->method() === 'POST'
            && parse_url($request->url(), PHP_URL_PATH) === '/api/luaran'
            && $request->data()['id_dosen'] === 23
            && $request->data()['id_jenis_luaran'] === 12
            && $request->data()['id_kegiatan'] === 41
            && $request->data()['halaman_jurnal'] === 8
            && $request->data()['url_luaran'] === 'https://drive.google.com/file/d/jurnal'
            && $request->data()['tautan_jurnal'] === 'https://drive.google.com/file/d/jurnal'
            && ! array_key_exists('jenis_luaran', $request->data())
            && ! array_key_exists('url_publikasi', $request->data()));
    }

    public function test_create_requires_a_dosen_foreign_key_before_calling_the_api(): void
    {
        config(['services.tridharma.base_url' => 'https://tridharma.fayra.my.id/api']);
        Http::fake();

        $response = $this->withSession([
            'access_token' => 'test-jwt-token',
            'user' => ['role' => 'kaprodi'],
        ])->postJson(route('luaran.store'), [
            'jenis_luaran' => 'Jurnal',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('id_dosen');

        Http::assertNothingSent();
    }

    public function test_luaran_form_renders_typed_fields_and_master_owner_selector(): void
    {
        config(['services.tridharma.base_url' => 'https://tridharma.fayra.my.id/api']);
        Http::fake([
            '*' => Http::response(['success' => true, 'data' => []]),
        ]);

        $response = $this->withSession([
            'access_token' => 'test-jwt-token',
            'user' => ['role' => 'kaprodi'],
        ])->get(route('luaran.index'));

        $response->assertOk()
            ->assertSee('name="id_dosen"', false)
            ->assertSee('type="date"', false)
            ->assertSee('type="number"', false)
            ->assertSee('name="jenis_buku"', false)
            ->assertSee('name="indeksasi"', false)
            ->assertSee('name="tanggal_pendaftaran_hki"', false)
            ->assertSee('name="tanggal_terbit_sertifikat_hki"', false)
            ->assertSee('name="status_pengembangan"', false)
            ->assertSee('name="tingkat_kesiapanterapan"', false)
            ->assertSee('name="tanggal_pembuatan"', false)
            ->assertSee('value="TKT 9"', false)
            ->assertSee('value="Software/Web"', false)
            ->assertSee('name="url_luaran"', false)
            ->assertSee('name="tautan_seminar"', false);
    }

    public function test_index_renders_luaran_with_related_author_and_generic_document_url(): void
    {
        config(['services.tridharma.base_url' => 'https://tridharma.fayra.my.id/api']);
        Http::fake([
            '*api/luaran*' => Http::response([
                'success' => true,
                'data' => [[
                    'id_luaran' => 28,
                    'judul_luaran' => 'Luaran Terbit',
                    'jenis_luaran' => ['nama_jenis_luaran' => 'Jurnal'],
                    'dosen' => ['id_dosen' => 23, 'nama_dosen' => 'Dosen Terhubung'],
                    'nama_jurnal' => 'Jurnal Teknologi',
                    'tanggal_terbit' => '2025-07-14',
                    'url_luaran' => 'https://example.test/luaran',
                ]],
            ]),
            '*api/dosen*' => Http::response([
                'success' => true,
                'data' => [['id_dosen' => 23, 'nama_dosen' => 'Dosen Terhubung']],
            ]),
        ]);

        $response = $this->withSession([
            'access_token' => 'test-jwt-token',
            'user' => ['role' => 'kaprodi'],
        ])->get(route('luaran.index'));

        $response->assertOk()
            ->assertSee('Luaran Terbit')
            ->assertSee('Jurnal')
            ->assertSee('Dosen Terhubung')
            ->assertSee('Jurnal Teknologi')
            ->assertSee('https://example.test/luaran', false);
    }
}
