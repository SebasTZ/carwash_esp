<?php

namespace Tests\Feature\Livewire\Ventas;

use App\Livewire\Ventas\ReportePeriodo;
use Livewire\Livewire;
use Tests\TestCase;

class ReportePeriodoTest extends TestCase
{
    #[\PHPUnit\Framework\Attributes\Test]
    public function renderiza_ventas_del_periodo_y_total_excluyendo_medios_no_permitidos(): void
    {
        $ventas = [
            [
                'comprobante' => ['tipo_comprobante' => 'Boleta', 'numero_comprobante' => 'B001-0001'],
                'cliente' => ['persona' => ['razon_social' => 'Cliente Uno']],
                'fecha_hora' => '2026-04-15 10:00:00',
                'vendedor' => ['name' => 'Vendedor Uno'],
                'total' => '100.00',
                'comentarios' => '-',
                'medio_pago' => 'efectivo',
                'efectivo' => '100.00',
                'tarjeta_credito' => '0.00',
            ],
            [
                'comprobante' => ['tipo_comprobante' => 'Boleta', 'numero_comprobante' => 'B001-0002'],
                'cliente' => ['persona' => ['razon_social' => 'Cliente Dos']],
                'fecha_hora' => '2026-04-15 11:00:00',
                'vendedor' => ['name' => 'Vendedor Dos'],
                'total' => '50.00',
                'comentarios' => '-',
                'medio_pago' => 'tarjeta_regalo',
                'efectivo' => '0.00',
                'tarjeta_credito' => '0.00',
            ],
        ];

        Livewire::test(ReportePeriodo::class, ['reporte' => 'diario', 'ventas' => $ventas])
            ->assertSee('Cliente Uno')
            ->assertSee('Cliente Dos')
            ->assertSeeHtml('Total registros:</strong> 2')
            ->assertSeeHtml('Monto total:</strong> S/ 100.00');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function aplica_busqueda_reactiva_en_tabla_del_periodo(): void
    {
        $ventas = [
            [
                'comprobante' => ['tipo_comprobante' => 'Boleta', 'numero_comprobante' => 'B001-0001'],
                'cliente' => ['persona' => ['razon_social' => 'Cliente Uno']],
                'fecha_hora' => '2026-04-15 10:00:00',
                'vendedor' => ['name' => 'Vendedor Uno'],
                'total' => '100.00',
                'comentarios' => '-',
                'medio_pago' => 'efectivo',
                'efectivo' => '100.00',
                'tarjeta_credito' => '0.00',
            ],
            [
                'comprobante' => ['tipo_comprobante' => 'Boleta', 'numero_comprobante' => 'B001-0002'],
                'cliente' => ['persona' => ['razon_social' => 'Cliente Dos']],
                'fecha_hora' => '2026-04-15 11:00:00',
                'vendedor' => ['name' => 'Vendedor Dos'],
                'total' => '90.00',
                'comentarios' => '-',
                'medio_pago' => 'tarjeta_credito',
                'efectivo' => '0.00',
                'tarjeta_credito' => '90.00',
            ],
        ];

        Livewire::test(ReportePeriodo::class, ['reporte' => 'semanal', 'ventas' => $ventas])
            ->set('search', 'Dos')
            ->assertSee('Cliente Dos')
            ->assertDontSee('Cliente Uno')
            ->assertSeeHtml('Total registros:</strong> 1');
    }
}
