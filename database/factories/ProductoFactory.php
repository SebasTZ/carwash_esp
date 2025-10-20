<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Producto>
 */
class ProductoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'codigo' => fake()->unique()->numerify('PROD####'),
            'nombre' => fake()->words(3, true),
            'stock' => fake()->numberBetween(0, 100),
            'precio_venta' => fake()->randomFloat(2, 10, 500),
            'descripcion' => fake()->sentence(),
            'fecha_vencimiento' => fake()->optional()->dateTimeBetween('now', '+2 years'),
            'img_path' => null,
            'estado' => 1,
            'es_servicio_lavado' => false,
            'marca_id' => \App\Models\Marca::factory(),
            'presentacione_id' => \App\Models\Presentacione::factory(),
        ];
    }
    
    /**
     * State para crear un servicio de lavado
     */
    public function servicioLavado(): static
    {
        return $this->state(fn (array $attributes) => [
            'es_servicio_lavado' => true,
            'nombre' => 'Servicio de Lavado',
            'precio_venta' => 30.00,
        ]);
    }
}
