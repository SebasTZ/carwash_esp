<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Cita>
 */
class CitaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'cliente_id' => \App\Models\Cliente::factory(),
            'fecha' => $this->faker->dateTimeBetween('now', '+30 days')->format('Y-m-d'),
            'hora' => $this->faker->time('H:i'),
            'posicion_cola' => $this->faker->numberBetween(1, 10),
            'estado' => $this->faker->randomElement(['pendiente', 'en_proceso', 'completada', 'cancelada']),
            'notas' => $this->faker->optional()->sentence(),
        ];
    }
}
