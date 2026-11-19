<?php

namespace Database\Seeders;

use App\Models\Depot;
use App\Models\DepotHariLibur;
use Illuminate\Database\Seeder;

/** Seeds clearly identified test-only schedules; these are not survey results. */
class DepotHariLiburSeeder extends Seeder
{
    public function run(): void
    {
        $hariLiburPerDepot = [
            'DUMMY UJI - Depot Cempaka' => ['Senin', 'Selasa'],
            'DUMMY UJI - Depot Kenanga' => ['Rabu'],
            'DUMMY UJI - Depot Melati' => ['Minggu'],
        ];

        foreach ($hariLiburPerDepot as $namaDepot => $hariLibur) {
            $depot = Depot::where('nama_depot', $namaDepot)->firstOrFail();

            foreach ($hariLibur as $hari) {
                DepotHariLibur::updateOrCreate(
                    ['depot_id' => $depot->id, 'hari' => $hari],
                );
            }
        }
    }
}
