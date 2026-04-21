<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TradeHistory extends Model
{
    protected $fillable = [
        'symbol',
        'type',
        'current_price',
        'zone_bottom',
        'zone_top',
        'order_id',
    ];

    protected function casts(): array
    {
        return [
            'current_price' => 'decimal:8',
            'zone_bottom' => 'decimal:8',
            'zone_top' => 'decimal:8',
        ];
    }

    public function scopeForSymbol($query, string $symbol)
    {
        return $query->where('symbol', $symbol);
    }
}
