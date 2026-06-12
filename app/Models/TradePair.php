<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['pair', 'side', 'lavarage', 'margin'])]
class TradePair extends Model
{
    //
}
