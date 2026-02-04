<?php

namespace Database\Seeders;

use App\Models\Vehicle;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class VehicleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Créer 10 voitures
        Vehicle::factory()
            ->count(10)
            ->ofType('car')
            ->create();

        // Créer 5 motos
        Vehicle::factory()
            ->count(5)
            ->ofType('motorcycle')
            ->create();

        // Créer 5 vans
        Vehicle::factory()
            ->count(5)
            ->ofType('van')
            ->create();

        // Créer 3 voitures sportives
        Vehicle::factory()
            ->count(3)
            ->ofType('sport')
            ->create();

        // Créer quelques véhicules en maintenance
        Vehicle::factory()
            ->count(2)
            ->maintenance()
            ->create();
    }
}
