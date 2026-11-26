<?php

namespace Database\Seeders;

use App\Models\Depot;
use App\Models\Kelurahan;
use Illuminate\Database\Seeder;

/** Seeds clearly identified test-only depots; these are not survey results. */
class DepotSeeder extends Seeder
{
    public function run(): void
    {
        $kelurahanSatu = Kelurahan::firstOrCreate([
            'nama_kelurahan' => 'DUMMY UJI - Kelurahan Uji 1',
        ]);

        $kelurahanDua = Kelurahan::firstOrCreate([
            'nama_kelurahan' => 'DUMMY UJI - Kelurahan Uji 2',
        ]);

        $depots = [
            [
                'nama_depot' => 'DUMMY UJI - Depot Cempaka',
                'kelurahan_id' => $kelurahanSatu->id,
                'alamat' => 'Alamat dummy untuk pengujian; bukan hasil survei.',
                'link_google_maps' => 'https://maps.google.com/?q=-6.5981000,106.7971000',
                'latitude' => -6.5981000,
                'longitude' => 106.7971000,
                'harga_per_galon' => 5000,
                'jam_buka' => '08:00:00',
                'jam_tutup' => '18:00:00',
                'buka_setiap_hari' => false,
                'is_delivery' => true,
                'telepon' => null,
            ],
            [
                'nama_depot' => 'DUMMY UJI - Depot Kenanga',
                'kelurahan_id' => $kelurahanSatu->id,
                'alamat' => 'Alamat dummy untuk pengujian; bukan hasil survei.',
                'link_google_maps' => 'https://maps.google.com/?q=-6.6002000,106.7992000',
                'latitude' => -6.6002000,
                'longitude' => 106.7992000,
                'harga_per_galon' => 6000,
                'jam_buka' => '07:30:00',
                'jam_tutup' => '17:30:00',
                'buka_setiap_hari' => true,
                'is_delivery' => false,
                'telepon' => null,
            ],
            [
                'nama_depot' => 'DUMMY UJI - Depot Melati',
                'kelurahan_id' => $kelurahanDua->id,
                'alamat' => 'Alamat dummy untuk pengujian; bukan hasil survei.',
                'link_google_maps' => 'https://maps.google.com/?q=-6.6023000,106.8013000',
                'latitude' => -6.6023000,
                'longitude' => 106.8013000,
                'harga_per_galon' => 5500,
                'jam_buka' => '09:00:00',
                'jam_tutup' => '20:00:00',
                'buka_setiap_hari' => true,
                'is_delivery' => true,
                'telepon' => null,
            ],
        ];

        foreach ($depots as $attributes) {
            Depot::updateOrCreate(
                ['nama_depot' => $attributes['nama_depot']],
                $attributes,
            );
        }
    }
}
