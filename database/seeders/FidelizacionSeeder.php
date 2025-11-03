<?php

namespace Database\Seeders;

use App\Models\Cliente;
use App\Models\Fidelizacion;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FidelizacionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener todos los clientes existentes
        $clientes = Cliente::all();

        foreach ($clientes as $cliente) {
            // Actualizar lavados acumulados (10 lavados = 1 lavado gratis disponible)
            // Usaremos 10 lavados como base inicial para poder probar
            $cliente->update(['lavados_acumulados' => 10]);
            
            // También crear/actualizar registro de fidelización con puntos
            // (puntos se calculan como 10% del total de ventas)
            Fidelizacion::updateOrCreate(
                ['cliente_id' => $cliente->id],
                ['puntos' => 5] // 5 puntos base
            );
        }

        $this->command->info('✅ Se actualizaron ' . $clientes->count() . ' cliente(s) con 10 lavados acumulados');
    }
}

