<?php

namespace App\Http\Controllers;

use App\Http\Requests\EditPairRequest;
use App\Models\TradePair;
use App\Services\Pm2Service;

class PairController extends Controller
{
    public function index()
    {
        $pair = TradePair::where('id', 1)->first();
        return view('home', compact('pair'));
    }

    public function editPair(EditPairRequest $request, Pm2Service $pm2)
    {
        TradePair::updateOrCreate(
            ['id' => 1],
            [
                'pair'     => strtoupper($request->validated('pair')),
                'side'     => $request->validated('side'),
                'lavarage' => $request->validated('lavarage'),
                'margin'   => $request->validated('margin'),
            ]
        );

        $pm2->restart('bot');

        return redirect()->back()->with('success', 'Configuration saved and bot restarted.');
    }
}
