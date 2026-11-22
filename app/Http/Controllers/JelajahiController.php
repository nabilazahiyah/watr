<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class JelajahiController extends Controller
{
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

    public function peta()
    {
        return view('peta', [
            'depots'    => $this->depots(),
            'kelurahan' => $this->kelurahan(),
        ]);
    }

    private function kelurahan(): array
    {
        return ['Babakan', 'Babakan Pasar', 'Cibogor', 'Ciwaringin', 'Gudang', 'Kebon Kalapa',
                'Pabaton', 'Panaragan', 'Paledang', 'Sempur', 'Tegallega'];
    }

    private function depots(): array
    {
        $depots = [
            ['id' => 1, 'nama' => 'Jaya Tirta Alam', 'kelurahan' => 'Babakan', 'lat' => -6.5890, 'lng' => 106.7935,
             'harga_min' => 12000, 'harga_max' => 18000, 'rating' => 4.9, 'ulasan' => 59,
             'antar' => true, 'buka24' => true, 'foto' => 'jaya-tirta-alam.png',
             'alamat' => 'Jln veteran gang kepatihan 06 RT03 RW01 Bogor, Gang kepithan, RT.01/RW.01, Kb. Klp., Kecamatan Bogor Tengah, Kota Bogor, Jawa Barat 16125',
             'telp' => '0815-1313-7534', 'wa' => '6281513137534'],

            ['id' => 2, 'nama' => 'SHiST Water Station', 'kelurahan' => 'Babakan Pasar', 'lat' => -6.6035, 'lng' => 106.8000,
             'harga_min' => 12000, 'harga_max' => 18000, 'rating' => 5.0, 'ulasan' => 1,
             'antar' => true, 'buka24' => false, 'foto' => 'shist-water-station.png',
             'alamat' => 'Jl. Kb. Kopi No.3, RT.03/RW.09, Kb. Klp., Kecamatan Bogor Tengah, Kota Bogor, Jawa Barat 16125',
             'telp' => '0857-7374-3400', 'jam' => '08.30 - 17.00'],

            ['id' => 3, 'nama' => "TIN'S TIRTA", 'kelurahan' => 'Tegallega', 'lat' => -6.6075, 'lng' => 106.8050,
             'harga_min' => 12000, 'harga_max' => 18000, 'rating' => 5.0, 'ulasan' => 24,
             'antar' => false, 'buka24' => false, 'foto' => 'tins-tirta.png',
             'alamat' => 'Jl. Pendidikan No.02, RT.03/RW.01, Babakan, Kecamatan Bogor Tengah, Kota Bogor, Jawa Barat 16128',
             'telp' => '0813-1778-0207', 'jam' => '08.00 - 20.30'],
        ];

        // Lengkapi field yang belum diisi: nomor WhatsApp dibuat dari telp (0xxx -> 62xxx)
        return array_map(function ($d) {
            $d['jam'] = $d['jam'] ?? null;
            $d['wa']  = $d['wa'] ?? (! empty($d['telp'])
                ? '62' . ltrim(preg_replace('/\D/', '', $d['telp']), '0')
                : null);

            return $d;
        }, $depots);
    }
}