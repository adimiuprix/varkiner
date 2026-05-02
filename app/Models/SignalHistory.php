<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $symbol
 * @property string $type
 * @property string $current_price
 * @property string $zone_bottom
 * @property string $zone_top
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class SignalHistory extends Model
{
    protected $fillable = [
        'symbol',
        'type',
        'current_price',
        'zone_bottom',
        'zone_top',
    ];
}
