<?php

namespace Database\Factories;

use App\Models\TipoVehiculo;
use Illuminate\Database\Eloquent\Factories\Factory;

class TipoVehiculoFactory extends Factory
{
    protected $model = TipoVehiculo::class;

    public function definition()
    {
        return [
            'nombre' => $this->faker->randomElement(['Moto', 'Sedan', 'SUV', 'Camioneta']),
            'comision' => $this->faker->randomFloat(2, 5, 20),
            'estado' => 'activo',
        ];
    }

    public function moto()
    {
        return $this->state(function (array $attributes) {
            return [
                'nombre' => 'Moto',
                'comision' => 5.00,
            ];
        });
    }

    public function sedan()
    {
        return $this->state(function (array $attributes) {
            return [
                'nombre' => 'Sedan',
                'comision' => 10.00,
            ];
        });
    }

    public function suv()
    {
        return $this->state(function (array $attributes) {
            return [
                'nombre' => 'SUV',
                'comision' => 10.00,
            ];
        });
    }

    public function camioneta()
    {
        return $this->state(function (array $attributes) {
            return [
                'nombre' => 'Camioneta',
                'comision' => 15.00,
            ];
        });
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
