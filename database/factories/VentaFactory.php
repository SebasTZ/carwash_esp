<?php

namespace Database\Factories;

use App\Models\Venta;
use App\Models\Cliente;
use App\Models\User;
use App\Models\Comprobante;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Venta>
 */
class VentaFactory extends Factory
{
    protected $model = Venta::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'cliente_id' => Cliente::factory(),
            'user_id' => User::factory(),
            'comprobante_id' => Comprobante::factory(),
            'numero_comprobante' => 'B001-' . str_pad($this->faker->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
            'fecha_hora' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'impuesto' => $this->faker->randomFloat(2, 0, 20),
            'total' => $this->faker->randomFloat(2, 10, 500),
            'estado' => 1,
            'medio_pago' => $this->faker->randomElement(['efectivo', 'tarjeta_credito']),
            'servicio_lavado' => false,
        ];
    }

    /**
     * Estado de venta completada
     */
    public function completada(): static
    {
        return $this->state(fn (array $attributes) => [
            'estado' => 1,
        ]);
    }

    /**
     * Estado de venta anulada
     */
    public function anulada(): static
    {
        return $this->state(fn (array $attributes) => [
            'estado' => 0,
        ]);
    }

    /**
     * Venta con pago en efectivo
     */
    public function efectivo(): static
    {
        return $this->state(fn (array $attributes) => [
            'metodo_pago' => 'efectivo',
        ]);
    }

    /**
     * Venta con pago por tarjeta
     */
    public function tarjeta(): static
    {
        return $this->state(fn (array $attributes) => [
            'metodo_pago' => 'tarjeta',
        ]);
    }

    /**
     * Venta con monto específico
     */
    public function conTotal(float $total): static
    {
        return $this->state(fn (array $attributes) => [
            'total' => $total,
            'impuesto' => $total * 0.18, // IGV 18%
        ]);
    }
}
