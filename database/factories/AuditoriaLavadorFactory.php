<?php

namespace Database\Factories;

use App\Models\AuditoriaLavador;
use App\Models\ControlLavado;
use App\Models\Lavador;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AuditoriaLavadorFactory extends Factory
{
    protected $model = AuditoriaLavador::class;

    public function definition()
    {
        return [
            'control_lavado_id' => ControlLavado::factory(),
            'lavador_id_anterior' => Lavador::factory(),
            'lavador_id_nuevo' => Lavador::factory(),
            'usuario_id' => User::factory(),
            'motivo' => $this->faker->sentence(),
            'fecha_cambio' => now(),
        ];
    }
}
