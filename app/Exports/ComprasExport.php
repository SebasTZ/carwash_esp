<?php

namespace App\Exports;

use App\Models\Compra;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class ComprasExport implements FromCollection, WithHeadings, WithEvents
{
    protected $compras;

    public function __construct($compras)
    {
        $this->compras = $compras;
    }

    public function collection()
    {
        return $this->compras->map(function ($compra) {
            return [
                $compra->comprobante->tipo_comprobante,
                $compra->numero_comprobante,
                $compra->proveedore->persona->razon_social,
                Carbon::parse($compra->fecha_hora)->format('d-m-Y H:i'),
                number_format($compra->impuesto, 2),
                number_format($compra->total, 2)
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Tipo de comprobante',
            'Número de comprobante',
            'Proveedor',
            'Fecha y hora',
            'IGV',
            'Total'
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $lastRow = $this->compras->count() + 1;
                $lastColumn = 'F';

                // Estilo para encabezados
                $event->sheet->getStyle('A1:' . $lastColumn . '1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => 'FFFFFF'],
                    ],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'color' => ['rgb' => '2F75B5']
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

                // Formato para las columnas de montos (IGV y Total)
                $event->sheet->getStyle('E2:F'.$lastRow)->getNumberFormat()
                    ->setFormatCode('_("$"* #,##0.00_);_("$"* \(#,##0.00\);_("$"* "-"??_);_(@_)');

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
                            ->setRGB('EBF1F8');
                    }
                }

                // Agregar totales
                $totalRow = $lastRow + 1;
                $igvTotal = $this->compras->sum('impuesto');
                $total = $this->compras->sum('total');

                $event->sheet->setCellValue('D' . $totalRow, 'TOTALES:');
                $event->sheet->setCellValue('E' . $totalRow, number_format($igvTotal, 2));
                $event->sheet->setCellValue('F' . $totalRow, number_format($total, 2));

                // Estilo para la fila de totales
                $event->sheet->getStyle('D'.$totalRow.':'.$lastColumn.$totalRow)->applyFromArray([
                    'font' => [
                        'bold' => true,
                    ],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'color' => ['rgb' => 'D9E2F3']
                    ],
                    'borders' => [
                        'outline' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        ],
                    ],
                ]);
            }
        ];
    }
}