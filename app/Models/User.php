<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get all location demands for this user.
     */
    public function locationDemands()
    {
        return $this->hasMany(LocationDemand::class);
    }

    /**
     * Get all locations (confirmed rentals) for this user.
     */
    public function locations()
    {
        return $this->hasMany(Location::class);
    }

    /**
     * Get active locations for this user.
     */
    public function activeLocations()
    {
        return $this->hasMany(Location::class)->where('status', 'active');
    }

    /**
     * Get pending location demands for this user.
     */
    public function pendingDemands()
    {
        return $this->hasMany(LocationDemand::class)->where('status', 'pending');
    }

    /**
     * Check if the user has admin role.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if the user has regular user role.
     */
    public function isUser(): bool
    {
        return $this->role === 'user';
    }
}
