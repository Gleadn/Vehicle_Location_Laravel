<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Location extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'user_id',
        'vehicle_id',
        'start_date',
        'end_date',
        'start_datetime',
        'end_datetime',
        'status',
        'total_price',
        'notes',
        'cancelled_at',
        'cancelled_reason',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'start_datetime' => 'datetime',
        'end_datetime' => 'datetime',
        'total_price' => 'decimal:2',
        'cancelled_at' => 'datetime',
    ];

    /**
     * Get the user who made this location.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the vehicle for this location.
     */
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * Scope to get active locations.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to get confirmed locations.
     */
    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    /**
     * Scope to get completed locations.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Check if location is currently active.
     */
    public function isActive()
    {
        return $this->status === 'active' && 
               now()->between($this->start_datetime ?? $this->start_date, $this->end_datetime ?? $this->end_date);
    }
}
