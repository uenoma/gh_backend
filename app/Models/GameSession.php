<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
            ->withPivot('joined_at')
            ->orderBy('game_session_members.joined_at');
    }
}
