<?php

namespace App\Exports;

use App\Models\TarjetaRegalo;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TarjetasRegaloExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    public function collection()
    {
        return TarjetaRegalo::with('cliente.persona')->get();
    }

    public function headings(): array
    {
        return [
            'Code',
            'Initial Value',
            'Current Balance',
            'Status',
            'Sale Date',
            'Expiration Date',
            'Customer',
        ];
    }

    public function map($tarjeta): array
    {
        return [
            $tarjeta->codigo,
            $tarjeta->valor_inicial,
            $tarjeta->saldo_actual,
            ucfirst($tarjeta->estado),
            $tarjeta->fecha_venta,
            $tarjeta->fecha_vencimiento ?? '-',
            $tarjeta->cliente ? $tarjeta->cliente->persona->razon_social : '-',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:G1')->getFont()->setBold(true);
        $sheet->getStyle('A1:G1')->getFill()->setFillType('solid')->getStartColor()->setRGB('D9E1F2');
        $sheet->getColumnDimension('A')->setWidth(20);
        $sheet->getColumnDimension('B')->setWidth(15);
        $sheet->getColumnDimension('C')->setWidth(18);
        $sheet->getColumnDimension('D')->setWidth(12);
        $sheet->getColumnDimension('E')->setWidth(15);
        $sheet->getColumnDimension('F')->setWidth(18);
        $sheet->getColumnDimension('G')->setWidth(30);
        return [];
    }
}
