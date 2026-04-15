<?php

namespace Tests\Feature\Livewire\Ventas;

use App\Livewire\Ventas\ReportePeriodo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class ReportePeriodoTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        Permission::findOrCreate('reporte-diario-venta');
        Permission::findOrCreate('reporte-semanal-venta');
        Permission::findOrCreate('reporte-mensual-venta');

        $this->user = User::factory()->create();
        $this->user->givePermissionTo(['reporte-diario-venta', 'reporte-semanal-venta', 'reporte-mensual-venta']);
    }

    private function ventasMuestra(): array
    {
        return [
            [
                'comprobante' => ['tipo_comprobante' => 'Boleta', 'numero_comprobante' => 'B001-0001'],
                'cliente' => ['persona' => ['razon_social' => 'Cliente Uno']],
                'fecha_hora' => '2026-04-15 10:00:00',
                'vendedor' => ['name' => 'Vendedor Uno'],
                'total' => '100.00',
                'total_raw' => 100.0,
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
                'total_raw' => 50.0,
                'comentarios' => '-',
                'medio_pago' => 'tarjeta_regalo',
                'efectivo' => '0.00',
                'tarjeta_credito' => '0.00',
            ],
        ];
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function renderiza_ventas_del_periodo_y_total_excluyendo_medios_no_permitidos(): void
    {
        Livewire::actingAs($this->user)
            ->test(ReportePeriodo::class, ['reporte' => 'diario', 'ventas' => $this->ventasMuestra()])
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
                'total_raw' => 100.0,
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
                'total_raw' => 90.0,
                'comentarios' => '-',
                'medio_pago' => 'tarjeta_credito',
                'efectivo' => '0.00',
                'tarjeta_credito' => '90.00',
            ],
        ];

        Livewire::actingAs($this->user)
            ->test(ReportePeriodo::class, ['reporte' => 'semanal', 'ventas' => $ventas])
            ->set('search', 'Dos')
            ->assertSee('Cliente Dos')
            ->assertDontSee('Cliente Uno')
            ->assertSeeHtml('Total registros:</strong> 1');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function usuario_sin_permiso_recibe_403(): void
    {
        $sinPermiso = User::factory()->create();

        Livewire::actingAs($sinPermiso)
            ->test(ReportePeriodo::class, ['reporte' => 'diario', 'ventas' => []])
            ->assertForbidden();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function lista_vacia_renderiza_sin_errores(): void
    {
        Livewire::actingAs($this->user)
            ->test(ReportePeriodo::class, ['reporte' => 'mensual', 'ventas' => []])
            ->assertSeeHtml('Total registros:</strong> 0')
            ->assertSeeHtml('Monto total:</strong> S/ 0.00');
    }
}
