<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class InvoiceExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $invoices;
    protected bool $isReseller;

    public function __construct($invoices, bool $isReseller = false)
    {
        $this->invoices = $invoices;
        $this->isReseller = $isReseller;
    }

    public function collection()
    {
        return collect($this->invoices);
    }

    public function headings(): array
    {
        $headings = [
            'ID',
            'No Invoice',
            'ID Pelanggan',
            'Nama Pelanggan',
            'Telepon / WA',
            'Layanan / Paket',
            'Tgl Terbit',
            'Jatuh Tempo',
            'Status',
            'Total Tagihan (Rp)',
            'Terbayar (Rp)',
            'Sisa Tagihan (Rp)',
        ];

        if (!$this->isReseller) {
            $headings[] = 'Owner / Reseller';
        }

        return $headings;
    }

    public function map($invoice): array
    {
        $plan = $invoice->customer?->customerServices?->first()?->serviceProfile?->name
            ?? $invoice->items?->first()?->description
            ?? '-';

        $resellerName = $invoice->customer?->reseller?->name
            ?? $invoice->customer?->createdBy?->name
            ?? '-';

        $statusLabel = match ($invoice->status) {
            'paid' => 'Lunas',
            'unpaid' => 'Belum Lunas',
            'partial' => 'Sebagian',
            'overdue' => 'Jatuh Tempo',
            'pending' => 'Menunggu',
            default => ucfirst($invoice->status ?? '-'),
        };

        $mapped = [
            $invoice->id,
            $invoice->invoice_number,
            $invoice->customer?->code ?? '-',
            $invoice->customer?->name ?? '-',
            $invoice->customer?->phone ?? '-',
            $plan,
            $invoice->issue_date ? $invoice->issue_date->format('d/m/Y') : '-',
            $invoice->due_date ? $invoice->due_date->format('d/m/Y') : '-',
            $statusLabel,
            (float) ($invoice->total_amount ?? 0),
            (float) ($invoice->paid_amount ?? 0),
            (float) (($invoice->total_amount ?? 0) - ($invoice->paid_amount ?? 0)),
        ];

        if (!$this->isReseller) {
            $mapped[] = $resellerName;
        }

        return $mapped;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '2563EB']],
            ],
        ];
    }
}
