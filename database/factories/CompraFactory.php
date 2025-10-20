<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Compra>
 */
class CompraFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'proveedore_id' => \App\Models\Proveedore::factory(),
            'comprobante_id' => \App\Models\Comprobante::factory(),
            'numero_comprobante' => 'C001-' . str_pad($this->faker->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
            'fecha_hora' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'impuesto' => $this->faker->randomFloat(2, 0, 20),
            'total' => $this->faker->randomFloat(2, 50, 1000),
        ];
    }
}
