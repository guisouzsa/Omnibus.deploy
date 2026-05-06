<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Route extends Model
{
    protected $fillable = [
        'user_id',
        'driver_id',
        'school_id',
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
        'distance',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Drivers::class);
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function vehicle(): HasOne
    {
        return $this->hasOne(Vehicle::class, 'route_id');
    }

    /**
     * Relacionamento com Notifications
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class, 'route_id');
    }
}
