<?php

namespace App\Exports;

use App\Models\Venta;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Carbon\Carbon;

class VentasExport implements FromCollection, WithHeadings, WithMapping, WithEvents
{
    protected $ventas;

    public function __construct($ventas)
    {
        $this->ventas = $ventas;
    }

    public function collection()
    {
        return $this->ventas;
    }

    public function map($venta): array
    {
        $paymentMethods = [
            'efectivo' => 'Efectivo',
            'tarjeta' => 'Tarjeta de crédito',
            'tarjeta_credito' => 'Tarjeta de crédito',
            'tarjeta_regalo' => 'Tarjeta de regalo',
            'lavado_gratis' => 'Lavado gratis (Fidelidad)',
        ];
        return [
            $venta->comprobante->tipo_comprobante . ' ' . $venta->numero_comprobante,
            $venta->cliente->persona->razon_social,
            Carbon::parse($venta->fecha_hora)->format('d-m-Y H:i'),
            $venta->user->name,
            number_format($venta->total, 2),
            $venta->comentarios ?? '-',
            $paymentMethods[$venta->medio_pago] ?? ucfirst(str_replace('_', ' ', $venta->medio_pago)),
            number_format($venta->efectivo, 2),
            number_format($venta->yape, 2),
            $venta->servicio_lavado ? 'Sí' : 'No',
            $venta->horario_lavado ? Carbon::parse($venta->horario_lavado)->format('d-m-Y H:i') : 'N/D',
        ];
    }

    public function headings(): array
    {
        return [
            'Comprobante',
            'Cliente',
            'Fecha y hora',
            'Vendedor',
            'Total',
            'Comentarios',
            'Medio de pago',
            'Efectivo',
            'Tarjeta de crédito',
            'Servicio de lavado',
            'Hora fin de lavado',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $count = $this->ventas->count();
                $lastRow = $count + 1;
                $lastColumn = 'K';

                // Estilo para encabezados
                $event->sheet->getStyle('A1:' . $lastColumn . '1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => 'FFFFFF'],
                    ],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'color' => ['rgb' => '1F4E78']
                    ],
                    'borders' => [
                        'outline' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        ],
                    ],
                ]);

                if ($count > 0) {
                    // Estilo para el contenido
                    $event->sheet->getStyle('A2:' . $lastColumn . $lastRow)->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            ],
                        ],
                    ]);

                    // Formato para las columnas de montos
                    $event->sheet->getStyle('E2:E'.$lastRow)->getNumberFormat()
                        ->setFormatCode('_("$"* #,##0.00_);_("$"* \(#,##0.00\);_("$"* "-"??_);_(@_)');
                    $event->sheet->getStyle('H2:I'.$lastRow)->getNumberFormat()
                        ->setFormatCode('_("$"* #,##0.00_);_("$"* \(#,##0.00\);_("$"* "-"??_);_(@_)');

                    // Alineación
                    $event->sheet->getStyle('A1:' . $lastColumn . $lastRow)
                        ->getAlignment()
                        ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

                    // Alternar colores de filas
                    for($row = 2; $row <= $lastRow; $row++) {
                        if($row % 2 == 0) {
                            $event->sheet->getStyle('A'.$row.':'.$lastColumn.$row)
                                ->getFill()
                                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                                ->getStartColor()
                                ->setRGB('E8EFF7');
                        }
                    }

                    // Agregar totales
                    $totalRow = $lastRow + 1;
                    $totalVentas = $this->ventas->sum('total');
                    $totalEfectivo = $this->ventas->sum('efectivo');
                    $totalYape = $this->ventas->sum('yape');
                    $totalComision = $this->ventas->where('servicio_lavado', 1)->sum('controlLavado.tipoVehiculo.comision');
                    $totalPagado = $this->ventas->where('servicio_lavado', 1)->sum('controlLavado.lavador.pagosComisiones.monto_pagado');
                    $totalPendiente = $totalComision - $totalPagado;

                    $event->sheet->setCellValue('D' . $totalRow, 'TOTALES:');
                    $event->sheet->setCellValue('E' . $totalRow, number_format($totalVentas, 2));
                    $event->sheet->setCellValue('H' . $totalRow, number_format($totalEfectivo, 2));
                    $event->sheet->setCellValue('I' . $totalRow, number_format($totalYape, 2));

                    // Estilo para la fila de totales
                    $event->sheet->getStyle('D'.$totalRow.':'.$lastColumn.$totalRow)->applyFromArray([
                        'font' => [
                            'bold' => true,
                        ],
                        'fill' => [
                            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                            'color' => ['rgb' => 'C5D9F1']
                        ],
                        'borders' => [
                            'outline' => [
                                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            ],
                        ],
                    ]);

                    // Formato para los totales
                    $event->sheet->getStyle('E'.$totalRow)->getNumberFormat()
                        ->setFormatCode('_("$"* #,##0.00_);_("$"* \(#,##0.00\);_("$"* "-"??_);_(@_)');
                    $event->sheet->getStyle('H'.$totalRow.':I'.$totalRow)->getNumberFormat()
                        ->setFormatCode('_("$"* #,##0.00_);_("$"* \(#,##0.00\);_("$"* "-"??_);_(@_)');
                }

                // Ancho automático para todas las columnas (siempre)
                foreach(range('A', $lastColumn) as $column) {
                    $event->sheet->getColumnDimension($column)->setAutoSize(true);
                }
            }
        ];
    }
}