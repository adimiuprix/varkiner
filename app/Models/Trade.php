<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Trade extends Model
{
    protected $fillable = [
        'symbol',
        'type',
        'price',
        'status',
        'txid',
    ];
}
