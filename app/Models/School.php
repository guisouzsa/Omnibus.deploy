<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class School extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'cep',
        'address',
        'reference_point',
        'lat',
        'lng',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
