<?php

namespace Database\Factories;

use App\Models\PagoComision;
use App\Models\Lavador;
use Illuminate\Database\Eloquent\Factories\Factory;

class PagoComisionFactory extends Factory
{
    protected $model = PagoComision::class;

    public function definition()
    {
        return [
            'lavador_id' => Lavador::factory(),
            'monto_pagado' => $this->faker->randomFloat(2, 10, 100),
            'desde' => now()->subDays(7),
            'hasta' => now(),
            'observacion' => $this->faker->optional()->sentence(),
            'fecha_pago' => now(),
        ];
    }
}
