<?php

namespace App\Exports;

use App\Models\Cliente;
use App\Models\Venta;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class FidelidadExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $clientes;
    protected $lavadosGratis;

    public function __construct($clientes, $lavadosGratis)
    {
        $this->clientes = $clientes;
        $this->lavadosGratis = $lavadosGratis;
    }

    public function collection()
    {
        // Unimos ambos reportes en una sola colección con separador
        $rows = collect();
        $rows->push(['Frequent Customers', '', '']);
        foreach ($this->clientes as $c) {
            $rows->push([
                $c->persona->razon_social,
                $c->lavados_acumulados,
                ''
            ]);
        }
        $rows->push(['', '', '']);
        $rows->push(['Free Washes Granted', '', '']);
        foreach ($this->lavadosGratis as $l) {
            $rows->push([
                $l->cliente->persona->razon_social,
                $l->fecha_hora,
                $l->numero_comprobante
            ]);
        }
        return $rows;
    }

    public function headings(): array
    {
        return ['Customer', 'Accumulated Washes / Date', 'Receipt'];
    }

    public function map($row): array
    {
        return $row;
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:C1')->getFont()->setBold(true);
        $sheet->getStyle('A1:C1')->getFill()->setFillType('solid')->getStartColor()->setRGB('D9E1F2');
        $sheet->getColumnDimension('A')->setWidth(30);
        $sheet->getColumnDimension('B')->setWidth(22);
        $sheet->getColumnDimension('C')->setWidth(18);
        return [];
    }
}
