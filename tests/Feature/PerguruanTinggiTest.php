<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PerguruanTinggiTest extends TestCase
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

    public function test_authenticated_user_can_view_sk_pendirian_in_perguruan_tinggi(): void
    {
        Http::fake([
            '*/api/perguruan-tinggi*' => Http::response([
                'success' => true,
                'data' => [
                    'id' => 1,
                    'kode_pt' => 'PT001',
                    'nama_pt' => 'Politeknik Negeri Semarang',
                    'alamat' => 'Jl. Prof. Sudarto',
                    'pimpinan' => 'Dr. Ir. Supriyadi, M.T.',
                    'visi' => 'Visi test',
                    'misi' => 'Misi test',
                    'sk_pendirian' => 'SK/001/DIKTI/1992',
                    'tanggal_sk_pendirian' => '1992-04-05',
                ],
            ], 200),
            '*/api/upps*' => Http::response(['success' => true, 'data' => []], 200),
            '*/api/prodi*' => Http::response(['success' => true, 'data' => []], 200),
        ]);

        $response = $this->withSession($this->authSession())
            ->get(route('perguruan-tinggi.index'));

        $response->assertStatus(200);
        $response->assertSee('SK Pendirian:');
        $response->assertSee('SK/001/DIKTI/1992');
        $response->assertSee('Tanggal SK Pendirian:');
        $response->assertSee('5 April 1992');
    }

    public function test_user_can_update_sk_pendirian(): void
    {
        Http::fake([
            '*/api/perguruan-tinggi/*' => Http::response([
                'success' => true,
                'message' => 'Data Perguruan Tinggi berhasil disimpan.',
                'data' => [
                    'id' => 1,
                    'sk_pendirian' => 'SK/999/DIKTI/2026',
                    'tanggal_sk_pendirian' => '2026-01-01',
                ],
            ], 200),
        ]);

        $response = $this->withSession($this->authSession())
            ->put(route('perguruan-tinggi.update', 1), [
                'nama_pt' => 'Politeknik Negeri Semarang Updated',
                'pimpinan' => 'Direktur Baru',
                'alamat' => 'Alamat Baru',
                'visi' => 'Visi Baru',
                'misi' => 'Misi Baru',
                'sk_pendirian' => 'SK/999/DIKTI/2026',
                'tanggal_sk_pendirian' => '2026-01-01',
            ]);

        $response->assertRedirect(route('perguruan-tinggi.index'));
        $response->assertSessionHas('toast');
    }
}
