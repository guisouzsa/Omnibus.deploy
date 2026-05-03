<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Expenses extends Model
{
    protected $fillable = [
        'driver_id',
        'vehicle_plate',
        'value',
        'proof_of_payment',
        'description',
    ];

    /**
     * Relacionamento com Driver
     */
    public function driver(): BelongsTo
    {
        return $this->belongsTo(Drivers::class, 'driver_id');
    }

    public function getProofOfPaymentAttribute($value): ?string
    {
        if (empty($value)) {
            return null;
        }

        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://') || str_starts_with($value, 'data:')) {
            return $value;
        }

        if (str_starts_with($value, '/')) {
            return url($value);
        }

        if (str_starts_with($value, 'storage/')) {
            return url('/' . $value);
        }

        return $value;
    }
}
