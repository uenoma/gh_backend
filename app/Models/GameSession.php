<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Models\GameSessionPlot;

class GameSession extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'description',
        'capacity',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function members()
    {
        return $this->belongsToMany(User::class, 'game_session_members')
            ->withPivot('joined_at', 'mobile_suit_id', 'pilot_point')
            ->orderBy('game_session_members.joined_at');
    }

    public function plots()
    {
        return $this->hasMany(GameSessionPlot::class);
    }
}
