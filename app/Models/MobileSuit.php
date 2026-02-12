<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MobileSuit extends Model
{
    protected $fillable = [
        'data_id',
        'ms_number',
        'ms_name',
        'ms_name_optional',
        'ms_icon',
        'ms_data'
    ];

    protected $casts = [
        'ms_data' => 'array',
    ];

    public function creator()
    {
        return $this->hasOne(MobileSuitCreator::class);
    }
}
