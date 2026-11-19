<?php

namespace Tests\Feature;

use App\Models\Depot;
use App\Models\DepotHariLibur;
use App\Models\Kelurahan;
use App\Services\DepotFrontendData;
use Illuminate\Database\Connection;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class DamiuDatabaseTest extends TestCase
{
    private ?Connection $testConnection = null;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'app.key' => 'base64:'.base64_encode(str_repeat('x', 32)),
            'database.default' => 'pgsql',
            'database.connections.pgsql.database' => 'db_watr',
        ]);

        DB::purge('pgsql');

        $this->testConnection = DB::connection('pgsql');
        $this->testConnection->beginTransaction();
    }

    protected function tearDown(): void
    {
        if ($this->testConnection?->transactionLevel() > 0) {
            $this->testConnection->rollBack();
        }

        parent::tearDown();
    }

    public function test_kelurahan_has_many_depots(): void
    {
        $kelurahan = $this->createKelurahan();
        $depotSatu = $this->createDepot($kelurahan);
        $depotDua = $this->createDepot($kelurahan);

        $this->assertEqualsCanonicalizing(
            [$depotSatu->id, $depotDua->id],
            $kelurahan->depots->modelKeys(),
        );
    }

    public function test_depot_belongs_to_one_kelurahan(): void
    {
        $kelurahan = $this->createKelurahan();
        $depot = $this->createDepot($kelurahan);

        $this->assertSame($kelurahan->id, $depot->kelurahan->id);
    }

    public function test_depot_has_many_hari_libur(): void
    {
        $depot = $this->createDepot();
        $senin = $this->createHariLibur($depot, 'Senin');
        $selasa = $this->createHariLibur($depot, 'Selasa');

        $this->assertEqualsCanonicalizing(
            [$senin->id, $selasa->id],
            $depot->hariLibur->modelKeys(),
        );
    }

    public function test_hari_libur_belongs_to_the_correct_depot(): void
    {
        $depotSatu = $this->createDepot();
        $depotDua = $this->createDepot();
        $hariLibur = $this->createHariLibur($depotSatu, 'Jumat');

        $this->assertSame($depotSatu->id, $hariLibur->depot->id);
        $this->assertNotSame($depotDua->id, $hariLibur->depot->id);
    }

    public function test_home_page_renders_database_depot_data(): void
    {
        $depot = Depot::query()
            ->where('nama_depot', 'DUMMY UJI - Depot Cempaka')
            ->firstOrFail();

        $this->get(route('home'))
            ->assertOk()
            ->assertSee($depot->nama_depot)
            ->assertSee('Rp5.000 per galon')
            ->assertSee($depot->alamat);
    }

    public function test_explore_page_embeds_database_depot_data_for_leaflet(): void
    {
        $depot = $this->createDepot(attributes: [
            'nama_depot' => 'DUMMY TEST - Jelajahi',
        ]);

        $this->get(route('jelajahi'))
            ->assertOk()
            ->assertSee('window.DEPOTS =')
            ->assertSee($depot->nama_depot)
            ->assertSee((string) $depot->id)
            ->assertSee($depot->alamat);
    }

    public function test_tentang_route_renders_the_new_frontend_page(): void
    {
        $this->get(route('tentang'))
            ->assertOk()
            ->assertSee('Tentang WATR');
    }

    public function test_pages_render_empty_catalog_state(): void
    {
        $emptyCatalog = \Mockery::mock(DepotFrontendData::class);
        $emptyCatalog->shouldReceive('catalog')->twice()->andReturn([
            'depots' => collect(),
            'kelurahan' => collect(),
        ]);
        $this->app->instance(DepotFrontendData::class, $emptyCatalog);
        $this->withoutVite();

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Belum ada data depot.');

        $this->get(route('jelajahi'))
            ->assertOk()
            ->assertSee('window.DEPOTS = []')
            ->assertSee('Belum ada data depot.');
    }

    public function test_database_rejects_latitude_outside_valid_range(): void
    {
        $this->expectException(QueryException::class);

        $this->createDepot(attributes: ['latitude' => 90.0000001]);
    }

    public function test_database_rejects_longitude_outside_valid_range(): void
    {
        $this->expectException(QueryException::class);

        $this->createDepot(attributes: ['longitude' => -180.0000001]);
    }

    public function test_database_rejects_negative_price(): void
    {
        $this->expectException(QueryException::class);

        $this->createDepot(attributes: ['harga_per_galon' => -1]);
    }

    public function test_database_rejects_invalid_holiday_name(): void
    {
        $depot = $this->createDepot();

        $this->expectException(QueryException::class);

        $this->createHariLibur($depot, 'Funday');
    }

    public function test_database_rejects_duplicate_holiday_for_same_depot(): void
    {
        $depot = $this->createDepot();
        $this->createHariLibur($depot, 'Minggu');

        $this->expectException(QueryException::class);

        $this->createHariLibur($depot, 'Minggu');
    }

    public function test_database_rejects_depot_with_unknown_kelurahan(): void
    {
        $this->expectException(QueryException::class);

        $this->createDepot(kelurahanId: PHP_INT_MAX);
    }

    public function test_database_rejects_duplicate_kelurahan_name(): void
    {
        $kelurahan = $this->createKelurahan();

        $this->expectException(QueryException::class);

        Kelurahan::create([
            'nama_kelurahan' => $kelurahan->nama_kelurahan,
        ]);
    }

    public function test_database_rejects_holiday_with_unknown_depot(): void
    {
        $this->expectException(QueryException::class);

        DepotHariLibur::create([
            'depot_id' => PHP_INT_MAX,
            'hari' => 'Minggu',
        ]);
    }

    private function createKelurahan(): Kelurahan
    {
        return Kelurahan::create([
            'nama_kelurahan' => 'DUMMY TEST - '.Str::uuid(),
        ]);
    }

    private function createDepot(?Kelurahan $kelurahan = null, array $attributes = [], ?int $kelurahanId = null): Depot
    {
        $kelurahan ??= $kelurahanId === null ? $this->createKelurahan() : null;

        return Depot::create(array_merge([
            'kelurahan_id' => $kelurahanId ?? $kelurahan->id,
            'nama_depot' => 'DUMMY TEST - '.Str::uuid(),
            'alamat' => 'Alamat fixture pengujian.',
            'link_google_maps' => 'https://example.test/dummy-depot',
            'latitude' => -6.5981000,
            'longitude' => 106.7971000,
            'harga_per_galon' => 5000,
            'jam_buka' => '08:00:00',
            'jam_tutup' => '18:00:00',
            'buka_setiap_hari' => true,
            'is_delivery' => false,
            'telepon' => null,
        ], $attributes));
    }

    private function createHariLibur(Depot $depot, string $hari): DepotHariLibur
    {
        return DepotHariLibur::create([
            'depot_id' => $depot->id,
            'hari' => $hari,
        ]);
    }
}
