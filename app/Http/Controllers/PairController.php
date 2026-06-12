<?php

namespace App\Http\Controllers;

use App\Http\Requests\EditPairRequest;
use App\Models\TradePair;
use App\Models\User;
use App\Services\Pm2Service;

class PairController extends Controller
{
    public function index()
    {
        $pair = TradePair::where('id', 1)->first();
        $user = User::first();
        return view('home', compact('pair', 'user'));
    }

    public function editPair(EditPairRequest $request, Pm2Service $pm2)
    {
        TradePair::where('id', 1)->update([
            'pair' => strtoupper($request->validated('pair')),
            'side' => $request->validated('side'),
        ]);

        User::updateOrCreate(
            ['id' => 1],
            [
                'lavarage' => $request->validated('lavarage'),
                'margin'   => $request->validated('margin'),
            ]
        );

        $pm2->restart('bot');

        return redirect()->back();
    }
}
