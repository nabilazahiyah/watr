<?php

namespace App\Http\Controllers;

use App\Services\DepotFrontendData;

class JelajahiController extends Controller
{
    public function index(DepotFrontendData $depotFrontendData)
    {
        $catalog = $depotFrontendData->catalog();
        $hargaTertinggi = (int) ($catalog['depots']->max('harga') ?? 0);
        $catalog['maxHarga'] = max(15000, (int) (ceil($hargaTertinggi / 500) * 500));

        return view('jelajahi', $catalog);
    }
}
