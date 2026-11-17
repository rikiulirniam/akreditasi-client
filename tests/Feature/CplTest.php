<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CplTest extends TestCase
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
                'nama' => 'Admin Akademik',
                'role' => 'Administrator',
                'email' => 'admin@akademik.id',
            ],
        ]));
        $signature = base64_encode('fake_signature');

        return "{$header}.{$payload}.{$signature}";
    }

    public function test_cpl_index_displays_list_and_stats(): void
    {
        Http::fake([
            '*/api/cpl*' => Http::response([
                'success' => true,
                'data' => [
                    [
                        'id' => 1,
                        'id_kurikulum' => 10,
                        'kode_cpl' => 'CPL-01',
                        'deskripsi_cpl' => 'Mampu menerapkan etika profesi rekayasa perangkat lunak.',
                        'kategori' => 'Sikap',
                        'kurikulum' => [
                            'id' => 10,
                            'nama_kurikulum' => 'Kurikulum 2024 TI',
                        ],
                    ],
                    [
                        'id' => 2,
                        'id_kurikulum' => 10,
                        'kode_cpl' => 'CPL-02',
                        'deskripsi_cpl' => 'Menguasai konsep teoritis bidang komputasi sains.',
                        'kategori' => 'Pengetahuan',
                        'kurikulum' => [
                            'id' => 10,
                            'nama_kurikulum' => 'Kurikulum 2024 TI',
                        ],
                    ],
                ],
            ], 200),
            '*/api/kurikulum*' => Http::response([
                'success' => true,
                'data' => [
                    [
                        'id' => 10,
                        'nama_kurikulum' => 'Kurikulum 2024 TI',
                    ],
                ],
            ], 200),
        ]);

        $response = $this->withSession([
            'access_token' => $this->getFakeToken(),
            'user' => [
                'id' => 1,
                'nama' => 'Administrator',
                'role' => 'ADMINISTRATOR',
            ],
        ])->get(route('cpl.index'));

        $response->assertStatus(200);
        $response->assertSee('CPL-01');
        $response->assertSee('CPL-02');
        $response->assertSee('Sikap');
        $response->assertSee('Pengetahuan');
        $response->assertSee('Mampu menerapkan etika profesi');
        $response->assertSee('Kurikulum 2024 TI');
    }

    public function test_cpl_index_filters_by_category_and_search(): void
    {
        Http::fake([
            '*/api/cpl*' => Http::response([
                'success' => true,
                'data' => [
                    [
                        'id' => 1,
                        'id_kurikulum' => 10,
                        'kode_cpl' => 'CPL-SIKAP-99',
                        'deskripsi_cpl' => 'Mampu menerapkan integritas moral tinggi.',
                        'kategori' => 'Sikap',
                    ],
                    [
                        'id' => 2,
                        'id_kurikulum' => 10,
                        'kode_cpl' => 'CPL-ILMU-88',
                        'deskripsi_cpl' => 'Menguasai algoritma dan struktur data lanjutan.',
                        'kategori' => 'Pengetahuan',
                    ],
                ],
            ], 200),
            '*/api/kurikulum*' => Http::response([
                'success' => true,
                'data' => [
                    ['id' => 10, 'nama_kurikulum' => 'Kurikulum 2024 TI'],
                ],
            ], 200),
        ]);

        $response = $this->withSession([
            'access_token' => $this->getFakeToken(),
            'user' => [
                'id' => 1,
                'nama' => 'Administrator',
                'role' => 'ADMINISTRATOR',
            ],
        ])->get(route('cpl.index', ['kategori' => 'Pengetahuan']));

        $response->assertStatus(200);
        $response->assertSee('CPL-ILMU-88');
        $response->assertDontSee('CPL-SIKAP-99');
    }

    public function test_cpl_store_successfully_creates_cpl(): void
    {
        Http::fake([
            '*/api/cpl' => Http::response([
                'success' => true,
                'message' => 'CPL berhasil ditambahkan',
                'data' => [
                    'id' => 99,
                    'id_kurikulum' => 10,
                    'kode_cpl' => 'CPL-05',
                    'deskripsi_cpl' => 'Mampu merancang aplikasi enterprise modern.',
                    'kategori' => 'Keterampilan Khusus',
                ],
            ], 201),
            '*/api/kurikulum*' => Http::response([
                'success' => true,
                'data' => [
                    ['id' => 10, 'nama_kurikulum' => 'Kurikulum 2024 TI'],
                ],
            ], 200),
        ]);

        $response = $this->withSession([
            'access_token' => $this->getFakeToken(),
            'user' => [
                'id' => 1,
                'nama' => 'Administrator',
                'role' => 'ADMINISTRATOR',
            ],
        ])->post(route('cpl.store'), [
            'id_kurikulum' => 10,
            'kode_cpl' => 'CPL-05',
            'deskripsi_cpl' => 'Mampu merancang aplikasi enterprise modern.',
            'kategori' => 'Keterampilan Khusus',
        ]);

        $response->assertRedirect(route('cpl.index'));
        $response->assertSessionHas('toast', function ($toast) {
            return ($toast['type'] ?? '') === 'success';
        });
    }

    public function test_cpl_store_validates_required_fields(): void
    {
        $response = $this->withSession([
            'access_token' => $this->getFakeToken(),
            'user' => [
                'id' => 1,
                'nama' => 'Administrator',
                'role' => 'ADMINISTRATOR',
            ],
        ])->post(route('cpl.store'), []);

        $response->assertSessionHasErrors(['id_kurikulum', 'kode_cpl', 'deskripsi_cpl', 'kategori']);
    }

    public function test_cpl_update_successfully_updates_cpl(): void
    {
        Http::fake([
            '*/api/cpl/1' => Http::response([
                'success' => true,
                'message' => 'CPL berhasil diperbarui',
                'data' => [
                    'id' => 1,
                    'id_kurikulum' => 10,
                    'kode_cpl' => 'CPL-01-REV',
                    'deskripsi_cpl' => 'Mampu menerapkan etika dan integritas akademik.',
                    'kategori' => 'Sikap',
                ],
            ], 200),
            '*/api/kurikulum*' => Http::response([
                'success' => true,
                'data' => [
                    ['id' => 10, 'nama_kurikulum' => 'Kurikulum 2024 TI'],
                ],
            ], 200),
        ]);

        $response = $this->withSession([
            'access_token' => $this->getFakeToken(),
            'user' => [
                'id' => 1,
                'nama' => 'Administrator',
                'role' => 'ADMINISTRATOR',
            ],
        ])->put(route('cpl.update', 1), [
            'id_kurikulum' => 10,
            'kode_cpl' => 'CPL-01-REV',
            'deskripsi_cpl' => 'Mampu menerapkan etika dan integritas akademik.',
            'kategori' => 'Sikap',
        ]);

        $response->assertRedirect(route('cpl.index'));
        $response->assertSessionHas('toast', function ($toast) {
            return ($toast['type'] ?? '') === 'success';
        });
    }

    public function test_cpl_destroy_successfully_deletes_cpl(): void
    {
        Http::fake([
            '*/api/cpl/1' => Http::response([
                'success' => true,
                'message' => 'CPL berhasil dihapus',
            ], 200),
        ]);

        $response = $this->withSession([
            'access_token' => $this->getFakeToken(),
            'user' => [
                'id' => 1,
                'nama' => 'Administrator',
                'role' => 'ADMINISTRATOR',
            ],
        ])->delete(route('cpl.destroy', 1));

        $response->assertRedirect(route('cpl.index'));
        $response->assertSessionHas('toast', function ($toast) {
            return ($toast['type'] ?? '') === 'success';
        });
    }
}
