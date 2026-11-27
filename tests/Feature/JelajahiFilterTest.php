<?php

namespace Tests\Feature;

use App\Services\DepotFrontendData;
use Mockery;
use Tests\TestCase;

class JelajahiFilterTest extends TestCase
{
    private const KELURAHAN = [
        'Babakan', 'Babakan Pasar', 'Cibogor', 'Ciwaringin', 'Gudang', 'Kebon Kalapa',
        'Pabaton', 'Paledang', 'Panaragan', 'Sempur', 'Tegallega',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $catalog = Mockery::mock(DepotFrontendData::class);
        $catalog->shouldReceive('catalog')->andReturn([
                'depots' => collect($this->sampleDepots()),
                'kelurahan' => collect(self::KELURAHAN),
            ]);
        $this->app->instance(DepotFrontendData::class, $catalog);
    }

    public function test_search_matches_name_address_and_ward_without_case_sensitivity(): void
    {
        $this->get(route('jelajahi', ['q' => 'aQuA']))
            ->assertOk()->assertSee('Aqua Alfa')->assertDontSee('Banyu Beta');

        $this->get(route('jelajahi', ['q' => 'MAWAR']))
            ->assertOk()->assertSee('Banyu Beta')->assertDontSee('Aqua Alfa');

        $this->get(route('jelajahi', ['q' => 'PaLeDaNg']))
            ->assertOk()->assertSee('Banyu Beta')->assertDontSee('Aqua Alfa');
    }

    public function test_ward_filter_shows_only_depots_in_selected_ward_and_keeps_official_options(): void
    {
        $response = $this->get(route('jelajahi', ['kelurahan' => 'Babakan']))->assertOk();

        $response->assertSee('Aqua Alfa')->assertSee('Citra Gamma')->assertDontSee('Banyu Beta');
        foreach (self::KELURAHAN as $ward) {
            $response->assertSee('>' . $ward . '</option>', false);
        }
    }

    public function test_daily_delivery_and_combined_filters_work_together(): void
    {
        $this->get(route('jelajahi', ['harian' => '1']))
            ->assertOk()->assertSee('Aqua Alfa')->assertSee('Citra Gamma')->assertDontSee('Banyu Beta');

        $this->get(route('jelajahi', ['antar' => '1']))
            ->assertOk()->assertSee('Aqua Alfa')->assertSee('Banyu Beta')->assertDontSee('Citra Gamma');

        $this->get(route('jelajahi', [
            'q' => 'JALAN', 'kelurahan' => 'Babakan', 'harian' => '1', 'antar' => '1',
        ]))->assertOk()->assertSee('Aqua Alfa')->assertDontSee('Citra Gamma');
    }

    public function test_name_sorting_works_in_both_directions(): void
    {
        $ascending = $this->get(route('jelajahi', ['urut' => 'nama_asc']))->assertOk()->getContent();
        $descending = $this->get(route('jelajahi', ['urut' => 'nama_desc']))->assertOk()->getContent();

        $this->assertLessThan(strpos($ascending, 'Banyu Beta'), strpos($ascending, 'Aqua Alfa'));
        $this->assertLessThan(strpos($descending, 'Citra Gamma'), strpos($descending, 'Delta Depot'));
    }

    public function test_empty_results_show_count_and_informative_message(): void
    {
        $this->get(route('jelajahi', ['q' => 'no-such-depot']))
            ->assertOk()
            ->assertSee('0 depot ditemukan')
            ->assertSee('Tidak ditemukan depot yang cocok. Coba ubah pencarian atau filter.');
    }

    /** @return array<int, array<string, mixed>> */
    private function sampleDepots(): array
    {
        return [
            $this->depot(11, 'Aqua Alfa', 'Babakan', 'Jalan Melati', 5000, true, true),
            $this->depot(12, 'Banyu Beta', 'Paledang', 'Jalan Mawar', 6000, false, true),
            $this->depot(13, 'Citra Gamma', 'Babakan', 'Jalan Kenanga', 7000, true, false),
            $this->depot(14, 'Delta Depot', 'Ciwaringin', 'Gang Merdeka', 4000, false, false),
        ];
    }

    /** @return array<string, mixed> */
    private function depot(int $id, string $name, string $ward, string $address, int $price, bool $daily, bool $delivery): array
    {
        return [
            'id' => $id,
            'nama' => $name,
            'kelurahan' => $ward,
            'alamat' => $address,
            'lat' => -6.6,
            'lng' => 106.8,
            'harga' => $price,
            'jam_buka' => '08:00',
            'jam_tutup' => '18:00',
            'buka_setiap_hari' => $daily,
            'antar' => $delivery,
            'hari_libur' => [],
            'telepon' => null,
            'link_google_maps' => null,
        ];
    }
}
