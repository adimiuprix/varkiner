<?php

namespace App\Http\Controllers;

use App\Models\TradeHistory;
use App\Models\Trade;
use Illuminate\Http\Request;

class TradeOrderController extends Controller
{
    public function openTradeOrder(Request $request)
    {
        // Validasi input
        $request->validate([
            'symbol' => 'required',
            'type' => 'required',
            'current_price' => 'required',
            'zone.bottom' => 'required',
            'zone.top' => 'required',
        ]);

        // Ambil history terakhir untuk symbol ini
        $lastHistory = TradeHistory::where('symbol', $request->symbol)->latest()->first();

        // Jika tipe sama dengan history terakhir, jangan eksekusi apa pun
        if ($lastHistory && $lastHistory->type === $request->type) {
            return response()->json([
                'message' => 'Trade type is same as last history, nothing executed'
            ]);
        }

        // Jika tipe berbeda dari history terakhir, tutup trade yang sedang open
        if ($lastHistory && $lastHistory->type !== $request->type) {
            Trade::where('symbol', $request->symbol)
                ->where('status', 'open')
                ->update(['status' => 'closed']);
        }
        
        // Buka trade baru
        $tradeExecute = "potjkeotkoetjkoteotgeototmkj";

        Trade::create([
            'symbol' => $request->symbol,
            'type'   => $request->type,
            'price'  => $request->current_price,
            'status' => 'open',
            'txid'   => $tradeExecute,
        ]);

        // Simpan history baru
        TradeHistory::create([
            'symbol'        => $request->symbol,
            'type'          => $request->type,
            'current_price' => $request->current_price,
            'zone_bottom'   => $request->zone['bottom'],
            'zone_top'      => $request->zone['top'],
            'order_id'      => $tradeExecute,
        ]);

        return response()->json([
            'message' => 'Trade updated and new order opened successfully',
        ], 201);
    }
}
