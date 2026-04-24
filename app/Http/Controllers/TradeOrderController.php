<?php

namespace App\Http\Controllers;

use App\Models\Trade;
use App\Models\TradeHistory;
use App\Services\BitgetService;
use Illuminate\Http\Request;

class TradeOrderController extends Controller
{
    public function openTradeOrder(Request $request, BitgetService $service)
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
                'message' => 'Trade type is same as last history, nothing executed',
            ]);
        }

        // ubah signal type menjadi buy atau sell
        $signalType = match ($request->type) {
            'OB BULLISH' => 'buy',
            'OB BEARISH' => 'sell',
            default => null
        };

        // Jika tipe berbeda dari history terakhir, tutup trade yang sedang open
        if ($lastHistory && $lastHistory->type !== $request->type) {
            Trade::where('symbol', $request->symbol)
                ->where('status', 'open')
                ->update(['status' => 'closed']);
        }

        // Buka trade baru
        $tradeExecute = $this->futuresOrder($service, $request->symbol, $signalType);

        Trade::create([
            'symbol' => $request->symbol,
            'type' => $request->type,
            'price' => $request->current_price,
            'status' => 'open',
            'txid' => $tradeExecute['data']['successList']['clientOid'],
        ]);

        // Simpan history baru
        TradeHistory::create([
            'symbol' => $request->symbol,
            'type' => $request->type,
            'current_price' => $request->current_price,
            'zone_bottom' => $request->zone['bottom'],
            'zone_top' => $request->zone['top'],
            'order_id' => $tradeExecute,
        ]);

        return response()->json([
            'message' => 'Trade updated and new order opened successfully',
        ], 201);
    }

    public function futuresOrder(BitgetService $service, $symbol, $signalType)
    {
        $leverage = '15';
        $margin = '1';
        $service->setLeverage([
            'symbol' => $symbol,
            'productType' => 'USDT-FUTURES',
            'leverage' => $leverage,
            'marginCoin' => 'USDT',
        ]);
        $price = (float)$service->getTickerFutures('RAVEUSDT')['data'][0]['markPrice'] ?? 0;

        $size = number_format(($margin * $leverage) / $price, 4, '.', '');

        $tradeExecute = $service->createFuturesOrder([
            'symbol' => $symbol,
            'productType' => 'USDT-FUTURES',
            'marginMode' => 'isolated',
            'side' => $signalType,
            'orderType' => 'market',
            'size' => $size,
            'marginCoin' => 'USDT',
        ]);

        return response()->json($tradeExecute);
    }

    public function stopOrder(BitgetService $service, $symbol = 'RAVEUSDT')
    {
        if (request()->isMethod('post')) {
            $tradeExecute = $service->flashCloseOrder($symbol, 'USDT-FUTURES');
            return response()->json([
                'tradeExecute' => $tradeExecute,
            ]);
        } else {
            return view('home');
        }
    }
}
