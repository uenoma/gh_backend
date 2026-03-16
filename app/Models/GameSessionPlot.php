<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GameSessionPlot extends Model
{
    protected $fillable = [
        'game_session_id',
        'user_id',
        'inning',
        'plot',
        'damage',
    ];

    protected $casts = [
        'plot'   => 'array',
        'damage' => 'array',
    ];
}
