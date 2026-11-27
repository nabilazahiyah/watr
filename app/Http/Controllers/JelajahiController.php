<?php

namespace App\Http\Controllers;

use App\Services\DepotFrontendData;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class JelajahiController extends Controller
{
    private ?array $catalog = null;

    public function __construct(private DepotFrontendData $depotFrontendData)
    {
    }

    /** Halaman daftar semua depot (tab "Jelajahi"). */
    public function index(Request $request)
    {
        $depots = collect($this->depots());

        $q = Str::lower(trim((string) $request->query('q')));
        if ($q !== '') {
            $depots = $depots->filter(fn ($d) =>
                Str::contains(Str::lower((string) ($d['nama'] ?? '')), $q) ||
                Str::contains(Str::lower((string) ($d['kelurahan'] ?? '')), $q) ||
                Str::contains(Str::lower((string) ($d['alamat'] ?? '')), $q)
            );
        }

        if ($kel = $request->query('kelurahan')) {
            $depots = $depots->where('kelurahan', $kel);
        }

        if ($request->boolean('harian')) {
            $depots = $depots->where('buka_setiap_hari', true);
        }

        if ($request->boolean('antar')) {
            $depots = $depots->where('antar', true);
        }

        $depots = match ($request->query('urut', 'nama_asc')) {
            'nama_desc' => $depots->sortByDesc('nama', SORT_NATURAL | SORT_FLAG_CASE),
            'harga_asc' => $depots->sortBy('harga_min'),
            default     => $depots->sortBy('nama', SORT_NATURAL | SORT_FLAG_CASE),
        };

        return view('jelajahi', [
            'depots'    => $depots->values()->all(),
            'kelurahan' => $this->kelurahan(),
        ]);
    }

    /** Halaman peta interaktif (dibuka dari tombol "Lihat di peta"). */
    public function peta()
    {
        return view('peta', [
            'depots'    => $this->depots(),
            'kelurahan' => $this->kelurahan(),
        ]);
    }

    /** Data dari service backend, dipanggil sekali per request. */
    private function catalog(): array
    {
        return $this->catalog ??= $this->depotFrontendData->catalog();
    }

    private function kelurahan(): array
    {
        return $this->catalog()['kelurahan']->all();
    }

    /**
     * Ubah data dari service ke format yang dipakai frontend.
     */
    private function depots(): array
    {
        return $this->catalog()['depots']->map(function (array $d) {
            $harga = $d['harga'] ?? null;
            $foto = Str::slug($d['nama']) . '.png';
            $fotoUrl = file_exists(public_path('images/depot/' . $foto))
                ? asset('images/depot/' . $foto)
                : asset('images/fotodummy.jpg');

            $telp = $d['telepon'] ?? null;

            return [
                'id'        => $d['id'],
                'nama'      => $d['nama'],
                'kelurahan' => $d['kelurahan'],
                'alamat'    => $d['alamat'] ?? null,
                'lat'       => (float) $d['lat'],
                'lng'       => (float) $d['lng'],

                // Database hanya punya satu harga, jadi min = max
                'harga_min' => $harga,
                'harga_max' => $harga,

                'antar'     => (bool) $d['antar'],
                'buka_setiap_hari' => (bool) $d['buka_setiap_hari'],
                'jam_buka'  => $d['jam_buka'],
                'jam_tutup' => $d['jam_tutup'],
                'hari_libur' => $d['hari_libur'] ?? [],

                'foto'      => $fotoUrl,
                'telp'      => $telp,
                'wa'        => $telp ? '62' . ltrim(preg_replace('/\D/', '', $telp), '0') : null,
                'maps'      => $d['link_google_maps'] ?? null,
            ];
        })->values()->all();
    }
}
