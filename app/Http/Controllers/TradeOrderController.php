<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TradeHistory;

class TradeOrderController extends Controller
{
    public function openTradeOrder(Request $request)
    {

        // Simpan signal trading dari API ke database
        TradeHistory::create([
            'symbol'        => $request->symbol,
            'type'          => $request->type,
            'current_price' => $request->current_price,
            'zone_bottom'   => $request->zone['bottom'],
            'zone_top'      => $request->zone['top'],
        ]);

        return response()->json([
            'message' => 'Trade order opened successfully',
        ]);
    }
}
