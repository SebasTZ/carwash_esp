<?php

namespace Database\Factories;

use App\Models\Lavador;
use Illuminate\Database\Eloquent\Factories\Factory;

class LavadorFactory extends Factory
{
    protected $model = Lavador::class;

    public function definition()
    {
        return [
            'nombre' => $this->faker->name(),
            'dni' => $this->faker->unique()->numerify('########'),
            'telefono' => $this->faker->phoneNumber(),
            'estado' => 'activo',
        ];
    }

    public function inactivo()
    {
        return $this->state(function (array $attributes) {
            return [
                'estado' => 'inactivo',
            ];
        });
    }
}
