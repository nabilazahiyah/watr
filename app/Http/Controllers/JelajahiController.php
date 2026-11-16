<?php

namespace App\Http\Controllers;

class JelajahiController extends Controller
{
    public function index()
    {
        $depots = [
            ['id' => 1, 'nama' => 'Jaya Tirta Alam',    'kelurahan' => 'Babakan',        'lat' => -6.5890, 'lng' => 106.7935, 'harga' => 5000, 'rating' => 4.9, 'ulasan' => 59, 'antar' => true,  'buka24' => false, 'foto' => 'jaya-tirta-alam.png'],
            ['id' => 2, 'nama' => 'SHiST Water Station', 'kelurahan' => 'Babakan Pasar',  'lat' => -6.6035, 'lng' => 106.8000, 'harga' => 6000, 'rating' => 5.0, 'ulasan' => 1,  'antar' => true,  'buka24' => true,  'foto' => 'shist-water-station.png'],
            ['id' => 3, 'nama' => "TIN'S TIRTA",        'kelurahan' => 'Tegallega',      'lat' => -6.6075, 'lng' => 106.8050, 'harga' => 7000, 'rating' => 5.0, 'ulasan' => 24, 'antar' => false, 'buka24' => false, 'foto' => 'tins-tirta.png'],
        ];

        $kelurahan = ['Babakan', 'Babakan Pasar', 'Cibogor', 'Ciwaringin', 'Gudang', 'Kebon Kalapa',
                      'Pabaton', 'Panaragan', 'Paledang', 'Sempur', 'Tegallega'];

        return view('jelajahi', compact('depots', 'kelurahan'));
    }
}