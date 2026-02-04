<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LocationProposal extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'location_demand_id',
        'vehicle_id',
        'proposed_price',
        'rank',
        'selected',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'proposed_price' => 'decimal:2',
        'selected' => 'boolean',
    ];

    /**
     * Get the demand for this proposal.
     */
    public function locationDemand()
    {
        return $this->belongsTo(LocationDemand::class);
    }

    /**
     * Get the vehicle for this proposal.
     */
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * Scope to get selected proposals.
     */
    public function scopeSelected($query)
    {
        return $query->where('selected', true);
    }

    /**
     * Scope to order by rank.
     */
    public function scopeOrderedByRank($query)
    {
        return $query->orderBy('rank', 'asc');
    }
}
