<?php

namespace App\Services;

use App\Models\Venta;
use App\Models\Cliente;
use App\Models\Producto;
use App\Exceptions\VentaException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VentaService
{
    public function __construct(
        private StockService $stockService,
        private FidelizacionService $fidelizacionService,
        private TarjetaRegaloService $tarjetaRegaloService,
        private ComprobanteService $comprobanteService
    ) {}

    /**
     * Procesa una venta completa con todos sus componentes
     *
     * @param array $data Datos validados de la venta
     * @return Venta
     * @throws VentaException
     */
    public function procesarVenta(array $data): Venta
    {
        return DB::transaction(function () use ($data) {
            // 1. Validar y procesar medio de pago
            $this->procesarMedioPago($data);

            // 2. Generar número de comprobante
            $numeroComprobante = $this->comprobanteService->generarSiguienteNumero($data['comprobante_id']);

            // 3. Crear la venta
            $venta = $this->crearVenta($data, $numeroComprobante);

            // 4. Procesar productos (asociar y actualizar stock)
            $this->procesarProductos($venta, $data);

            // 5. Procesar fidelización si aplica
            $this->procesarFidelizacion($venta, $data);

            // 6. Crear control de lavado si es servicio
            if ($data['servicio_lavado'] ?? false) {
                $this->crearControlLavado($venta, $data);
            }

            Log::channel('ventas')->info('Venta procesada exitosamente', [
                'venta_id' => $venta->id,
                'cliente_id' => $venta->cliente_id,
                'total' => $venta->total,
                'numero_comprobante' => $venta->numero_comprobante,
                'medio_pago' => $venta->medio_pago,
            ]);

            return $venta->load(['cliente.persona', 'productos', 'comprobante']);
        });
    }

    /**
     * Procesa el medio de pago de la venta
     */
    private function procesarMedioPago(array &$data): void
    {
        $medioPago = $data['medio_pago'];

        match ($medioPago) {
            'tarjeta_regalo' => $this->procesarPagoTarjetaRegalo($data),
            'lavado_gratis' => $this->procesarLavadoGratis($data),
            'efectivo', 'tarjeta_credito' => null, // No requiere procesamiento especial
            default => throw new VentaException("Medio de pago inválido: {$medioPago}")
        };
    }

    /**
     * Procesa pago con tarjeta de regalo
     */
    private function procesarPagoTarjetaRegalo(array &$data): void
    {
        if (empty($data['tarjeta_regalo_codigo'])) {
            throw new VentaException('Debe proporcionar el código de la tarjeta de regalo');
        }

        $tarjeta = $this->tarjetaRegaloService->validarYDescontar(
            $data['tarjeta_regalo_codigo'],
            $data['total']
        );

        $data['tarjeta_regalo_id'] = $tarjeta->id;
        $data['lavado_gratis'] = false;
    }

    /**
     * Procesa lavado gratis por fidelización
     */
    private function procesarLavadoGratis(array &$data): void
    {
        $cliente = Cliente::findOrFail($data['cliente_id']);

        if (!$this->fidelizacionService->puedeUsarLavadoGratis($cliente)) {
            throw new VentaException('El cliente no tiene suficientes lavados acumulados para un lavado gratuito');
        }

        $this->fidelizacionService->canjearLavadoGratis($cliente);

        $data['lavado_gratis'] = true;
        $data['tarjeta_regalo_id'] = null;
    }

    /**
     * Crea el registro de venta
     */
    private function crearVenta(array $data, string $numeroComprobante): Venta
    {
        $horarioLavado = null;
        if (!empty($data['horario_lavado'])) {
            $horarioLavado = now()->format('Y-m-d') . ' ' . $data['horario_lavado'] . ':00';
        }

        return Venta::create([
            'fecha_hora' => now(),
            'impuesto' => $data['impuesto'] ?? 0,
            'numero_comprobante' => $numeroComprobante,
            'total' => $data['total'],
            'cliente_id' => $data['cliente_id'],
            'user_id' => auth()->id(),
            'comprobante_id' => $data['comprobante_id'],
            'comentarios' => $data['comentarios'] ?? null,
            'medio_pago' => $data['medio_pago'],
            'servicio_lavado' => $data['servicio_lavado'] ?? false,
            'horario_lavado' => $horarioLavado,
            'lavado_gratis' => $data['lavado_gratis'] ?? false,
            'tarjeta_regalo_id' => $data['tarjeta_regalo_id'] ?? null,
            'estado' => 1,
        ]);
    }

    /**
     * Procesa los productos de la venta
     */
    private function procesarProductos(Venta $venta, array $data): void
    {
        $productos = $data['arrayidproducto'] ?? [];
        $cantidades = $data['arraycantidad'] ?? [];
        $preciosVenta = $data['arrayprecioventa'] ?? [];
        $descuentos = $data['arraydescuento'] ?? [];

        foreach ($productos as $index => $productoId) {
            $cantidad = intval($cantidades[$index]);
            $precioVenta = floatval($preciosVenta[$index]);
            $descuento = floatval($descuentos[$index] ?? 0);

            // Asociar producto a la venta
            $venta->productos()->attach($productoId, [
                'cantidad' => $cantidad,
                'precio_venta' => $precioVenta,
                'descuento' => $descuento,
            ]);

            // Actualizar stock usando el servicio
            $producto = Producto::findOrFail($productoId);
            
            // Solo actualizar stock si NO es servicio de lavado
            if (!$producto->es_servicio_lavado) {
                $this->stockService->descontarStock(
                    $producto,
                    $cantidad,
                    "Venta #{$venta->numero_comprobante}"
                );
            }
        }
    }

    /**
     * Procesa la fidelización del cliente
     */
    private function procesarFidelizacion(Venta $venta, array $data): void
    {
        $cliente = Cliente::findOrFail($venta->cliente_id);

        // 1. Acumular puntos de fidelización (10% del total)
        if ($data['medio_pago'] !== 'lavado_gratis') {
            $this->fidelizacionService->acumularPuntos($cliente, $venta->total);
        }

        // 2. Acumular lavado si es servicio de lavado (para lavado gratis)
        if (
            ($data['servicio_lavado'] ?? false) &&
            $data['medio_pago'] !== 'lavado_gratis' &&
            $data['medio_pago'] !== 'tarjeta_regalo'
        ) {
            $this->fidelizacionService->acumularLavado($cliente);
        }
    }

    /**
     * Crea el control de lavado asociado
     */
    private function crearControlLavado(Venta $venta, array $data): void
    {
        if (empty($data['horario_lavado'])) {
            throw new VentaException('Debe proporcionar un horario de culminación del lavado');
        }

        \App\Models\ControlLavado::create([
            'venta_id' => $venta->id,
            'cliente_id' => $venta->cliente_id,
            'lavador_id' => null,
            'hora_llegada' => now(),
            'horario_estimado' => $venta->horario_lavado,
            'inicio_lavado' => null,
            'fin_lavado' => null,
            'inicio_interior' => null,
            'fin_interior' => null,
            'hora_final' => null,
            'tiempo_total' => null,
            'estado' => 'En espera',
        ]);
    }

    /**
     * Anula una venta y revierte sus efectos
     */
    public function anularVenta(Venta $venta, string $motivo): void
    {
        DB::transaction(function () use ($venta, $motivo) {
            // Revertir stock
            foreach ($venta->productos as $producto) {
                if (!$producto->es_servicio_lavado) {
                    $this->stockService->incrementarStock(
                        $producto,
                        $producto->pivot->cantidad,
                        "Anulación venta #{$venta->numero_comprobante}"
                    );
                }
            }

            // Revertir fidelización
            if ($venta->servicio_lavado && !$venta->lavado_gratis) {
                $this->fidelizacionService->revertirLavado($venta->cliente);
            }

            // Revertir tarjeta de regalo
            if ($venta->tarjeta_regalo_id) {
                $this->tarjetaRegaloService->revertirUso($venta->tarjeta_regalo_id, $venta->total);
            }

            // Marcar venta como anulada
            $venta->update([
                'estado' => 0,
                'comentarios' => ($venta->comentarios ?? '') . "\nAnulada: {$motivo}",
            ]);

            Log::warning('Venta anulada', [
                'venta_id' => $venta->id,
                'motivo' => $motivo,
                'usuario_id' => auth()->id(),
            ]);
        });
    }
}
