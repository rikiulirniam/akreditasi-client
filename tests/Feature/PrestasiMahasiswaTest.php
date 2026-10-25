<?php

namespace Tests\Feature;

use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PrestasiMahasiswaTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    private function fakeToken(): string
    {
        $header = base64_encode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
        $payload = base64_encode(json_encode([
            'sub' => 1,
            'exp' => time() + 3600,
            'user' => [
                'nama' => 'Admin',
                'role' => 'ADMINISTRATOR',
                'email' => 'admin@example.com',
            ],
        ]));

        return "{$header}.{$payload}.".base64_encode('signature');
    }

    private function authenticate(): static
    {
        return $this->withSession([
            'access_token' => $this->fakeToken(),
            'user' => ['nama' => 'Admin', 'role' => 'ADMINISTRATOR'],
        ]);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('prestasi-mahasiswa.index'))->assertRedirect(route('login'));
    }

    public function test_user_can_view_achievement_list_with_student_names(): void
    {
        Http::fake([
            '*/api/prestasi-mahasiswa*' => Http::response([
                'success' => true,
                'data' => [
                    ['id' => 7, 'id_mahasiswa' => 1, 'nama_aktivitas' => 'Juara 1 OSN', 'tahun' => 2024],
                ],
            ]),
            '*/api/mahasiswa*' => Http::response([
                'success' => true,
                'data' => [
                    ['id' => 1, 'nama_mahasiswa' => 'Ayu Lestari', 'nim' => '230101'],
                ],
            ]),
            '*' => Http::response(['success' => true, 'data' => []]),
        ]);

        $response = $this->authenticate()->get(route('prestasi-mahasiswa.index'));

        $response->assertOk();
        $response->assertSee('Prestasi Mahasiswa');
        $response->assertSee('Ayu Lestari');
        $response->assertSee('230101');
        $response->assertSee('Juara 1 OSN');
        $response->assertSee('2024');
        $response->assertSee('Jenis Prestasi');
        $response->assertSee('Bukti Prestasi');
        $response->assertSee('Program Studi');
        $response->assertSee('Pilih Kelas');
        $response->assertSee('Pilih Angkatan');
        $response->assertSee('Nama Kegiatan');
        $response->assertSee('Sertifikat');
        $response->assertSee('Update Prestasi Mahasiswa');
    }

    public function test_user_can_create_an_achievement_through_akademik_service(): void
    {
        Storage::fake('public');
        Http::fake([
            '*/api/prestasi-mahasiswa' => Http::response([
                'success' => true,
                'message' => 'Data prestasi berhasil dibuat.',
                'data' => ['id' => 7],
            ], 201),
            '*/api/angkatan-kelas*' => Http::response([
                'success' => true,
                'data' => [['id' => 3, 'prodi_id' => 4, 'kelas_id' => 5, 'angkatan_id' => 6]],
            ]),
            '*/api/enrollment-kelas*' => Http::response([
                'success' => true,
                'data' => [['angkatan_kelas_id' => 3, 'id_mahasiswa' => 1]],
            ]),
            '*/api/jenis-prestasi' => Http::response(['success' => true, 'data' => ['id' => 11]], 201),
            '*/api/evidence-prestasi-mahasiswa' => Http::response(['success' => true, 'data' => ['id' => 12]], 201),
            '*' => Http::response(['success' => true, 'data' => []]),
        ]);

        $response = $this->authenticate()->post(route('prestasi-mahasiswa.store'), [
            'id_mahasiswa' => 1,
            'angkatan_kelas_id' => 3,
            'nama_aktivitas' => ' Juara 1 OSN ',
            'jenis' => 'Akademik',
            'tahun' => 2024,
            'certificate' => UploadedFile::fake()->create('sertifikat.pdf', 100, 'application/pdf'),
        ]);

        $response->assertRedirect(route('prestasi-mahasiswa.index'));
        $response->assertSessionHas('toast');
        $this->assertSame('success', session('toast.type'));
        Http::assertSent(fn (HttpRequest $request) => $request->method() === 'POST'
            && str_ends_with($request->url(), '/api/prestasi-mahasiswa')
            && $request->data() === [
                'id_mahasiswa' => 1,
                'nama_aktivitas' => 'Juara 1 OSN',
                'tahun' => 2024,
            ]);
        Http::assertSent(fn (HttpRequest $request) => $request->method() === 'POST'
            && str_ends_with($request->url(), '/api/jenis-prestasi')
            && $request->data() === ['id_prestasi_mahasiswa' => 7, 'jenis' => 'Akademik']);
        Http::assertSent(fn (HttpRequest $request) => $request->method() === 'POST'
            && str_ends_with($request->url(), '/api/evidence-prestasi-mahasiswa')
            && $request->data()['id_prestasi_mahasiswa'] === 7
            && str_contains($request->data()['evidence'], '/storage/prestasi-mahasiswa/'));
        $this->assertCount(1, Storage::disk('public')->allFiles('prestasi-mahasiswa'));
    }

    public function test_user_can_update_and_delete_an_achievement(): void
    {
        Http::fake([
            '*/api/prestasi-mahasiswa/7' => Http::response([
                'success' => true,
                'message' => 'Data prestasi berhasil diperbarui.',
            ]),
            '*/api/jenis-prestasi*' => Http::response(['success' => true, 'data' => []]),
            '*' => Http::response(['success' => true, 'data' => []]),
        ]);

        $updateResponse = $this->authenticate()->put(route('prestasi-mahasiswa.update', 7), [
            'id_mahasiswa' => 1,
            'nama_aktivitas' => 'Juara 2 OSN',
            'jenis' => 'Akademik',
            'tahun' => 2025,
        ]);
        $updateResponse->assertRedirect(route('prestasi-mahasiswa.index'));

        $deleteResponse = $this->authenticate()->delete(route('prestasi-mahasiswa.destroy', 7));
        $deleteResponse->assertRedirect(route('prestasi-mahasiswa.index'));

        Http::assertSent(fn (HttpRequest $request) => $request->method() === 'PUT'
            && str_ends_with($request->url(), '/api/prestasi-mahasiswa/7')
            && $request->data() === [
                'id_mahasiswa' => 1,
                'nama_aktivitas' => 'Juara 2 OSN',
                'tahun' => 2025,
            ]);
        Http::assertSent(fn (HttpRequest $request) => $request->method() === 'DELETE'
            && str_ends_with($request->url(), '/api/prestasi-mahasiswa/7'));
    }

    public function test_user_can_update_achievement_certificate_using_a_link(): void
    {
        Http::fake([
            '*/api/prestasi-mahasiswa/7' => Http::response(['success' => true]),
            '*/api/jenis-prestasi/15' => Http::response(['success' => true]),
            '*/api/evidence-prestasi-mahasiswa/12' => Http::response(['success' => true]),
            '*/api/jenis-prestasi*' => Http::response([
                'success' => true,
                'data' => [['id' => 15, 'id_prestasi_mahasiswa' => 7, 'jenis' => 'Akademik']],
            ]),
            '*/api/evidence-prestasi-mahasiswa*' => Http::response([
                'success' => true,
                'data' => [['id' => 12, 'id_prestasi_mahasiswa' => 7, 'evidence' => 'https://example.com/old-certificate']],
            ]),
            '*' => Http::response(['success' => true, 'data' => []]),
        ]);

        $response = $this->authenticate()->put(route('prestasi-mahasiswa.update', 7), [
            'id_mahasiswa' => 1,
            'nama_aktivitas' => 'Juara 1 OSN',
            'jenis' => 'Akademik',
            'tahun' => 2024,
            'evidence' => 'https://example.com/new-certificate',
        ]);

        $response->assertRedirect(route('prestasi-mahasiswa.index'));
        $this->assertSame('success', session('toast.type'));
        Http::assertSent(fn (HttpRequest $request) => $request->method() === 'PUT'
            && str_ends_with($request->url(), '/api/evidence-prestasi-mahasiswa/12')
            && $request->data() === [
                'id_prestasi_mahasiswa' => 7,
                'evidence' => 'https://example.com/new-certificate',
            ]);
    }

    public function test_user_cannot_update_achievement_with_an_invalid_certificate_link(): void
    {
        $response = $this->authenticate()->put(route('prestasi-mahasiswa.update', 7), [
            'id_mahasiswa' => 1,
            'nama_aktivitas' => 'Juara 1 OSN',
            'jenis' => 'Akademik',
            'tahun' => 2024,
            'evidence' => 'not-a-url',
        ]);

        $response->assertSessionHasErrors(['evidence']);
    }

    public function test_user_can_view_achievement_with_evidence_and_type_records(): void
    {
        Http::fake([
            '*/api/prestasi-mahasiswa/7' => Http::response([
                'success' => true,
                'data' => [
                    'id' => 7,
                    'id_mahasiswa' => 1,
                    'nama_aktivitas' => 'Juara 1 OSN',
                    'tahun' => 2024,
                ],
            ]),
            '*/api/mahasiswa/1' => Http::response([
                'success' => true,
                'data' => ['id' => 1, 'nama_mahasiswa' => 'Ayu Lestari', 'nim' => '230101'],
            ]),
            '*/api/mahasiswa?per_page=500' => Http::response([
                'success' => true,
                'data' => [
                    ['id' => 1, 'nama_mahasiswa' => 'Ayu Lestari', 'nim' => '230101'],
                ],
            ]),
            '*/api/evidence-prestasi-mahasiswa*' => Http::response([
                'success' => true,
                'data' => [
                    ['id' => 12, 'id_prestasi_mahasiswa' => 7, 'evidence' => 'https://example.com/sertifikat'],
                    ['id' => 13, 'id_prestasi_mahasiswa' => 8, 'evidence' => 'other.pdf'],
                ],
            ]),
            '*/api/jenis-prestasi*' => Http::response([
                'success' => true,
                'data' => [
                    ['id' => 15, 'id_prestasi_mahasiswa' => 7, 'jenis' => 'Akademik'],
                    ['id' => 16, 'id_prestasi_mahasiswa' => 8, 'jenis' => 'Olahraga'],
                ],
            ]),
        ]);

        $response = $this->authenticate()->get(route('prestasi-mahasiswa.show', 7));

        $response->assertOk();
        $response->assertSee('Juara 1 OSN');
        $response->assertSee('Ayu Lestari');
        $response->assertSee('230101');
        $response->assertSee('https://example.com/sertifikat');
        $response->assertSee('Akademik');
        $response->assertDontSee('other.pdf');
        $response->assertDontSee('Olahraga');
    }

    public function test_user_can_add_related_evidence_and_type_records(): void
    {
        Http::fake([
            '*/api/evidence-prestasi-mahasiswa' => Http::response(['success' => true], 201),
            '*/api/jenis-prestasi' => Http::response(['success' => true], 201),
        ]);

        $evidenceResponse = $this->authenticate()->post(route('prestasi-mahasiswa.evidence.store', 7), [
            'evidence' => 'sertifikat.pdf',
        ]);
        $evidenceResponse->assertRedirect(route('prestasi-mahasiswa.show', 7));

        $jenisResponse = $this->authenticate()->post(route('prestasi-mahasiswa.jenis.store', 7), [
            'jenis' => 'Akademik',
        ]);
        $jenisResponse->assertRedirect(route('prestasi-mahasiswa.show', 7));

        Http::assertSent(fn (HttpRequest $request) => $request->method() === 'POST'
            && str_ends_with($request->url(), '/api/evidence-prestasi-mahasiswa')
            && $request->data() === ['id_prestasi_mahasiswa' => 7, 'evidence' => 'sertifikat.pdf']);
        Http::assertSent(fn (HttpRequest $request) => $request->method() === 'POST'
            && str_ends_with($request->url(), '/api/jenis-prestasi')
            && $request->data() === ['id_prestasi_mahasiswa' => 7, 'jenis' => 'Akademik']);
    }

    public function test_related_record_cannot_be_changed_from_another_achievement(): void
    {
        Http::fake([
            '*/api/evidence-prestasi-mahasiswa/12' => Http::response([
                'success' => true,
                'data' => ['id' => 12, 'id_prestasi_mahasiswa' => 8, 'evidence' => 'other.pdf'],
            ]),
        ]);

        $response = $this->authenticate()->put(route('prestasi-mahasiswa.evidence.update', [7, 12]), [
            'evidence' => 'changed.pdf',
        ]);

        $response->assertSessionHas('toast');
        $this->assertSame('error', session('toast.type'));
        Http::assertNotSent(fn (HttpRequest $request) => $request->method() === 'PUT');
    }

    public function test_user_cannot_create_achievement_with_invalid_data(): void
    {
        $response = $this->authenticate()->post(route('prestasi-mahasiswa.store'), [
            'id_mahasiswa' => '',
            'nama_aktivitas' => '',
            'tahun' => 1800,
        ]);

        $response->assertSessionHasErrors(['id_mahasiswa', 'angkatan_kelas_id', 'nama_aktivitas', 'jenis', 'tahun', 'certificate']);
    }

    public function test_list_is_paginated_to_nine_rows_and_searches_related_fields(): void
    {
        $achievements = [];
        for ($index = 1; $index <= 10; $index++) {
            $achievements[] = [
                'id' => $index,
                'id_mahasiswa' => 1,
                'nama_aktivitas' => $index === 10 ? 'Juara Nasional' : "Kegiatan {$index}",
                'tahun' => 2024,
            ];
        }

        Http::fake([
            '*/api/prestasi-mahasiswa*' => Http::response(['success' => true, 'data' => $achievements]),
            '*/api/mahasiswa*' => Http::response([
                'success' => true,
                'data' => [['id' => 1, 'nama_mahasiswa' => 'Ayu Lestari', 'nim' => '230101']],
            ]),
            '*' => Http::response(['success' => true, 'data' => []]),
        ]);

        $firstPage = $this->authenticate()->get(route('prestasi-mahasiswa.index'));
        $firstPage->assertOk();
        $firstPage->assertSee('Menampilkan 01–09 dari 10');
        $firstPage->assertSee('page=2');
        $firstPage->assertDontSee('Juara Nasional');

        $secondPage = $this->authenticate()->get(route('prestasi-mahasiswa.index', ['page' => 2]));
        $secondPage->assertOk();
        $secondPage->assertSee('Juara Nasional');

        $searchResult = $this->authenticate()->get(route('prestasi-mahasiswa.index', ['search' => 'Juara Nasional']));
        $searchResult->assertOk();
        $searchResult->assertSee('Juara Nasional');
        $searchResult->assertDontSee('Kegiatan 1');
    }
}
