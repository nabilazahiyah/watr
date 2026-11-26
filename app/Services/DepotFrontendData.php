<?php

namespace App\Services;

use App\Models\Depot;
use App\Models\Kelurahan;
use Illuminate\Support\Collection;

class DepotFrontendData
{
    /**
     * Return one consistent payload for the home and explore pages.
     *
     * @return array{depots: Collection<int, array<string, mixed>>, kelurahan: Collection<int, string>}
     */
    public function catalog(): array
    {
        $depots = Depot::query()
            ->with([
                'kelurahan:id,nama_kelurahan',
                'hariLibur:id,depot_id,hari',
            ])
            ->orderBy('id')
            ->get()
            ->map(fn (Depot $depot) => [
                'id' => $depot->id,
                'nama' => $depot->nama_depot,
                'kelurahan' => $depot->kelurahan->nama_kelurahan,
                'alamat' => $depot->alamat,
                'link_google_maps' => $depot->link_google_maps,
                'lat' => $depot->latitude,
                'lng' => $depot->longitude,
                'harga' => $depot->harga_per_galon,
                'jam_buka' => $this->formatTime($depot->jam_buka),
                'jam_tutup' => $this->formatTime($depot->jam_tutup),
                'buka_setiap_hari' => $depot->buka_setiap_hari,
                'antar' => $depot->is_delivery,
                'telepon' => $depot->telepon,
                'hari_libur' => $depot->hariLibur->pluck('hari')->values()->all(),
            ]);

        $kelurahan = Kelurahan::query()
            ->orderBy('nama_kelurahan')
            ->pluck('nama_kelurahan');

        return compact('depots', 'kelurahan');
    }

    private function formatTime(?string $time): ?string
    {
        return $time === null ? null : substr($time, 0, 5);
    }
}
