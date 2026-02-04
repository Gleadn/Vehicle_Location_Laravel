<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vehicle extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'brand',
        'model',
        'registration_number',
        'type',
        'fuel_type',
        'seats',
        'mileage',
        'daily_rate',
        'available',
        'status',
        'description',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'mileage' => 'decimal:2',
        'daily_rate' => 'decimal:2',
        'available' => 'boolean',
    ];

    /**
     * Get all locations for this vehicle.
     */
    public function locations()
    {
        return $this->hasMany(Location::class);
    }

    /**
     * Get all location proposals for this vehicle.
     */
    public function locationProposals()
    {
        return $this->hasMany(LocationProposal::class);
    }

    /**
     * Scope to get only available vehicles.
     */
    public function scopeAvailable($query)
    {
        return $query->where('available', true)
                     ->where('status', 'active');
    }

    /**
     * Scope to filter by vehicle type.
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }
}
