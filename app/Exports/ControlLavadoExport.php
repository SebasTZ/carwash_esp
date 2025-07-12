<?php

namespace App\Exports;

use App\Models\ControlLavado;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class ControlLavadoExport implements FromCollection, WithHeadings, WithEvents
{
    protected $lavados;

    public function __construct($lavados)
    {
        $this->lavados = $lavados;
    }

    public function collection()
    {
        return $this->lavados->map(function ($lavado) {
            $tiempoTotal = null;
            if ($lavado->hora_final && $lavado->hora_llegada) {
                $tiempoTotal = Carbon::parse($lavado->hora_llegada)->diffInMinutes($lavado->hora_final);
            }
            return [
                $lavado->venta->numero_comprobante,
                $lavado->cliente->persona->razon_social,
                $lavado->lavador ? $lavado->lavador->nombre : '-',
                $lavado->tipoVehiculo ? $lavado->tipoVehiculo->nombre : '-',
                $lavado->tipoVehiculo ? number_format($lavado->tipoVehiculo->comision, 2) : '-',
                Carbon::parse($lavado->hora_llegada)->format('d-m-Y H:i'),
            $lavado->hora_final ? Carbon::parse($lavado->hora_final)->format('d-m-Y H:i') : 'En proceso',
            $tiempoTotal ? number_format($tiempoTotal, 0) . ' min' : '-',
            $lavado->estado,
            $lavado->observaciones ?? '-',
            $lavado->user ? $lavado->user->name : 'Sin asignar'
            ];
        });
    }

    public function headings(): array
    {
        return [
            'N° Comprobante',
            'Cliente',
            'Lavador',
            'Tipo de vehículo',
            'Comisión',
            'Hora de llegada',
            'Hora final',
            'Tiempo total',
            'Estado',
            'Observaciones',
            'Usuario asignado',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $lastRow = $this->lavados->count() + 1;
                $lastColumn = 'K';

                // Estilo para encabezados
                $event->sheet->getStyle('A1:' . $lastColumn . '1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => 'FFFFFF'],
                    ],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'color' => ['rgb' => '4F46E5'], // Color primario índigo
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    ],
                ]);

                // Estilo para el cuerpo de la tabla
                $event->sheet->getStyle('A2:' . $lastColumn . $lastRow)->applyFromArray([
                    'alignment' => [
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['rgb' => 'E5E7EB'],
                        ],
                    ],
                ]);

                // Formato condicional para estados
                $event->sheet->getStyle('I2:I' . $lastRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $event->sheet->getStyle('H2:H' . $lastRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                // Ancho de columnas
                $event->sheet->getColumnDimension('A')->setWidth(15); // N° Comprobante
                $event->sheet->getColumnDimension('B')->setWidth(30); // Cliente
                $event->sheet->getColumnDimension('C')->setWidth(20); // Lavador
                $event->sheet->getColumnDimension('D')->setWidth(15); // Tipo Vehículo
                $event->sheet->getColumnDimension('E')->setWidth(12); // Comisión
                $event->sheet->getColumnDimension('F')->setWidth(18); // Hora Llegada
                $event->sheet->getColumnDimension('G')->setWidth(18); // Hora Final
                $event->sheet->getColumnDimension('H')->setWidth(12); // Tiempo Total
                $event->sheet->getColumnDimension('I')->setWidth(12); // Estado
                $event->sheet->getColumnDimension('J')->setWidth(25); // Observaciones
                $event->sheet->getColumnDimension('K')->setWidth(20); // Responsable

                // Formato condicional para estados
                foreach ($event->sheet->getRowIterator(2, $lastRow) as $row) {
                    $estado = $event->sheet->getCell('I' . $row->getRowIndex())->getValue();
                    $colorFondo = '';
                    switch ($estado) {
                        case 'En espera':
                            $colorFondo = 'FCD34D'; // Amarillo
                            break;
                        case 'En proceso':
                            $colorFondo = '60A5FA'; // Azul
                            break;
                        case 'Terminado':
                            $colorFondo = '34D399'; // Verde
                            break;
                    }
                    if ($colorFondo) {
                        $event->sheet->getStyle('I' . $row->getRowIndex())->applyFromArray([
                            'fill' => [
                                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                                'color' => ['rgb' => $colorFondo],
                            ],
                            'font' => [
                                'color' => ['rgb' => 'FFFFFF'],
                            ],
                        ]);
                    }
                }

                // Formato para montos
                $event->sheet->getStyle('E2:E' . $lastRow)->getNumberFormat()->setFormatCode('S/ #,##0.00');
            },
        ];
    }
}