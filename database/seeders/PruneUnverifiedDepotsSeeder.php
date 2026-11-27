<?php

namespace Database\Seeders;

use App\Models\Depot;
use App\Models\DepotHariLibur;
use App\Models\Kelurahan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use LogicException;

/** Keeps the seven surveyed depots and the 11 official Bogor Tengah wards. */
class PruneUnverifiedDepotsSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $this->call(SurveyDepotSeeder::class);

            $verifiedLinks = SurveyDepotSeeder::verifiedMapLinks();
            $officialKelurahan = SurveyDepotSeeder::officialKelurahanNames();
            $keepDepotIds = Depot::query()
                ->whereIn('link_google_maps', $verifiedLinks)
                ->selectRaw('MIN(id) AS id')
                ->groupBy('link_google_maps')
                ->pluck('id')
                ->all();

            if (count($keepDepotIds) !== count($verifiedLinks)) {
                throw new LogicException('Tidak semua depot survei dapat dipastikan berdasarkan tautan Google Maps. Pembersihan dibatalkan.');
            }

            $removeDepotIds = Depot::query()
                ->whereNotIn('id', $keepDepotIds)
                ->pluck('id')
                ->all();

            if ($removeDepotIds !== []) {
                DepotHariLibur::query()->whereIn('depot_id', $removeDepotIds)->delete();
                Depot::query()->whereIn('id', $removeDepotIds)->delete();
            }

            if (Kelurahan::query()
                ->whereNotIn('nama_kelurahan', $officialKelurahan)
                ->whereHas('depots')
                ->exists()) {
                throw new LogicException('Masih ada depot yang merujuk kelurahan nonresmi. Pembersihan kelurahan dibatalkan.');
            }

            Kelurahan::query()
                ->whereNotIn('nama_kelurahan', $officialKelurahan)
                ->delete();

            if (Kelurahan::query()->count() !== count($officialKelurahan)) {
                throw new LogicException('Jumlah kelurahan setelah sinkronisasi bukan 11. Transaksi dibatalkan.');
            }
        });
    }
}
