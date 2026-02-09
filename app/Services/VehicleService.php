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
     * Get best matching vehicles based on weighted criteria.
     */
    public function getBestMatches(array $criteria, int $limit = 3, array $excludeIds = []): Collection
    {
        $query = Vehicle::available();

        if (!empty($excludeIds)) {
            $query->whereNotIn('id', $excludeIds);
        }

        $types = $criteria['types'] ?? null;
        $category = $criteria['vehicle_category'] ?? null;

        if (!$types && $category) {
            $types = $category === 'motorcycle'
                ? ['motorcycle']
                : ['car', 'van', 'sport'];
        }

        if (!empty($types)) {
            $query->whereIn('type', $types);
        }

        if (isset($criteria['max_budget'])) {
            $query->where('daily_rate', '<=', $criteria['max_budget']);
        }

        $vehicles = $query->get();

        if ($vehicles->isEmpty()) {
            return new Collection();
        }

        $weights = [
            'type' => 3,
            'seats' => 2,
            'fuel' => 2,
            'price' => 1,
        ];

        if (!empty($criteria['weights']) && is_array($criteria['weights'])) {
            $weights = array_merge($weights, $criteria['weights']);
        }

        if (!empty($criteria['priority_criteria']) && is_array($criteria['priority_criteria'])) {
            foreach ($criteria['priority_criteria'] as $priority) {
                if (isset($weights[$priority])) {
                    $weights[$priority] += 1;
                }
            }
        }

        $minPrice = $vehicles->min('daily_rate');
        $maxPrice = $vehicles->max('daily_rate');

        $scored = $vehicles->map(function (Vehicle $vehicle) use ($criteria, $weights, $minPrice, $maxPrice) {
            $score = $this->calculateMatchScore($vehicle, $criteria, $weights, $minPrice, $maxPrice);
            return [
                'vehicle' => $vehicle,
                'score' => $score,
            ];
        });

        $sorted = $scored->sort(function ($a, $b) {
            if ($a['score'] === $b['score']) {
                return $a['vehicle']->daily_rate <=> $b['vehicle']->daily_rate;
            }

            return $b['score'] <=> $a['score'];
        })->values();

        $topVehicles = $sorted->take($limit)->pluck('vehicle')->all();
        
        return new Collection($topVehicles);
    }

    /**
     * Calculate the match score for a vehicle.
     */
    protected function calculateMatchScore(
        Vehicle $vehicle,
        array $criteria,
        array $weights,
        float $minPrice,
        float $maxPrice
    ): float {
        $score = 0.0;

        if (!empty($criteria['types'])) {
            if (in_array($vehicle->type, $criteria['types'], true)) {
                $score += $weights['type'];
            }
        }

        if (isset($criteria['seats_required'])) {
            $delta = abs($vehicle->seats - (int) $criteria['seats_required']);
            $maxDelta = 10;
            $seatScore = $weights['seats'] * (1 - min($delta / $maxDelta, 1));
            $score += $seatScore;
        }

        if (isset($criteria['fuel_type'])) {
            if ($vehicle->fuel_type === $criteria['fuel_type']) {
                $score += $weights['fuel'];
            }
        }

        if (isset($criteria['trip_type']) && $criteria['trip_type'] === 'road_trip') {
            if ($vehicle->fuel_type === 'electric') {
                $score -= $weights['fuel'] * 0.75;
            }
        }

        if ($maxPrice > $minPrice) {
            $normalizedPrice = 1 - (($vehicle->daily_rate - $minPrice) / ($maxPrice - $minPrice));
        } else {
            $normalizedPrice = 1;
        }

        $score += $weights['price'] * $normalizedPrice;

        return $score;
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
