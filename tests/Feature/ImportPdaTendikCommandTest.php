<?php

namespace Tests\Feature;

use App\Console\Commands\ImportPdaTendikCommand;
use App\Models\TenagaKependidikan;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Client\Request as ClientRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ImportPdaTendikCommandTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('tenaga_kependidikan');
        Schema::dropIfExists('pendidikan');

        Schema::create('pendidikan', function (Blueprint $table): void {
            $table->id('id_pendidikan');
            $table->string('jenjang');
            $table->timestamps();
        });

        Schema::create('tenaga_kependidikan', function (Blueprint $table): void {
            $table->id('id_tendik');
            $table->unsignedBigInteger('id_pendidikan');
            $table->string('nama_tendik');
            $table->string('jabatan_tendik');
            $table->string('nip')->unique();
            $table->text('sertifikasi')->nullable();
            $table->timestamps();
        });

        DB::table('pendidikan')->insert([
            ['id_pendidikan' => 1, 'jenjang' => 'S2', 'created_at' => now(), 'updated_at' => now()],
            ['id_pendidikan' => 3, 'jenjang' => 'S3', 'created_at' => now(), 'updated_at' => now()],
            ['id_pendidikan' => 4, 'jenjang' => 'D4/S1', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function test_imports_tendik_from_html_data_page_and_syncs_without_duplicates(): void
    {
        $page1Inertia = [
            'component' => 'Public/TendikIndex',
            'props' => [
                'listTendik' => [
                    'current_page' => 1,
                    'last_page' => 2,
                    'total' => 2,
                    'data' => [
                        [
                            'slug' => 'adang-kurniawan-69cdd9ec3f8f5',
                            'nama' => 'Adang Kurniawan',
                            'gelar_depan' => 'Ir.',
                            'gelar_belakang' => 'S.T.',
                            'nip' => null,
                            'jabatan' => 'Pranata Laboratorium Pendidikan Ahli Muda',
                            'pangkat' => 'Penata Tingkat I',
                            'golongan' => 'III/d',
                            'penempatan_nama' => 'Gedung Laboratorium Mesin',
                        ],
                    ],
                ],
            ],
        ];

        $page2Inertia = [
            'component' => 'Public/TendikIndex',
            'props' => [
                'listTendik' => [
                    'current_page' => 2,
                    'last_page' => 2,
                    'total' => 2,
                    'data' => [
                        [
                            'slug' => 'ayu-jayanti-69cdd9ec3a541',
                            'nama' => 'Ayu Jayanti',
                            'gelar_depan' => null,
                            'gelar_belakang' => 'S.A.P., M.Si.',
                            'nip' => '199001012020122001',
                            'jabatan' => 'Analis Sumber Daya Manusia Ahli Pertama',
                            'pangkat' => 'Golongan IX',
                            'golongan' => 'IX',
                            'penempatan_nama' => 'Kepegawaian',
                        ],
                    ],
                ],
            ],
        ];

        $htmlPage1 = '<!DOCTYPE html><html><body><div id="app" data-page="'
            .htmlspecialchars(json_encode($page1Inertia, JSON_THROW_ON_ERROR), ENT_QUOTES, 'UTF-8')
            .'"></div></body></html>';

        $page1InertiaUpdated = $page1Inertia;
        $page1InertiaUpdated['props']['listTendik']['data'][0]['jabatan'] = 'Kepala Laboratorium Mesin';
        $htmlPage1Updated = '<!DOCTYPE html><html><body><div id="app" data-page="'
            .htmlspecialchars(json_encode($page1InertiaUpdated, JSON_THROW_ON_ERROR), ENT_QUOTES, 'UTF-8')
            .'"></div></body></html>';

        Http::fake([
            'https://pda.polines.ac.id/tendik?mode=tabel&page=1' => Http::sequence()
                ->push($htmlPage1, 200)
                ->push($htmlPage1Updated, 200),
            'https://pda.polines.ac.id/tendik?mode=tabel&page=2' => Http::sequence()
                ->push($page2Inertia, 200)
                ->push($page2Inertia, 200),
        ]);

        $this->artisan('pda:import-tendik')
            ->assertSuccessful();

        $this->assertDatabaseCount('tenaga_kependidikan', 2);

        $expectedFallbackNip = ImportPdaTendikCommand::generateDummyNip('adang-kurniawan-69cdd9ec3f8f5');
        $this->assertMatchesRegularExpression('/^\d{18}$/', $expectedFallbackNip);

        $this->assertDatabaseHas('tenaga_kependidikan', [
            'nip' => $expectedFallbackNip,
            'nama_tendik' => 'Ir. Adang Kurniawan, S.T.',
            'jabatan_tendik' => 'Pranata Laboratorium Pendidikan Ahli Muda',
            'sertifikasi' => 'Pangkat: Penata Tingkat I | Golongan: III/d | Unit Penempatan: Gedung Laboratorium Mesin',
            'id_pendidikan' => 4,
        ]);

        $this->assertDatabaseHas('tenaga_kependidikan', [
            'nip' => '199001012020122001',
            'nama_tendik' => 'Ayu Jayanti, S.A.P., M.Si.',
            'jabatan_tendik' => 'Analis Sumber Daya Manusia Ahli Pertama',
            'sertifikasi' => 'Pangkat: Golongan IX | Golongan: IX | Unit Penempatan: Kepegawaian',
            'id_pendidikan' => 1,
        ]);

        Http::assertSent(fn (ClientRequest $request) => $request->url() === 'https://pda.polines.ac.id/tendik?mode=tabel&page=1'
            && str_contains($request->header('User-Agent')[0] ?? '', 'Mozilla/5.0'));

        // Jalankan ulang dengan perubahan jabatan untuk memastikan updateOrCreate tidak membuat duplikat
        $this->artisan('pda:import-tendik')
            ->assertSuccessful();

        $this->assertDatabaseCount('tenaga_kependidikan', 2);
        $this->assertSame(
            'Kepala Laboratorium Mesin',
            TenagaKependidikan::query()->where('nip', $expectedFallbackNip)->value('jabatan_tendik'),
        );
    }

    public function test_schedule_registers_pda_import_tendik_twice_monthly_at_02_30(): void
    {
        $schedule = app(Schedule::class);

        $events = collect($schedule->events())->filter(
            fn ($event) => str_contains((string) $event->command, 'pda:import-tendik')
        );

        $this->assertNotEmpty($events, 'Schedule untuk pda:import-tendik belum terdaftar.');

        $event = $events->first();
        $this->assertSame('30 2 1,15 * *', $event->expression);
    }
}
