<?php

namespace Database\Seeders;

use App\Models\Depot;
use App\Models\DepotHariLibur;
use App\Models\Kelurahan;
use Illuminate\Database\Seeder;

/**
 * Seeds synthetic WATR map fixtures. Names, addresses, coordinates, and phone
 * numbers are simulation data only and must never be treated as survey results.
 */
class DummyWebGisDamiuSeeder extends Seeder
{
    /**
     * Each coordinate is an invented map-test point within Kota Bogor, not a
     * surveyed DAMIU location. The 10 wards are listed in Perwal Bogor 131/2019.
     */
    private const DATASET = [
        'Babakan' => [
            [1, -6.5862, 106.8053, 5000, '06:00:00', '18:00:00', false, true, ['Senin']],
            [2, -6.5871, 106.8060, 6500, '07:30:00', '20:00:00', true, false, []],
            [3, -6.5878, 106.8046, 5500, '08:00:00', '19:00:00', false, true, ['Jumat', 'Sabtu']],
        ],
        'Babakan Pasar' => [
            [4, -6.6010, 106.8004, 4500, '07:00:00', '17:00:00', true, true, []],
            [5, -6.6004, 106.8011, 7000, '08:30:00', '21:00:00', false, false, ['Selasa']],
            [6, -6.6017, 106.7998, 6000, '06:30:00', '18:30:00', true, true, []],
        ],
        'Cibogor' => [
            [7, -6.5845, 106.7923, 5000, '08:00:00', '17:00:00', false, false, ['Rabu']],
            [8, -6.5851, 106.7910, 7500, '06:00:00', '20:00:00', true, true, []],
            [9, -6.5837, 106.7916, 5500, '09:00:00', '18:00:00', false, true, ['Kamis', 'Minggu']],
        ],
        'Ciwaringin' => [
            [10, -6.5894, 106.7852, 6500, '07:00:00', '19:00:00', true, false, []],
            [11, -6.5887, 106.7840, 4000, '06:30:00', '16:30:00', false, true, ['Senin']],
            [12, -6.5901, 106.7846, 6000, '08:00:00', '20:00:00', true, true, []],
        ],
        'Gudang' => [
            [13, -6.6053, 106.8058, 5000, '06:00:00', '18:00:00', false, true, ['Selasa', 'Rabu']],
            [14, -6.6061, 106.8070, 7000, '09:00:00', '21:00:00', true, false, []],
            [15, -6.6046, 106.8067, 5500, '07:30:00', '17:30:00', false, true, ['Minggu']],
        ],
        'Kebon Kelapa' => [
            [16, -6.5950, 106.7871, 4500, '06:00:00', '16:00:00', true, false, []],
            [17, -6.5961, 106.7880, 8000, '08:00:00', '20:00:00', false, true, ['Kamis']],
            [18, -6.5943, 106.7884, 6000, '07:00:00', '19:00:00', true, true, []],
        ],
        'Pabaton' => [
            [19, -6.5902, 106.7910, 5000, '07:00:00', '18:00:00', false, false, ['Jumat']],
            [20, -6.5910, 106.7922, 6500, '06:30:00', '20:00:00', true, true, []],
            [21, -6.5893, 106.7920, 5500, '08:30:00', '17:30:00', false, true, ['Rabu', 'Kamis']],
        ],
        'Paledang' => [
            [22, -6.6085, 106.7993, 7000, '07:00:00', '19:00:00', true, false, []],
            [23, -6.6071, 106.7980, 5000, '06:00:00', '17:00:00', false, true, ['Selasa']],
            [24, -6.6092, 106.8001, 6000, '09:00:00', '20:00:00', true, true, []],
        ],
        'Panaragan' => [
            [25, -6.5930, 106.7834, 4500, '06:30:00', '18:30:00', false, false, ['Senin', 'Minggu']],
            [26, -6.5924, 106.7845, 7500, '08:00:00', '21:00:00', true, true, []],
            [27, -6.5941, 106.7841, 5500, '07:00:00', '17:00:00', false, true, ['Sabtu']],
        ],
        'Sempur' => [
            [28, -6.5818, 106.8072, 5000, '06:00:00', '18:00:00', true, true, []],
            [29, -6.5828, 106.8085, 6500, '08:00:00', '20:00:00', false, false, ['Rabu']],
            [30, -6.5809, 106.8061, 6000, '07:30:00', '19:30:00', true, true, []],
        ],
    ];

    public function run(): void
    {
        foreach (self::DATASET as $namaKelurahan => $depots) {
            $kelurahan = Kelurahan::firstOrCreate([
                'nama_kelurahan' => $namaKelurahan,
            ]);

            foreach ($depots as [$nomor, $latitude, $longitude, $harga, $jamBuka, $jamTutup, $bukaSetiapHari, $isDelivery, $hariLibur]) {
                $namaDepot = sprintf('SIMULASI WATR - Depot %02d', $nomor);

                $depot = Depot::firstOrCreate(
                    ['nama_depot' => $namaDepot],
                    [
                        'kelurahan_id' => $kelurahan->id,
                        'alamat' => "Titik koordinat simulasi di Kelurahan {$namaKelurahan}, Kota Bogor; bukan lokasi DAMIU aktual.",
                        'link_google_maps' => "https://maps.google.com/?q={$latitude},{$longitude}",
                        'latitude' => $latitude,
                        'longitude' => $longitude,
                        'harga_per_galon' => $harga,
                        'jam_buka' => $jamBuka,
                        'jam_tutup' => $jamTutup,
                        'buka_setiap_hari' => $bukaSetiapHari,
                        'is_delivery' => $isDelivery,
                        'telepon' => sprintf('000-000-%04d', $nomor),
                    ],
                );

                foreach ($hariLibur as $hari) {
                    DepotHariLibur::firstOrCreate([
                        'depot_id' => $depot->id,
                        'hari' => $hari,
                    ]);
                }
            }
        }
    }
}
