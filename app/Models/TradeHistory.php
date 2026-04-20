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
    ];
}
