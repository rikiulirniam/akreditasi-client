<?php

namespace Database\Factories;

use App\Models\TenagaKependidikan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TenagaKependidikan>
 */
class TenagaKependidikanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama' => fake()->name(),
            'nip' => fake()->unique()->numerify('19##########01####'),
            'jabatan' => fake()->randomElement([
                'Kepala Tata Usaha',
                'Admin Akademik',
                'Laboran Komputer',
                'Staf Kemahasiswaan',
                'Teknisi Laboratorium',
                'Staf Keuangan',
                'Pustakawan',
                'Admin Prodi',
                'Pengelola Sarana Prasarana',
            ]),
            'kualifikasi' => fake()->randomElement([
                'S1 Manajemen',
                'D3 Administrasi Perkantoran',
                'S1 Teknik Informatika',
                'S1 Administrasi Publik',
                'D3 Teknik Elektronika',
                'S1 Akuntansi',
                'S1 Ilmu Perpustakaan',
                'D3 Administrasi',
                'D3 Manajemen Aset',
            ]),
        ];
    }
}
