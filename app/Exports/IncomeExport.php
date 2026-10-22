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
    protected bool $isReseller;

    public function __construct($rows, bool $isReseller = false)
    {
        $this->rows = $rows;
        $this->isReseller = $isReseller;
    }

    public function collection()
    {
        return collect($this->rows);
    }

    public function headings(): array
    {
        $headings = [
            'Id',
            'Invoice',
            'ID Pelanggan',
            'Nama Pelanggan',
            'Tipe Service',
            'Paket Langganan',
            'Total Bruto [+PPN]',
            'Komisi Reseller',
            $this->isReseller ? 'Setor Pusat' : 'Nett ISP (Stlh Reseller)',
            'Tanggal Aktif',
        ];

        if (!$this->isReseller) {
            $headings[] = 'Owner Data';
        }

        return $headings;
    }

    public function map($row): array
    {
        if (is_object($row)) {
            $row = (array) $row;
        }

        $mapped = [
            $row['id'] ?? '-',
            $row['invoice_number'] ?? '-',
            $row['customer_id'] ?? '-',
            $row['customer_name'] ?? '-',
            $row['service_type'] ?? '-',
            $row['package_name'] ?? '-',
            (float) ($row['harga_ppn'] ?? 0),
            (float) ($row['fee_seller'] ?? 0),
            (float) ($row['profit'] ?? 0),
            $row['paid_at'] ?? '-',
        ];

        if (!$this->isReseller) {
            $mapped[] = $row['reseller_name'] ?? '-';
        }

        return $mapped;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '059669']],
            ],
        ];
    }
}