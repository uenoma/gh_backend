<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatChannel extends Model
{
    protected $fillable = [
        'name',
        'description',
        'user_id',
        'is_system',
    ];

    protected $casts = [
        'is_system' => 'boolean',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function members()
    {
        return $this->belongsToMany(User::class, 'chat_channel_members')
            ->withPivot('joined_at', 'last_read_at')
            ->orderBy('chat_channel_members.joined_at');
    }

    public function messages()
    {
        return $this->hasMany(ChatMessage::class);
    }
}
