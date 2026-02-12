<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MobileSuitCreator extends Model
{
    protected $fillable = [
        'mobile_suit_id',
        'creator_name',
        'edit_password',
    ];

    public function mobileSuit()
    {
        return $this->belongsTo(MobileSuit::class);
    }
}
