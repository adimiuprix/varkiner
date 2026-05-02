<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $symbol
 * @property string $type
 * @property string $price
 * @property string|null $zone_bottom
 * @property string|null $zone_top
 * @property string $status
 * @property string|null $txid
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class Trade extends Model
{
    protected $fillable = [
        'symbol',
        'type',
        'price',
        'zone_bottom',
        'zone_top',
        'status',
        'txid',
    ];
}
