<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TradePair;
use App\Services\Pm2Service;

class PairController extends Controller
{
    public function index()
    {
        $pair = TradePair::where('id', 1)->first();
        return view('home', compact('pair'));
    }

    public function editPair(Request $request, Pm2Service $pm2)
    {
        $pair = $request->input('pair');
        $side = $request->input('side');

        $trade = TradePair::where('id', 1)->update([
            'pair' => strtoupper($pair),
            'side' => $side
        ]);

        $pm2->restart('bot');

        return redirect()->back();
    }
}
