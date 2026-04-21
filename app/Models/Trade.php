<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Trade extends Model
{
    protected $fillable = [
        'symbol',
        'type',
        'price',
        'close_price',
        'status',
        'txid',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:8',
            'close_price' => 'decimal:8',
        ];
    }

    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    public function scopeForSymbol($query, string $symbol)
    {
        return $query->where('symbol', $symbol);
    }
}
