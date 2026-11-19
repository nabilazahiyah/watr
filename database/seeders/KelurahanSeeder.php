<?php

namespace Database\Seeders;

use App\Models\Kelurahan;
use Illuminate\Database\Seeder;

/** Seeds clearly identified test-only locations; these are not survey results. */
class KelurahanSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            'DUMMY UJI - Kelurahan Uji 1',
            'DUMMY UJI - Kelurahan Uji 2',
        ] as $namaKelurahan) {
            Kelurahan::updateOrCreate(
                ['nama_kelurahan' => $namaKelurahan],
            );
        }
    }
}
