<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SidebarTriDharmaTest extends TestCase
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
                'nama' => 'Superadmin',
                'role' => 'SUPERADMIN',
                'email' => 'superadmin@example.com',
            ],
        ]));
        $signature = base64_encode('fake_signature');

        return "{$header}.{$payload}.{$signature}";
    }

    protected function authSession(): array
    {
        return [
            'access_token' => $this->getFakeToken(),
            'user' => [
                'nama' => 'Superadmin',
                'role' => 'SUPERADMIN',
                'email' => 'superadmin@example.com',
            ],
        ];
    }

    public function test_sidebar_contains_tri_dharma_with_clickable_penelitian_and_luaran_links(): void
    {
        $response = $this->withSession($this->authSession())->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Tri Dharma');
        $response->assertSee('submenu-tridharma');
        $response->assertSee(route('penelitian-pkm.index'));
        $response->assertSee(route('luaran.index'));
        $response->assertSee('Penelitian &amp; PKM', false);
        $response->assertSee('Luaran');
        // Ensure no leftover broken href="#" for Tri Dharma subitems
        $this->assertStringNotContainsString(
            '<div id="submenu-tridharma" class="submenu-container hidden pl-6 pr-2 pt-1.5 pb-1 space-y-1.5">',
            $response->getContent()
        );
        // Ensure standard submenu container styling is applied
        $response->assertSee('id="submenu-tridharma"', false);
        $response->assertSee('data-is-active=', false);
    }

    public function test_tri_dharma_submenu_is_active_on_penelitian_pkm_page(): void
    {
        Http::fake([
            '*kegiatan*' => Http::response([
                'success' => true,
                'data' => [
                    'data' => [],
                    'total' => 0,
                    'per_page' => 10,
                    'current_page' => 1,
                    'last_page' => 1,
                ],
            ]),
        ]);

        $response = $this->withSession($this->authSession())->get(route('penelitian-pkm.index'));

        $response->assertOk();
        $response->assertSee('data-is-active="true"', false);
        $response->assertSee(route('penelitian-pkm.index'));
    }

    public function test_tri_dharma_submenu_is_active_on_luaran_page(): void
    {
        Http::fake([
            '*luaran*' => Http::response([
                'success' => true,
                'data' => [
                    'items' => [],
                ],
            ]),
            '*publikasi*' => Http::response([
                'success' => true,
                'data' => [
                    'items' => [],
                ],
            ]),
            '*hki*' => Http::response([
                'success' => true,
                'data' => [
                    'items' => [],
                ],
            ]),
            '*buku*' => Http::response([
                'success' => true,
                'data' => [
                    'items' => [],
                ],
            ]),
        ]);

        $response = $this->withSession($this->authSession())->get(route('luaran.index'));

        $response->assertOk();
        $response->assertSee('data-is-active="true"', false);
        $response->assertSee(route('luaran.index'));
    }
}
