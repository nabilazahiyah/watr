<?php

namespace Database\Seeders;

use App\Models\Depot;
use App\Models\DepotHariLibur;
use App\Models\Kelurahan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/** Seeds the seven surveyed DAMIU records retained from the final survey CSV. */
class SurveyDepotSeeder extends Seeder
{
    private const OFFICIAL_KELURAHAN = [
        'Babakan',
        'Babakan Pasar',
        'Cibogor',
        'Ciwaringin',
        'Gudang',
        'Kebon Kalapa',
        'Pabaton',
        'Paledang',
        'Panaragan',
        'Sempur',
        'Tegallega',
    ];

    private const DEPOTS = [
        [
            'nama_depot' => 'Moya Sehat Tirta',
            'kelurahan' => 'Tegallega',
            'alamat' => 'Jl. Tegal Lega, RT.01/RW.01, Tegallega, Kec. Bogor Tengah, Kota Bogor, Jawa Barat 16129',
            'latitude' => -6.5932549,
            'longitude' => 106.8147150,
            'jam_buka' => '08:00:00',
            'jam_tutup' => '17:30:00',
            'buka_setiap_hari' => true,
            'hari_libur' => [],
            'telepon' => '083198308626',
            'is_delivery' => true,
            'harga_per_galon' => 5000,
            'link_google_maps' => 'https://maps.app.goo.gl/phydMWh42NU6gALh9',
        ],
        [
            'nama_depot' => 'Jaya Tirta Alam',
            'kelurahan' => 'Kebon Kalapa',
            'alamat' => 'Jl. Veteran Gang Kepatihan 06 RT.03/RW.01, Gang Kepatihan, RT.01/RW.01, Kb. Klp., Kec. Bogor Tengah, Kota Bogor, Jawa Barat 16125',
            'latitude' => -6.5946011,
            'longitude' => 106.7843578,
            'jam_buka' => '07:00:00',
            'jam_tutup' => '21:00:00',
            'buka_setiap_hari' => true,
            'hari_libur' => [],
            'telepon' => '081513137534',
            'is_delivery' => true,
            'harga_per_galon' => 5000,
            'link_google_maps' => 'https://maps.app.goo.gl/wFxbnTHquJGjhMQb6',
        ],
        [
            'nama_depot' => 'Depot Air Minum Banyumas',
            'kelurahan' => 'Paledang',
            'alamat' => 'Jl. Polisi 2 No.11, RT.04/RW.01, Paledang, Kec. Bogor Tengah, Kota Bogor, Jawa Barat 16122',
            'latitude' => -6.5975719,
            'longitude' => 106.7919153,
            'jam_buka' => '07:30:00',
            'jam_tutup' => '18:00:00',
            'buka_setiap_hari' => false,
            'hari_libur' => ['Minggu'],
            'telepon' => '08985220344',
            'is_delivery' => true,
            'harga_per_galon' => 5000,
            'link_google_maps' => 'https://maps.app.goo.gl/CRWCCmiD2JL1p1EP7',
        ],
        [
            'nama_depot' => 'Depot Isi Ulang Air Minum Triple K (Ciwaringin)',
            'kelurahan' => 'Ciwaringin',
            'alamat' => 'Jl. Ciwaringin Komplek PU Blk 99/01, RT.01/RW.09, Ciwaringin, Kec. Bogor Tengah, Kota Bogor, Jawa Barat 16124',
            'latitude' => -6.5834201,
            'longitude' => 106.7895478,
            'jam_buka' => '07:00:00',
            'jam_tutup' => '21:00:00',
            'buka_setiap_hari' => true,
            'hari_libur' => [],
            'telepon' => '088808355714',
            'is_delivery' => false,
            'harga_per_galon' => 6000,
            'link_google_maps' => 'https://maps.app.goo.gl/DHZtUH9ob4KsCykA6',
        ],
        [
            'nama_depot' => 'DAMIU SHiST Water Station',
            'kelurahan' => 'Kebon Kalapa',
            'alamat' => 'Jl. Kb. Kopi No.3, RT.03/RW.09, Kb. Klp., Kec. Bogor Tengah, Kota Bogor, Jawa Barat 16125',
            'latitude' => -6.5908759,
            'longitude' => 106.7830187,
            'jam_buka' => '08:30:00',
            'jam_tutup' => '17:00:00',
            'buka_setiap_hari' => false,
            'hari_libur' => ['Minggu'],
            'telepon' => '085773743400',
            'is_delivery' => true,
            'harga_per_galon' => 5000,
            'link_google_maps' => 'https://maps.app.goo.gl/tx7de8jDekxBxSfBA',
        ],
        [
            'nama_depot' => 'Tirta Bogor Baru',
            'kelurahan' => 'Tegallega',
            'alamat' => 'Jl. Danau Kalimutu, RT.07/RW.03, Tegallega, Kec. Bogor Tengah, Kota Bogor, Jawa Barat 16129',
            'latitude' => -6.5947871,
            'longitude' => 106.8108262,
            'jam_buka' => '07:00:00',
            'jam_tutup' => '21:00:00',
            'buka_setiap_hari' => true,
            'hari_libur' => [],
            'telepon' => '081310268493',
            'is_delivery' => true,
            'harga_per_galon' => 5000,
            'link_google_maps' => 'https://maps.app.goo.gl/bWrAK6Bk1me6XDVZ7',
        ],
        [
            'nama_depot' => 'Bintang Water',
            'kelurahan' => 'Tegallega',
            'alamat' => 'Jl. Malabar Ujung No.8, RT.01/RW.02, Tegallega, Kec. Bogor Tengah, Kota Bogor, Jawa Barat 16129',
            'latitude' => -6.5928814,
            'longitude' => 106.8075097,
            'jam_buka' => '08:00:00',
            'jam_tutup' => '16:00:00',
            'buka_setiap_hari' => true,
            'hari_libur' => [],
            'telepon' => '0895352970483',
            'is_delivery' => true,
            'harga_per_galon' => 7000,
            'link_google_maps' => 'https://maps.app.goo.gl/bbW4X1Q7oPQxNmQr9',
        ],
    ];

    public static function verifiedMapLinks(): array
    {
        return array_column(self::DEPOTS, 'link_google_maps');
    }

    public static function officialKelurahanNames(): array
    {
        return self::OFFICIAL_KELURAHAN;
    }

    public function run(): void
    {
        DB::transaction(function (): void {
            $kelurahanIds = [];

            foreach (self::OFFICIAL_KELURAHAN as $namaKelurahan) {
                $kelurahanIds[$namaKelurahan] = Kelurahan::firstOrCreate([
                    'nama_kelurahan' => $namaKelurahan,
                ])->id;
            }

            foreach (self::DEPOTS as $data) {
                $namaKelurahan = $data['kelurahan'];
                $hariLibur = $data['hari_libur'];
                unset($data['kelurahan'], $data['hari_libur']);

                $depot = Depot::updateOrCreate(
                    ['link_google_maps' => $data['link_google_maps']],
                    [
                        ...$data,
                        'kelurahan_id' => $kelurahanIds[$namaKelurahan],
                    ],
                );

                $hariLiburQuery = DepotHariLibur::where('depot_id', $depot->id);
                if ($hariLibur === []) {
                    $hariLiburQuery->delete();
                } else {
                    $hariLiburQuery->whereNotIn('hari', $hariLibur)->delete();
                    foreach ($hariLibur as $hari) {
                        DepotHariLibur::firstOrCreate([
                            'depot_id' => $depot->id,
                            'hari' => $hari,
                        ]);
                    }
                }
            }
        });
    }
}
