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

        if ($q = trim((string) $request->query('q'))) {
            $depots = $depots->filter(fn ($d) =>
                str_contains(strtolower($d['nama']), strtolower($q)) ||
                str_contains(strtolower($d['kelurahan']), strtolower($q))
            );
        }

        if ($kel = $request->query('kelurahan')) {
            $depots = $depots->where('kelurahan', $kel);
        }

        $depots = match ($request->query('filter')) {
            'buka24' => $depots->where('buka24', true),
            'antar'  => $depots->where('antar', true),
            default  => $depots,
        };

        $depots = match ($request->query('urut', 'nama_asc')) {
            'nama_desc' => $depots->sortByDesc('nama', SORT_NATURAL | SORT_FLAG_CASE),
            'rating'    => $depots->sortByDesc('rating'),
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
        // SEMENTARA: rating & ulasan belum ada di database.
        // Hapus array ini kalau backend sudah menambah kolomnya.
        $sementara = [
            1 => ['rating' => 4.9, 'ulasan' => 59],
            2 => ['rating' => 5.0, 'ulasan' => 1],
            3 => ['rating' => 5.0, 'ulasan' => 24],
        ];

        return $this->catalog()['depots']->map(function (array $d) use ($sementara) {
            $jamBuka  = $d['jam_buka'] ?? null;
            $jamTutup = $d['jam_tutup'] ?? null;

            // Dianggap 24 jam kalau buka tiap hari, 00:00 sampai tengah malam
            $buka24 = ! empty($d['buka_setiap_hari'])
                && $jamBuka === '00:00'
                && in_array($jamTutup, ['00:00', '23:59', '24:00'], true);

            $harga = $d['harga'] ?? null;

            // Foto dari slug nama (jaya-tirta-alam.png), kalau tidak ada pakai placeholder
            $foto = Str::slug($d['nama']) . '.png';
            if (! file_exists(public_path('images/depot/' . $foto))) {
                $foto = 'placeholder.png';
            }

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

                'rating'    => $sementara[$d['id']]['rating'] ?? 0,
                'ulasan'    => $sementara[$d['id']]['ulasan'] ?? 0,

                'antar'     => (bool) $d['antar'],
                'buka24'    => $buka24,
                'jam'       => (! $buka24 && $jamBuka && $jamTutup)
                    ? str_replace(':', '.', $jamBuka) . ' - ' . str_replace(':', '.', $jamTutup)
                    : null,
                'hari_libur' => $d['hari_libur'] ?? [],

                'foto'      => $foto,
                'telp'      => $telp,
                'wa'        => $telp ? '62' . ltrim(preg_replace('/\D/', '', $telp), '0') : null,
                'maps'      => $d['link_google_maps'] ?? null,
            ];
        })->values()->all();
    }
}