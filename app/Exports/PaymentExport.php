<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PaymentExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $payments;
    protected bool $isReseller;

    public function __construct($payments, bool $isReseller = false)
    {
        $this->payments = $payments;
        $this->isReseller = $isReseller;
    }

    public function collection()
    {
        return collect($this->payments);
    }

    public function headings(): array
    {
        $headings = [
            'ID',
            'No Referensi',
            'ID Pelanggan',
            'Nama Pelanggan',
            'No Invoice Terkait',
            'Tanggal Pembayaran',
            'Metode Pembayaran',
            'Payment Gateway',
            'Status',
            'Jumlah Dibayar (Rp)',
        ];

        if (!$this->isReseller) {
            $headings[] = 'Owner / Reseller';
        }

        return $headings;
    }

    public function map($payment): array
    {
        $invoicesStr = $payment->invoices ? $payment->invoices->pluck('invoice_number')->implode(', ') : '-';
        if (empty($invoicesStr)) {
            $invoicesStr = '-';
        }

        $resellerName = $payment->customer?->reseller?->name
            ?? $payment->customer?->createdBy?->name
            ?? '-';

        $statusLabel = match ($payment->status) {
            'success' => 'Sukses / Lunas',
            'pending' => 'Menunggu',
            'failed' => 'Gagal / Batal',
            'expired' => 'Kedaluwarsa',
            default => ucfirst($payment->status ?? '-'),
        };

        $mapped = [
            $payment->id,
            $payment->reference_number ?? ('PAY-' . str_pad($payment->id, 6, '0', STR_PAD_LEFT)),
            $payment->customer?->code ?? '-',
            $payment->customer?->name ?? '-',
            $invoicesStr,
            $payment->paid_at ? $payment->paid_at->format('d/m/Y H:i') : ($payment->created_at ? $payment->created_at->format('d/m/Y H:i') : '-'),
            strtoupper(str_replace('_', ' ', $payment->method ?? '-')),
            strtoupper($payment->gateway ?? 'MANUAL'),
            $statusLabel,
            (float) ($payment->amount ?? 0),
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
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '059669']],
            ],
        ];
    }
}
