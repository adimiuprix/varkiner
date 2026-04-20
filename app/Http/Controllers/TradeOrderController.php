<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TradeOrderController extends Controller
{
    public function openTradeOrder(Request $request)
    {
        Log::info($request->all());

        return response()->json([
            'message' => 'Trade order opened successfully',
        ]);
    }
}
