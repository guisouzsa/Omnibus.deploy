<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    protected $fillable = [
        'driver_id',
        'plate',
        'capacity',
        'mainRoute',
    ];

    public function driver()
    {
        return $this->belongsTo(Drivers::class, 'driver_id');
    }
}