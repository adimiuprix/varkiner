<?php

namespace App\Http\Controllers;

use App\Models\Trade;
use App\Services\BitgetService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

        $signalType = match ($request->input('type')) {
            'BULLISH OB' => 'buy',
            'BEARISH OB' => 'sell',
            default => null
        };

        if (!$signalType) {
            return response()->json(['message' => 'Invalid trade type'], 400);
        }

        // Ambil trade terakhir untuk symbol ini
        $lastTrade = Trade::query()
            ->where('symbol', $request->input('symbol'))
            ->latest()
            ->first();

        // Cek apakah ada posisi yang masih open
        $isOpen = $lastTrade && $lastTrade->status === 'open';

        // =========================
        // 🛡️ LOGIKA DEDUPLIKASI
        // =========================
        if ($signalType === 'buy' && $isOpen) {
            return response()->json([
                'message' => 'Long position is already open, nothing executed',
            ]);
        }

        if ($signalType === 'sell' && !$isOpen) {
            return response()->json([
                'message' => 'No open position to close, nothing executed',
            ]);
        }

        $tradeExecute = null;

        /**
         * =========================
         * 🔥 EXTERNAL API (OUTSIDE TRANSACTION)
         * =========================
         */

        // Close order lama kalau ada yang open dan sinyal sell
        if ($signalType === 'sell' && $isOpen) {
            $this->stopOrder($service, $request->input('symbol'));
        }

        // Open order baru kalau buy
        if ($signalType === 'buy') {
            $tradeExecute = $this->futuresOrder($service, $request->input('symbol'), $signalType);
        }

        /**
         * =========================
         * 💾 DATABASE TRANSACTION
         * =========================
         */
        DB::transaction(function () use ($lastTrade, $request, $tradeExecute, $signalType, $isOpen) {

            // Jika BEARISH (sell) dan ada posisi open, HANYA update status jadi closed
            if ($signalType === 'sell' && $isOpen) {
                $lastTrade->update(['status' => 'closed']);
            }

            // Jika BULLISH (buy), insert record BARU
            if ($signalType === 'buy') {
                Trade::create([
                    'symbol' => $request->input('symbol'),
                    'type' => $request->input('type'),
                    'price' => $request->input('current_price'),
                    'zone_bottom' => $request->input('zone.bottom'),
                    'zone_top' => $request->input('zone.top'),
                    'status' => 'open',
                    'txid' => $tradeExecute['data']['orderId'] ?? null,
                ]);
            }

        });

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

        $price = (float)($service->getTickerFutures($symbol)['data'][0]['markPrice'] ?? 0);

        $size = number_format(($margin * $leverage) / $price, 4, '.', '');

        return $service->createFuturesOrder([
            'symbol' => $symbol,
            'productType' => 'USDT-FUTURES',
            'marginMode' => 'crossed',
            'side' => $signalType,
            'orderType' => 'market',
            'size' => $size,
            'marginCoin' => 'USDT',
        ]);
    }

    public function stopOrder(BitgetService $service, $symbol)
    {
        return $service->flashCloseOrder($symbol, 'USDT-FUTURES');
    }
}