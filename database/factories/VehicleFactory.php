<?php

namespace Database\Factories;

use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Vehicle>
 */
class VehicleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Véhicules cohérents : marque + modèle + carburant logique
        $vehicles = [
            'car' => [
                ['brand' => 'Toyota', 'model' => 'Corolla', 'fuel' => ['gasoline', 'hybrid']],
                ['brand' => 'Honda', 'model' => 'Civic', 'fuel' => ['gasoline', 'hybrid']],
                ['brand' => 'Ford', 'model' => 'Focus', 'fuel' => ['gasoline', 'diesel']],
                ['brand' => 'Volkswagen', 'model' => 'Golf', 'fuel' => ['gasoline', 'diesel', 'electric']],
                ['brand' => 'Renault', 'model' => 'Clio', 'fuel' => ['gasoline', 'diesel']],
                ['brand' => 'Peugeot', 'model' => '308', 'fuel' => ['gasoline', 'diesel', 'hybrid']],
                ['brand' => 'Tesla', 'model' => 'Model 3', 'fuel' => ['electric']],
            ],
            'motorcycle' => [
                ['brand' => 'Harley-Davidson', 'model' => 'Street 750', 'fuel' => ['gasoline']],
                ['brand' => 'Yamaha', 'model' => 'MT-07', 'fuel' => ['gasoline']],
                ['brand' => 'Honda', 'model' => 'CB500F', 'fuel' => ['gasoline']],
                ['brand' => 'Kawasaki', 'model' => 'Ninja 650', 'fuel' => ['gasoline']],
                ['brand' => 'Ducati', 'model' => 'Monster', 'fuel' => ['gasoline']],
            ],
            'van' => [
                ['brand' => 'Mercedes-Benz', 'model' => 'Sprinter', 'fuel' => ['diesel', 'electric']],
                ['brand' => 'Ford', 'model' => 'Transit', 'fuel' => ['diesel', 'gasoline']],
                ['brand' => 'Volkswagen', 'model' => 'Transporter', 'fuel' => ['diesel', 'gasoline']],
                ['brand' => 'Renault', 'model' => 'Master', 'fuel' => ['diesel']],
                ['brand' => 'Fiat', 'model' => 'Ducato', 'fuel' => ['diesel']],
            ],
            'sport' => [
                ['brand' => 'Ferrari', 'model' => '458 Italia', 'fuel' => ['gasoline']],
                ['brand' => 'Porsche', 'model' => '911', 'fuel' => ['gasoline', 'hybrid']],
                ['brand' => 'Lamborghini', 'model' => 'Huracán', 'fuel' => ['gasoline']],
                ['brand' => 'BMW', 'model' => 'M4', 'fuel' => ['gasoline']],
                ['brand' => 'Audi', 'model' => 'R8', 'fuel' => ['gasoline', 'electric']],
            ],
        ];

        // Choisir un type de véhicule
        $type = fake()->randomElement(['car', 'motorcycle', 'van', 'sport']);
        
        // Choisir un véhicule cohérent selon le type
        $vehicle = fake()->randomElement($vehicles[$type]);
        $brand = $vehicle['brand'];
        $model = $vehicle['model'];
        $fuelType = fake()->randomElement($vehicle['fuel']);

        // Nombre de sièges selon le type (valeurs précises)
        $seats = match($type) {
            'motorcycle' => fake()->randomElement([1, 2]),
            'sport' => 2,
            'car' => fake()->randomElement([5, 7]),
            'van' => fake()->randomElement([8, 11]),
        };

        // Prix journalier selon le type et le carburant
        $dailyRate = match($type) {
            'motorcycle' => fake()->randomFloat(2, 40, 80),
            'car' => $fuelType === 'electric' ? fake()->randomFloat(2, 80, 140) : fake()->randomFloat(2, 50, 120),
            'sport' => fake()->randomFloat(2, 200, 500),
            'van' => fake()->randomFloat(2, 80, 150),
        };

        // Statut cohérent : si en maintenance ou hors service, pas disponible
        $status = fake()->randomElement(['active', 'active', 'active', 'active', 'maintenance', 'out_of_service']);
        $available = $status === 'active' ? fake()->boolean(90) : false;

        return [
            'brand' => $brand,
            'model' => $model,
            'registration_number' => strtoupper(fake()->bothify('??-###-??')),
            'type' => $type,
            'fuel_type' => $fuelType,
            'seats' => $seats,
            'mileage' => fake()->randomFloat(2, 10000, 50000),
            'daily_rate' => $dailyRate,
            'available' => $available,
            'status' => $status,
            'description' => fake()->optional()->sentence(10),
        ];
    }

    /**
     * Indicate that the vehicle is available.
     */
    public function available(): static
    {
        return $this->state(fn (array $attributes) => [
            'available' => true,
            'status' => 'active',
        ]);
    }

    /**
     * Indicate that the vehicle is in maintenance.
     */
    public function maintenance(): static
    {
        return $this->state(fn (array $attributes) => [
            'available' => false,
            'status' => 'maintenance',
        ]);
    }

    /**
     * Create a vehicle of specific type.
     */
    public function ofType(string $type): static
    {
        // Déterminer les sièges valides pour ce type
        $seats = match($type) {
            'motorcycle' => fake()->randomElement([1, 2]),
            'sport' => 2,
            'car' => fake()->randomElement([5, 7]),
            'van' => fake()->randomElement([8, 11]),
        };

        // Déterminer un carburant cohérent
        $fuelType = match($type) {
            'motorcycle' => 'gasoline',
            'sport' => fake()->randomElement(['gasoline', 'hybrid']),
            'car' => fake()->randomElement(['gasoline', 'diesel', 'electric', 'hybrid']),
            'van' => fake()->randomElement(['gasoline', 'diesel']),
        };

        return $this->state(fn (array $attributes) => [
            'type' => $type,
            'seats' => $seats,
            'fuel_type' => $fuelType,
        ]);
    }
}
