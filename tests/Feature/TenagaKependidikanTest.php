<?php

namespace Tests\Feature;

use App\Models\TenagaKependidikan;
use Database\Seeders\TenagaKependidikanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenagaKependidikanTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(TenagaKependidikanSeeder::class);
        session(['access_token' => 'test-token']);
    }

    public function test_can_display_tenaga_kependidikan_index_page(): void
    {
        $response = $this->get(route('tenaga-kependidikan.index'));

        $response->assertStatus(200);
        $response->assertSee('Tenaga Kependidikan');
        $response->assertSee('Tambah Tendik');
        $response->assertSee('Bambang Triyono, S.E.');
    }

    public function test_can_search_by_name(): void
    {
        $response = $this->get(route('tenaga-kependidikan.index', ['cari' => 'Bambang']));

        $response->assertStatus(200);
        $response->assertSee('Bambang Triyono, S.E.');
        $response->assertDontSee('198509202008012003'); // Sri Wahyuni's NIP
    }

    public function test_can_filter_by_jabatan(): void
    {
        $response = $this->get(route('tenaga-kependidikan.index', ['jabatan' => 'Admin Akademik']));

        $response->assertStatus(200);
        $response->assertSee('Sri Wahyuni, A.Md.');
        $response->assertDontSee('197805122003121001'); // Bambang Triyono's NIP
    }

    public function test_can_store_new_tenaga_kependidikan_from_modal(): void
    {
        $payload = [
            'nama' => 'Test Tendik Baru, S.T.',
            'nip' => '199901012024011099',
            'jabatan' => 'Pranata Komputer',
        ];

        $response = $this->post(route('tenaga-kependidikan.store'), $payload);

        $response->assertRedirect(route('tenaga-kependidikan.index'));
        $this->assertDatabaseHas('tenaga_kependidikan', [
            'nip' => '199901012024011099',
            'nama' => 'Test Tendik Baru, S.T.',
            'jabatan' => 'Pranata Komputer',
            'kualifikasi' => '-',
        ]);
    }

    public function test_validates_required_fields(): void
    {
        $response = $this->post(route('tenaga-kependidikan.store'), []);

        $response->assertSessionHasErrors(['nama', 'nip', 'jabatan']);
    }

    public function test_can_update_tenaga_kependidikan(): void
    {
        $item = TenagaKependidikan::where('nip', '197805122003121001')->firstOrFail();

        $response = $this->put(route('tenaga-kependidikan.update', $item), [
            'nama' => 'Bambang Triyono, S.E., M.M.',
            'nip' => $item->nip,
            'jabatan' => 'Kepala Tata Usaha Utama',
            'kualifikasi' => 'S2 Manajemen',
        ]);

        $response->assertRedirect(route('tenaga-kependidikan.index'));
        $this->assertDatabaseHas('tenaga_kependidikan', [
            'id' => $item->id,
            'nama' => 'Bambang Triyono, S.E., M.M.',
            'jabatan' => 'Kepala Tata Usaha Utama',
        ]);

        // Revert back so sample data stays pure
        $item->update([
            'nama' => 'Bambang Triyono, S.E.',
            'jabatan' => 'Kepala Tata Usaha',
            'kualifikasi' => 'S1 Manajemen',
        ]);
    }

    public function test_can_delete_tenaga_kependidikan(): void
    {
        $dummy = TenagaKependidikan::create([
            'nama' => 'Dummy Tendik',
            'nip' => '199999999999999999',
            'jabatan' => 'Staff Test',
            'kualifikasi' => 'D3 Test',
        ]);

        $response = $this->delete(route('tenaga-kependidikan.destroy', $dummy));

        $response->assertRedirect(route('tenaga-kependidikan.index'));
        $this->assertDatabaseMissing('tenaga_kependidikan', [
            'id' => $dummy->id,
        ]);
    }
}
