<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Comprobante>
 */
class ComprobanteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tipo_comprobante' => fake()->randomElement(['Boleta', 'Factura', 'Ticket']),
            'serie' => fake()->randomElement(['B', 'F']),
            'estado' => 1,
        ];
    }
}
