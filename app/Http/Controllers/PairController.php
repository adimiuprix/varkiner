<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TradePair;
use App\Services\Pm2Service;

class PairController extends Controller
{
    public function index()
    {
        return view('home');
    }

    public function editPair(Request $request, Pm2Service $pm2)
    {
        $pair = $request->input('pair');

        // jadikan uppercase
        $pair = strtoupper($pair);

        $trade = TradePair::where('id', 1)->update([
            'pair' => $pair
        ]);

        $pm2->restart('bot');

        return $trade;
    }
}
