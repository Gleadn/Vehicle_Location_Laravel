<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LocationDemand extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'user_id',
        'demand_type',
        'vehicle_id',
        'vehicle_type',
        'start_date',
        'end_date',
        'seats_required',
        'max_budget',
        'status',
        'notes',
        'processed_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'max_budget' => 'decimal:2',
        'processed_at' => 'datetime',
    ];

    /**
     * Get the user who made this demand.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the requested vehicle (for specific demands).
     */
    public function requestedVehicle()
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id');
    }

    /**
     * Get all proposals for this demand.
     */
    public function proposals()
    {
        return $this->hasMany(LocationProposal::class);
    }

    /**
     * Get the selected proposal (if any).
     */
    public function selectedProposal()
    {
        return $this->hasOne(LocationProposal::class)->where('selected', true);
    }

    /**
     * Scope to get pending demands.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope to get generic demands.
     */
    public function scopeGeneric($query)
    {
        return $query->where('demand_type', 'generic');
    }

    /**
     * Scope to get specific demands.
     */
    public function scopeSpecific($query)
    {
        return $query->where('demand_type', 'specific');
    }

    /**
     * Check if this is a specific demand.
     */
    public function isSpecific(): bool
    {
        return $this->demand_type === 'specific';
    }

    /**
     * Check if this is a generic demand.
     */
    public function isGeneric(): bool
    {
        return $this->demand_type === 'generic';
    }

    /**
     * Scope to get demands that need processing.
     */
    public function scopeNeedsProcessing($query)
    {
        return $query->whereIn('status', ['pending', 'processing']);
    }
}
