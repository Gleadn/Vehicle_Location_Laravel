<?php

namespace App\Observers;

use App\Models\LocationDemand;

class LocationDemandObserver
{
    /**
     * Handle the LocationDemand "creating" event.
     */
    public function creating(LocationDemand $demand): void
    {
        $this->validateDemandData($demand);
    }

    /**
     * Handle the LocationDemand "updating" event.
     */
    public function updating(LocationDemand $demand): void
    {
        $this->validateDemandData($demand);
    }

    /**
     * Validate location demand data.
     */
    protected function validateDemandData(LocationDemand $demand): void
    {
        // Validation des dates
        if ($demand->start_date && $demand->end_date) {
            if ($demand->start_date > $demand->end_date) {
                throw new \InvalidArgumentException(
                    "La date de début ne peut pas être postérieure à la date de fin."
                );
            }

            // Vérifier que la date de début n'est pas dans le passé
            if ($demand->start_date < now()->toDateString()) {
                throw new \InvalidArgumentException(
                    "La date de début ne peut pas être dans le passé."
                );
            }
        }

        // Validation pour demande spécifique
        if ($demand->demand_type === 'specific') {
            if (!$demand->vehicle_id) {
                throw new \InvalidArgumentException(
                    "Une demande spécifique doit avoir un vehicle_id."
                );
            }
        }

        // Validation pour demande générique
        if ($demand->demand_type === 'generic') {
            if (!$demand->vehicle_type) {
                throw new \InvalidArgumentException(
                    "Une demande générique doit spécifier un type de véhicule."
                );
            }
        }

        // Validation du budget
        if ($demand->max_budget && $demand->max_budget <= 0) {
            throw new \InvalidArgumentException(
                "Le budget maximum doit être supérieur à 0."
            );
        }

        // Validation du nombre de places
        if ($demand->seats_required && $demand->seats_required <= 0) {
            throw new \InvalidArgumentException(
                "Le nombre de places requis doit être supérieur à 0."
            );
        }
    }
}
