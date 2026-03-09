<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bus extends Model
{
    protected $fillable = [
        'driver_id',
        'plate',
        'capacity',
        'mainRoute',
    ];

    /**
     * Relacionamento com Drivers
     */
    public function driver()
    {
        return $this->belongsTo(Drivers::class, 'driver_id');
    }
}
