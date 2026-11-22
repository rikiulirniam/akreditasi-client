<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class UppsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    protected function getFakeToken(): string
    {
        $header = base64_encode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
        $payload = base64_encode(json_encode([
            'sub' => 1,
            'exp' => time() + 3600,
            'user' => [
                'nama' => 'Admin Akreditasi',
                'role' => 'Admin',
                'email' => 'admin@example.com',
            ],
            'permissions' => [
                'upps.read',
                'upps.create',
                'upps.update',
                'upps.delete',
                'prodi.read',
            ],
        ]));
        $signature = base64_encode('fake_signature');

        return "{$header}.{$payload}.{$signature}";
    }

    protected function authSession(): array
    {
        return [
            'access_token' => $this->getFakeToken(),
            'user' => ['nama' => 'Admin Akreditasi', 'role' => 'Admin'],
            'permissions' => [
                'upps.read',
                'upps.create',
                'upps.update',
                'upps.delete',
                'prodi.read',
            ],
        ];
    }

    public function test_authenticated_user_can_view_upps_index_page_with_multi_prodi_relation(): void
    {
        Http::fake([
            '*/api/upps*' => Http::response([
                'success' => true,
                'data' => [
                    [
                        'id' => 1,
                        'kode_upps' => 'JTE-01',
                        'nama_upps' => 'Jurusan Teknik Elektro',
                        'jenis' => 'Jurusan',
                        'pimpinan_upps' => 'Dr. Garup Lambang Goro, S.T., M.T.',
                        'prodi_ids' => [1, 2, 3],
                    ],
                ],
            ], 200),
            '*/api/prodi*' => Http::response([
                'success' => true,
                'data' => [
                    ['id' => 1, 'kode_prodi' => 'TL01', 'nama_prodi' => 'D3 Teknik Listrik', 'jenjang' => 'D3', 'akreditasi' => 'Unggul', 'id_upps' => 1],
                    ['id' => 2, 'kode_prodi' => 'TE02', 'nama_prodi' => 'D3 Teknik Elektronika', 'jenjang' => 'D3', 'akreditasi' => 'Unggul', 'id_upps' => 1],
                    ['id' => 3, 'kode_prodi' => 'TT03', 'nama_prodi' => 'D4 Teknik Telekomunikasi', 'jenjang' => 'D4', 'akreditasi' => 'Unggul', 'id_upps' => 1],
                ],
            ], 200),
        ]);

        $response = $this->withSession($this->authSession())
            ->get(route('upps.index'));

        $response->assertStatus(200);
        $response->assertSee('Jurusan Teknik Elektro');
        $response->assertSee('Dr. Garup Lambang Goro, S.T., M.T.');
        // Memastikan relasi multi-prodi tercermin di UI
        $response->assertSee('D3 Teknik Listrik');
        $response->assertSee('D3 Teknik Elektronika');
        $response->assertSee('Program Studi yang Dinaungi');
    }

    public function test_authenticated_user_can_view_upps_anggota_page_with_hierarchy(): void
    {
        Http::fake([
            '*/api/upps/1*' => Http::response([
                'success' => true,
                'data' => [
                    'id' => 1,
                    'kode_upps' => 'JTE-01',
                    'nama_upps' => 'Jurusan Teknik Elektro',
                    'jenis' => 'Jurusan',
                    'pimpinan_upps' => 'Dr. Garup Lambang Goro, S.T., M.T.',
                ],
            ], 200),
            '*/api/anggota-upps*' => Http::response([
                'success' => true,
                'data' => [
                    [
                        'id' => 1,
                        'nama' => 'Dr. Garup Lambang Goro, S.T., M.T.',
                        'jabatan' => 'Ketua Jurusan',
                        'level_jabatan' => 1,
                    ],
                    [
                        'id' => 2,
                        'nama' => 'Ir. Budi Santoso, M.T.',
                        'jabatan' => 'Sekretaris Jurusan',
                        'level_jabatan' => 2,
                    ],
                    [
                        'id' => 3,
                        'nama' => 'Dr. Amin Suharjono, S.T., M.T.',
                        'jabatan' => 'Koordinator Program Studi D3 Teknik Listrik',
                        'level_jabatan' => 3,
                    ],
                ],
            ], 200),
        ]);

        $response = $this->withSession($this->authSession())
            ->get(route('upps.anggota', 1));

        $response->assertStatus(200);
        // Memastikan hirarki jabatan tercermin di UI
        $response->assertSee('Pimpinan UPPS');
        $response->assertSee('Sekretaris UPPS');
        $response->assertSee('Koordinator / Kaprodi');
        $response->assertSee('Hirarki Jabatan');
        $response->assertSee('Ketua Jurusan');
    }

    public function test_authenticated_user_can_store_upps_with_multiple_prodis(): void
    {
        Http::fake([
            '*/api/upps' => Http::response([
                'success' => true,
                'message' => 'Data UPPS berhasil ditambahkan.',
                'data' => ['id' => 10],
            ], 201),
        ]);

        $response = $this->withSession($this->authSession())
            ->post(route('upps.store'), [
                'nama_upps' => 'Jurusan Teknik Mesin Baru',
                'pimpinan_upps' => 'Dr. Bambang, M.T.',
                'jenis' => 'Jurusan',
                'prodi_ids' => [4, 5],
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('toast.type', 'success');

        $customUpps = session('custom_upps_list', []);
        $found = false;
        foreach ($customUpps as $cu) {
            if ($cu['nama_upps'] === 'Jurusan Teknik Mesin Baru') {
                $found = true;
                $this->assertEquals([4, 5], $cu['prodi_ids']);
                break;
            }
        }
        $this->assertTrue($found, 'UPPS baru dengan multi-prodi berhasil tersimpan di session.');
    }

    public function test_authenticated_user_can_store_anggota_with_hierarchy_level(): void
    {
        Http::fake([
            '*/api/anggota-upps' => Http::response([
                'success' => true,
                'message' => 'Anggota berhasil ditambahkan.',
                'data' => ['id' => 99],
            ], 201),
        ]);

        $response = $this->withSession($this->authSession())
            ->post(route('upps.anggota.store', 1), [
                'nama' => 'Dosen Penjaminan Mutu, S.T., M.Kom.',
                'jabatan' => 'Koordinator Penjaminan Mutu UPPS',
                'level_jabatan' => 3,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('toast.type', 'success');

        $customAnggota = session('custom_anggota_upps_1', []);
        $found = false;
        foreach ($customAnggota as $ca) {
            if ($ca['nama'] === 'Dosen Penjaminan Mutu, S.T., M.Kom.') {
                $found = true;
                $this->assertEquals(3, $ca['level_jabatan']);
                break;
            }
        }
        $this->assertTrue($found, 'Anggota baru dengan level hirarki berhasil tersimpan.');
    }
}
