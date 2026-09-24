<?php
namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class IncomeExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $rows;

    public function __construct($rows)
    {
        $this->rows = $rows;
    }

    public function collection()
    {
        return collect($this->rows);
    }

    public function headings(): array
    {
        return [
            'Id',
            'Invoice',
            'ID Pelanggan',
            'Nama',
            'Tipe Service',
            'Paket Langganan',
            'Harga [+PPN]',
            'Fee Seller',
            'Profit',
            'Tanggal Aktif',
            'Owner Data',
        ];
    }

    public function map($row): array
    {
        return [
            $row['id'] ?? '-',
            $row['invoice_number'] ?? '-',
            $row['customer_id'] ?? '-',
            $row['customer_name'] ?? '-',
            $row['service_type'] ?? '-',
            $row['package_name'] ?? '-',
            $row['harga_ppn'] ?? 0,
            $row['fee_seller'] ?? 0,
            $row['profit'] ?? 0,
            $row['paid_at'] ?? '-',
            $row['reseller_name'] ?? '-',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '4F46E5']]],
        ];
    }
}