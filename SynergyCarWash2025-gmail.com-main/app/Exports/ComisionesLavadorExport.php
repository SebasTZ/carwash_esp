<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Conditional;

class ComisionesLavadorExport implements FromView, WithEvents, ShouldAutoSize
{
    protected $data;
    protected $fechaInicio;
    protected $fechaFin;

    public function __construct($data, $fechaInicio, $fechaFin)
    {
        $this->data = $data;
        $this->fechaInicio = $fechaInicio;
        $this->fechaFin = $fechaFin;
    }

    public function view(): View
    {
        return view('pagos_comisiones.reporte_excel', [
            'data' => $this->data,
            'fechaInicio' => $this->fechaInicio,
            'fechaFin' => $this->fechaFin,
        ]);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet;
                $sheet->getStyle('A3:E3')->applyFromArray([
                    'font' => [
                        'bold' => true,
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'B8CCE4'],
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                    ],
                ]);

                $numLavadores = count($this->data);
                $firstDataRow = 4;
                $lastDataRow = $firstDataRow + $numLavadores - 1;
                $totalRow = $lastDataRow + 1;
                $lastColumn = 'E';

                // Título del reporte y período
                $sheet->mergeCells('A1:E1');
                $sheet->setCellValue('A1', 'REPORTE DE COMISIONES POR LAVADOR');
                $sheet->mergeCells('A2:E2');
                $sheet->setCellValue('A2', "Período: {$this->fechaInicio} a {$this->fechaFin}");

                // Estilo para el título
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 14,
                        'color' => ['rgb' => 'FFFFFF'],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '4F81BD'],
                    ],
                ]);

                // Estilo para el período
                $sheet->getStyle('A2')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 11,
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'DCE6F1'],
                    ],
                ]);

                // Estilo para los encabezados
                $sheet->getStyle('A3:E3')->applyFromArray([
                    'font' => [
                        'bold' => true,
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'B8CCE4'],
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                    ],
                ]);

                // Estilo para los datos
                $sheet->getStyle("A{$firstDataRow}:{$lastColumn}{$lastDataRow}")->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ],
                    ],
                ]);

                // Formato de números para columnas de montos
                $sheet->getStyle("C{$firstDataRow}:E{$lastDataRow}")->getNumberFormat()
                    ->setFormatCode(NumberFormat::FORMAT_CURRENCY_USD_SIMPLE);

                // Formato condicional para saldos pendientes
                $conditional = new Conditional();
                $conditional->setConditionType(Conditional::CONDITION_CELLIS);
                $conditional->setOperatorType(Conditional::OPERATOR_GREATERTHAN);
                $conditional->addCondition('0');
                $conditional->getStyle()->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('FFE5E5');
                $conditional->getStyle()->getFont()->getColor()->setRGB('FF0000');

                // Aplicar el formato condicional
                $sheet->getStyle("E{$firstDataRow}:E{$lastDataRow}")
                    ->setConditionalStyles([$conditional]);

                // Totales
                $sheet->setCellValue("A{$totalRow}", 'TOTALES');
                $sheet->setCellValue("B{$totalRow}", "=SUM(B{$firstDataRow}:B{$lastDataRow})");
                $sheet->setCellValue("C{$totalRow}", "=SUM(C{$firstDataRow}:C{$lastDataRow})");
                $sheet->setCellValue("D{$totalRow}", "=SUM(D{$firstDataRow}:D{$lastDataRow})");
                $sheet->setCellValue("E{$totalRow}", "=SUM(E{$firstDataRow}:E{$lastDataRow})");

                $sheet->getStyle("A{$totalRow}:E{$totalRow}")->applyFromArray([
                    'font' => [
                        'bold' => true,
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'DCE6F1'],
                    ],
                    'borders' => [
                        'top' => [
                            'borderStyle' => Border::BORDER_DOUBLE,
                        ],
                    ],
                ]);

                // Ajustar ancho de columnas
                foreach (range('A', $lastColumn) as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }

                // Inmovilizar paneles
                $sheet->freezePane('A4');
            },
        ];
    }
}
