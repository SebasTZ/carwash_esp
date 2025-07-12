<?php

namespace App\Exports;

use App\Models\Cita;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class CitasExport implements FromCollection, WithHeadings, WithEvents
{
    protected $citas;

    public function __construct($citas)
    {
        $this->citas = $citas;
    }

    public function collection()
    {
        return $this->citas->map(function ($cita) {
            return [
                $cita->cliente->persona->razon_social,
                $cita->cliente->persona->numero_documento,
                Carbon::parse($cita->fecha)->format('d-m-Y'),
                Carbon::parse($cita->hora)->format('H:i'),
                $cita->posicion_cola,
                $cita->estado,
                $cita->notas ?? '-'
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Cliente',
            'Documento',
            'Fecha',
            'Hora',
            'Posición en cola',
            'Estado',
            'Notas'
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $lastRow = $this->citas->count() + 1;
                $lastColumn = 'G';

                // Estilo para encabezados
                $event->sheet->getStyle('A1:' . $lastColumn . '1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => 'FFFFFF'],
                    ],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'color' => ['rgb' => '4F81BD']
                    ],
                    'borders' => [
                        'outline' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        ],
                    ],
                ]);

                // Estilo para el contenido
                $event->sheet->getStyle('A2:' . $lastColumn . $lastRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        ],
                    ],
                ]);

                // Ancho automático para todas las columnas
                foreach(range('A', $lastColumn) as $column) {
                    $event->sheet->getColumnDimension($column)->setAutoSize(true);
                }

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
                            ->setRGB('F2F2F2');
                    }
                }
            }
        ];
    }
}