<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Persona>
 */
class PersonaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'razon_social' => fake()->name(),
            'direccion' => fake()->address(),
            'tipo_persona' => fake()->randomElement(['Cliente', 'Proveedor']),
            'telefono' => fake()->numerify('9########'),
            'estado' => 1,
            'documento_id' => \App\Models\Documento::factory(),
            'numero_documento' => fake()->numerify('########'),
        ];
    }
}
