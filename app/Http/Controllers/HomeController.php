<?php

namespace App\Http\Controllers;

use App\Services\DepotFrontendData;

class HomeController extends Controller
{
    public function index(DepotFrontendData $depotFrontendData)
    {
        $catalog = $depotFrontendData->catalog();
        $depots = $catalog['depots'];
        $recommendedDepots = $depots->take(3);
        $statistics = [
            [$catalog['kelurahan']->count(), 'Kelurahan Terdata'],
            [$depots->count(), 'Depot Terdata'],
            [$depots->where('antar', true)->count(), 'Depot Menyediakan Layanan Antar'],
            [$depots->where('buka_setiap_hari', true)->count(), 'Depot Buka Setiap Hari'],
        ];

        return view('home', compact('recommendedDepots', 'statistics'));
    }
}
