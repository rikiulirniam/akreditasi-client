<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class LulusanTest extends TestCase
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
                'nama' => 'Admin Test',
                'role' => 'Admin',
                'email' => 'admin@example.com',
            ],
        ]));
        $signature = base64_encode('fake_signature');

        return "{$header}.{$payload}.{$signature}";
    }

    protected function authSession(): array
    {
        return [
            'access_token' => $this->getFakeToken(),
            'user' => ['nama' => 'Admin Test', 'role' => 'Admin'],
        ];
    }

    public function test_unauthenticated_user_is_redirected_to_login(): void
    {
        $response = $this->get(route('lulusan.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_lulusan_index_with_summary_cards_and_badges(): void
    {
        Http::fake([
            '*/api/lulusan/ringkasan*' => Http::response([
                'success' => true,
                'data' => [
                    'total_lulusan' => 120,
                    'rata_rata_masa_studi_bulan' => 44.5,
                    'rata_rata_ipk' => 3.54,
                    'persen_lulus_tepat_waktu' => 87.5,
                    'jumlah_lulus_tepat_waktu' => 105,
                ],
            ], 200),
            '*/api/lulusan*' => Http::response([
                'success' => true,
                'data' => [
                    [
                        'id' => 12,
                        'nim' => 33420005,
                        'nim_display' => '3.34.20.0.05',
                        'nama_mahasiswa' => 'Budi Santoso',
                        'tahun_masuk' => 2020,
                        'jenis_pendaftaran' => 'baru',
                        'tanggal_kelulusan' => '2024-08-25',
                        'jenjang_prodi' => 'D4',
                        'batas_masa_studi_bulan' => 48,
                        'masa_studi_bulan' => 48,
                        'ipk_riil' => 3.78,
                        'total_sks' => 144,
                        'lulus_tepat_waktu' => true,
                    ],
                    [
                        'id' => 13,
                        'nim' => 33420006,
                        'nim_display' => '3.34.20.0.06',
                        'nama_mahasiswa' => 'Siti Aminah',
                        'tahun_masuk' => 2019,
                        'jenis_pendaftaran' => 'transfer',
                        'tanggal_kelulusan' => '2024-08-25',
                        'jenjang_prodi' => 'D4',
                        'batas_masa_studi_bulan' => 48,
                        'masa_studi_bulan' => 54,
                        'ipk_riil' => 3.45,
                        'total_sks' => 144,
                        'lulus_tepat_waktu' => false,
                    ],
                ],
                'pagination' => [
                    'total' => 2,
                    'per_page' => 25,
                    'current_page' => 1,
                    'last_page' => 1,
                ],
            ], 200),
            '*/api/prodi*' => Http::response([
                'success' => true,
                'data' => [
                    ['id' => 1, 'nama_prodi' => 'Teknologi Rekayasa Komputer', 'jenjang' => 'D4'],
                ],
            ], 200),
            '*/api/angkatan*' => Http::response([
                'success' => true,
                'data' => [
                    ['id' => 1, 'angkatan' => 2020],
                ],
            ], 200),
        ]);

        $response = $this->withSession($this->authSession())
            ->get(route('lulusan.index'));

        $response->assertStatus(200);
        $response->assertSee('Data Lulusan');
        $response->assertSee('120'); // Total lulusan
        $response->assertSee('87.5%'); // Persen tepat waktu
        $response->assertSee('3.54'); // Rata-rata IPK
        $response->assertSee('Budi Santoso');
        $response->assertSee('3.34.20.0.05');
        $response->assertSee('Tepat Waktu');
        $response->assertSee('Siti Aminah');
        $response->assertSee('Terlambat');
        $response->assertSee('48 bln');
        $response->assertDontSee('/ 48 bln');
        $response->assertDontSee('Total SKS');
        $response->assertDontSee('IPK Riil');
    }
}
