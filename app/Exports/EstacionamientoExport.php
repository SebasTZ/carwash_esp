<?php

namespace App\Exports;

use App\Models\Estacionamiento;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Carbon\Carbon;

class EstacionamientoExport implements FromCollection, WithHeadings, WithEvents
{
    protected $estacionamientos;

    public function __construct($estacionamientos)
    {
        $this->estacionamientos = $estacionamientos;
    }

    public function collection()
    {
        return $this->estacionamientos->map(function ($estacionamiento) {
            return [
                $estacionamiento->placa,
                $estacionamiento->cliente->persona->razon_social,
                $estacionamiento->marca . ' / ' . $estacionamiento->modelo,
                $estacionamiento->hora_entrada->format('d/m/Y H:i'),
                $estacionamiento->hora_salida ? $estacionamiento->hora_salida->format('d/m/Y H:i') : '-',
                'S/.' . number_format($estacionamiento->tarifa_hora, 2),
                $estacionamiento->hora_salida ? 
                    number_format($estacionamiento->hora_entrada->diffInHours($estacionamiento->hora_salida, true), 1) . ' horas' : 
                    '-',
                $estacionamiento->monto_total ? 'S/.' . number_format($estacionamiento->monto_total, 2) : '-',
                $estacionamiento->estado
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Placa',
            'Cliente',
            'Vehículo',
            'Hora de entrada',
            'Hora de salida',
            'Tarifa por hora',
            'Tiempo total',
            'Monto total',
            'Estado'
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $event->sheet->getColumnDimension('A')->setWidth(15);
                $event->sheet->getColumnDimension('B')->setWidth(30);
                $event->sheet->getColumnDimension('C')->setWidth(25);
                $event->sheet->getColumnDimension('D')->setWidth(20);
                $event->sheet->getColumnDimension('E')->setWidth(20);
                $event->sheet->getColumnDimension('F')->setWidth(15);
                $event->sheet->getColumnDimension('G')->setWidth(15);
                $event->sheet->getColumnDimension('H')->setWidth(15);
                $event->sheet->getColumnDimension('I')->setWidth(15);
                
                $event->sheet->getStyle('A1:I1')->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'CCCCCC']
                    ]
                ]);
            }
        ];
    }
}