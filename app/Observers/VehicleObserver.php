<?php

namespace App\Observers;

use App\Models\Vehicle;

class VehicleObserver
{
    /**
     * Handle the Vehicle "creating" event.
     */
    public function creating(Vehicle $vehicle): void
    {
        $this->validateVehicleData($vehicle);
    }

    /**
     * Handle the Vehicle "updating" event.
     */
    public function updating(Vehicle $vehicle): void
    {
        $this->validateVehicleData($vehicle);
    }

    /**
     * Validate vehicle data based on type.
     */
    protected function validateVehicleData(Vehicle $vehicle): void
    {
        // Validation du nombre de sièges selon le type
        $validSeats = match($vehicle->type) {
            'motorcycle' => [1, 2],
            'sport' => [2],
            'car' => [5, 7],
            'van' => [8, 11],
            default => []
        };

        if (!in_array($vehicle->seats, $validSeats)) {
            throw new \InvalidArgumentException(
                "Un véhicule de type '{$vehicle->type}' ne peut avoir {$vehicle->seats} places. " .
                "Valeurs autorisées : " . implode(', ', $validSeats)
            );
        }

        // Validation du type de carburant pour les motos (essence uniquement)
        if ($vehicle->type === 'motorcycle' && $vehicle->fuel_type !== 'gasoline') {
            throw new \InvalidArgumentException(
                "Une moto ne peut avoir que 'gasoline' comme type de carburant."
            );
        }

        // Validation cohérence available/status
        if (in_array($vehicle->status, ['maintenance', 'out_of_service']) && $vehicle->available) {
            throw new \InvalidArgumentException(
                "Un véhicule en maintenance ou hors service ne peut pas être disponible."
            );
        }

        // Validation du kilométrage
        if ($vehicle->mileage < 0) {
            throw new \InvalidArgumentException(
                "Le kilométrage ne peut pas être négatif."
            );
        }

        // Validation du prix
        if ($vehicle->daily_rate <= 0) {
            throw new \InvalidArgumentException(
                "Le prix journalier doit être supérieur à 0."
            );
        }
    }
}
