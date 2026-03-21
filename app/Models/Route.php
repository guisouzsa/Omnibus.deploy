<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Route extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'start_point',
        'start_point_cep',
        'start_point_reference',
        'start_point_lat',
        'start_point_lng',
        'end_point',
        'end_point_lat',
        'end_point_lng',
        'departure_time',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
