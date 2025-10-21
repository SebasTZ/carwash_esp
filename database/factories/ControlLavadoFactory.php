<?php

namespace Database\Factories;

use App\Models\ControlLavado;
use App\Models\Venta;
use App\Models\Cliente;
use App\Models\Lavador;
use App\Models\TipoVehiculo;
use Illuminate\Database\Eloquent\Factories\Factory;

class ControlLavadoFactory extends Factory
{
    protected $model = ControlLavado::class;

    public function definition()
    {
        return [
            'venta_id' => Venta::factory(),
            'cliente_id' => Cliente::factory(),
            'lavador_id' => null,
            'tipo_vehiculo_id' => null,
            'hora_llegada' => now(),
            'horario_estimado' => now()->addMinutes(30),
            'inicio_lavado' => null,
            'fin_lavado' => null,
            'inicio_interior' => null,
            'fin_interior' => null,
            'hora_final' => null,
            'tiempo_total' => null,
            'estado' => 1,
        ];
    }

    public function conLavador()
    {
        return $this->state(function (array $attributes) {
            return [
                'lavador_id' => Lavador::factory(),
                'tipo_vehiculo_id' => TipoVehiculo::factory(),
            ];
        });
    }

    public function iniciado()
    {
        return $this->state(function (array $attributes) {
            return [
                'lavador_id' => Lavador::factory(),
                'tipo_vehiculo_id' => TipoVehiculo::factory(),
                'inicio_lavado' => now(),
            ];
        });
    }

    public function finalizado()
    {
        return $this->state(function (array $attributes) {
            return [
                'lavador_id' => Lavador::factory(),
                'tipo_vehiculo_id' => TipoVehiculo::factory(),
                'inicio_lavado' => now()->subMinutes(30),
                'fin_lavado' => now()->subMinutes(20),
            ];
        });
    }

    public function conInterior()
    {
        return $this->state(function (array $attributes) {
            return [
                'lavador_id' => Lavador::factory(),
                'tipo_vehiculo_id' => TipoVehiculo::factory(),
                'inicio_lavado' => now()->subMinutes(30),
                'fin_lavado' => now()->subMinutes(20),
                'inicio_interior' => now()->subMinutes(15),
            ];
        });
    }

    public function completado()
    {
        return $this->state(function (array $attributes) {
            return [
                'lavador_id' => Lavador::factory(),
                'tipo_vehiculo_id' => TipoVehiculo::factory(),
                'inicio_lavado' => now()->subMinutes(40),
                'fin_lavado' => now()->subMinutes(25),
                'inicio_interior' => now()->subMinutes(20),
                'fin_interior' => now(),
                'hora_final' => now(),
                'tiempo_total' => 40,
            ];
        });
    }
}
