<?php

namespace App\Http\Controllers;

use App\Models\Trade;
use App\Models\TradeHistory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class TradeOrderController extends Controller
{
    /**
     * Open a new trade order based on the incoming signal.
     *
     * If the signal type matches the last history entry for this symbol,
     * no action is taken (duplicate signal). If the type differs, any
     * open trades for the symbol are closed first, then a new trade is opened.
     */
    public function openTradeOrder(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'symbol' => ['required', 'string', 'max:20'],
            'type' => ['required', 'string', Rule::in(['BULLISH OB', 'BEARISH OB'])],
            'current_price' => ['required', 'numeric', 'gt:0'],
            'zone.bottom' => ['required', 'numeric', 'gt:0'],
            'zone.top' => ['required', 'numeric', 'gt:0'],
        ]);

        $symbol = $validated['symbol'];
        $type = $validated['type'];
        $currentPrice = $validated['current_price'];

        // Check last history entry for this symbol
        $lastHistory = TradeHistory::forSymbol($symbol)->latest()->first();

        // Duplicate signal -- nothing to do
        if ($lastHistory && $lastHistory->type === $type) {
            return response()->json([
                'message' => 'Trade type is same as last history, nothing executed',
            ]);
        }

        return DB::transaction(function () use ($validated, $symbol, $type, $currentPrice) {
            // Close any currently open trades for this symbol
            $closedCount = Trade::forSymbol($symbol)
                ->open()
                ->update([
                    'status' => 'closed',
                    'close_price' => $currentPrice,
                ]);

            // Generate a unique transaction id
            $txid = Str::uuid()->toString();

            // Open a new trade
            $trade = Trade::create([
                'symbol' => $symbol,
                'type' => $type,
                'price' => $currentPrice,
                'status' => 'open',
                'txid' => $txid,
            ]);

            // Record history
            TradeHistory::create([
                'symbol' => $symbol,
                'type' => $type,
                'current_price' => $currentPrice,
                'zone_bottom' => $validated['zone']['bottom'],
                'zone_top' => $validated['zone']['top'],
                'order_id' => $txid,
            ]);

            return response()->json([
                'message' => 'Trade updated and new order opened successfully',
                'data' => [
                    'trade_id' => $trade->id,
                    'txid' => $txid,
                    'closed_trades' => $closedCount,
                ],
            ], 201);
        });
    }
}
