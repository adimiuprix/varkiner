<?php

namespace App\Http\Controllers;

use App\Models\Trade;
use App\Models\SignalHistory;
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

        // Ambil history terakhir untuk symbol ini
        $lastHistory = SignalHistory::query()
            ->where('symbol', $request->input('symbol'))
            ->latest()
            ->first();

        // Jika tipe sama dengan history terakhir, skip
        if ($lastHistory && $lastHistory->type === $request->input('type')) {
            return response()->json([
                'message' => 'Trade type is same as last history, nothing executed',
            ]);
        }

        // Mapping signal
        $signalType = match ($request->input('type')) {
            'BULLISH OB' => 'buy',
            'BEARISH OB' => 'sell',
            default => null
        };

        $tradeExecute = null;

        /**
         * =========================
         * 🔥 EXTERNAL API (OUTSIDE TRANSACTION)
         * =========================
         */

        // Close order lama kalau beda type
        if ($lastHistory && $lastHistory->type !== $request->input('type')) {
            $this->stopOrder($service, $request->input('symbol'));
        }

        // Open order baru
        if ($signalType === 'buy') {
            $tradeExecute = $this->futuresOrder($service, $request->input('symbol'), $signalType);
        }

        /**
         * =========================
         * 💾 DATABASE TRANSACTION
         * =========================
         */
        DB::transaction(function () use ($lastHistory, $request, $tradeExecute, $signalType) {

            // Update trade lama jadi closed
            if ($lastHistory && $lastHistory->type !== $request->input('type')) {
                Trade::query()
                    ->where('symbol', $request->input('symbol'))
                    ->where('status', 'open')
                    ->update(['status' => 'closed']);
            }

            // Insert trade baru HANYA jika buy (ada posisi dibuka di Bitget)
            if ($signalType === 'buy') {
                Trade::create([
                    'symbol' => $request->input('symbol'),
                    'type' => $request->input('type'),
                    'price' => $request->input('current_price'),
                    'status' => 'open',
                    'txid' => $tradeExecute['data']['orderId'] ?? null,
                ]);
            }

            // History selalu dicatat (diperlukan untuk deduplikasi sinyal)
            SignalHistory::create([
                'symbol' => $request->input('symbol'),
                'type' => $request->input('type'),
                'current_price' => $request->input('current_price'),
                'zone_bottom' => $request->input('zone.bottom'),
                'zone_top' => $request->input('zone.top'),
                'order_id' => $tradeExecute['data']['orderId'] ?? null,
            ]);

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