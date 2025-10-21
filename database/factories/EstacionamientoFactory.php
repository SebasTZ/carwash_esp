<?php

namespace Database\Factories;

use App\Models\Estacionamiento;
use App\Models\Cliente;
use Illuminate\Database\Eloquent\Factories\Factory;

class EstacionamientoFactory extends Factory
{
    protected $model = Estacionamiento::class;

    public function definition(): array
    {
        return [
            'cliente_id' => Cliente::factory(),
            'placa' => strtoupper($this->faker->bothify('???-###')),
            'marca' => $this->faker->randomElement(['Toyota', 'Honda', 'Nissan', 'Ford', 'Chevrolet']),
            'modelo' => $this->faker->randomElement(['Corolla', 'Civic', 'Sentra', 'Focus', 'Cruze']),
            'telefono' => $this->faker->numerify('9########'),
            'tarifa_hora' => $this->faker->randomFloat(2, 3, 10),
            'hora_entrada' => now(),
            'hora_salida' => null,
            'monto_total' => null,
            'estado' => 'ocupado',
        ];
    }

    public function finalizado(): static
    {
        return $this->state(function (array $attributes) {
            $horaEntrada = now()->subHours(random_int(2, 8));
            $horaSalida = $horaEntrada->copy()->addHours(random_int(1, 4));
            $tiempoHoras = ceil($horaEntrada->diffInMinutes($horaSalida) / 60);
            
            return [
                'hora_entrada' => $horaEntrada,
                'hora_salida' => $horaSalida,
                'monto_total' => $attributes['tarifa_hora'] * $tiempoHoras,
                'estado' => 'finalizado',
            ];
        });
    }

    public function ocupado(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'estado' => 'ocupado',
                'hora_salida' => null,
                'monto_total' => null,
            ];
        });
    }
}
