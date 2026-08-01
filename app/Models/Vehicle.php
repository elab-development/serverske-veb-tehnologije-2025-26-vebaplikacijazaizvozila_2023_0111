<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'brand',
        'model',
        'registration_number',
        'production_year',
        'daily_price',
        'transmission',
        'fuel_type',
        'seats',
        'status',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'production_year' => 'integer',
            'daily_price' => 'decimal:2',
            'seats' => 'integer',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(VehicleImage::class);
    }
}
