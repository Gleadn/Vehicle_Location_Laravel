<?php

namespace App\Services;

use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Collection;

class VehicleService
{
    /**
     * Get available vehicles.
     */
    public function getAvailableVehicles(int $limit = null): Collection
    {
        $query = Vehicle::available();
        
        if ($limit) {
            $query->limit($limit);
        }
        
        return $query->get();
    }

    /**
     * Get cheapest available vehicles.
     */
    public function getCheapestVehicles(int $limit = 8): Collection
    {
        return Vehicle::available()
            ->orderBy('daily_rate', 'asc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get vehicles by type.
     */
    public function getVehiclesByType(string $type, int $limit = null): Collection
    {
        $query = Vehicle::available()->ofType($type);
        
        if ($limit) {
            $query->limit($limit);
        }
        
        return $query->get();
    }

    /**
     * Search vehicles based on criteria.
     */
    public function searchVehicles(array $criteria): Collection
    {
        $query = Vehicle::available();

        // Filter by type
        if (isset($criteria['type'])) {
            $query->where('type', $criteria['type']);
        }

        // Filter by minimum seats
        if (isset($criteria['seats'])) {
            $query->where('seats', '>=', $criteria['seats']);
        }

        // Filter by fuel type
        if (isset($criteria['fuel_type'])) {
            $query->where('fuel_type', $criteria['fuel_type']);
        }

        // Filter by maximum daily rate
        if (isset($criteria['max_budget'])) {
            $query->where('daily_rate', '<=', $criteria['max_budget']);
        }

        // Order by price
        $query->orderBy('daily_rate', 'asc');

        return $query->get();
    }

    /**
     * Check if vehicle is available for a date range.
     */
    public function isAvailableForDates(Vehicle $vehicle, string $startDate, string $endDate): bool
    {
        // Check if vehicle is available and active
        if (!$vehicle->available || $vehicle->status !== 'active') {
            return false;
        }

        // Check if there are any overlapping locations
        $overlappingLocations = $vehicle->locations()
            ->whereIn('status', ['confirmed', 'active'])
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate, $endDate])
                    ->orWhereBetween('end_date', [$startDate, $endDate])
                    ->orWhere(function ($q) use ($startDate, $endDate) {
                        $q->where('start_date', '<=', $startDate)
                          ->where('end_date', '>=', $endDate);
                    });
            })
            ->exists();

        return !$overlappingLocations;
    }

    /**
     * Calculate total price for a rental period.
     */
    public function calculateTotalPrice(Vehicle $vehicle, string $startDate, string $endDate): float
    {
        $start = new \DateTime($startDate);
        $end = new \DateTime($endDate);
        $days = $start->diff($end)->days + 1; // +1 to include both start and end day

        return round($vehicle->daily_rate * $days, 2);
    }

    /**
     * Get vehicle statistics.
     */
    public function getStatistics(): array
    {
        return [
            'total' => Vehicle::count(),
            'available' => Vehicle::available()->count(),
            'in_maintenance' => Vehicle::where('status', 'maintenance')->count(),
            'by_type' => [
                'car' => Vehicle::where('type', 'car')->count(),
                'motorcycle' => Vehicle::where('type', 'motorcycle')->count(),
                'van' => Vehicle::where('type', 'van')->count(),
                'sport' => Vehicle::where('type', 'sport')->count(),
            ],
            'average_rate' => Vehicle::available()->avg('daily_rate'),
        ];
    }

    /**
     * Update vehicle mileage after rental.
     */
    public function updateMileage(Vehicle $vehicle, float $additionalMileage): bool
    {
        $vehicle->mileage += $additionalMileage;
        return $vehicle->save();
    }

    /**
     * Mark vehicle as unavailable.
     */
    public function markAsUnavailable(Vehicle $vehicle, string $reason = 'maintenance'): bool
    {
        $vehicle->available = false;
        $vehicle->status = $reason;
        return $vehicle->save();
    }

    /**
     * Mark vehicle as available.
     */
    public function markAsAvailable(Vehicle $vehicle): bool
    {
        $vehicle->available = true;
        $vehicle->status = 'active';
        return $vehicle->save();
    }
}
