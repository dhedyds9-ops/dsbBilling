<?php

namespace App\Exports\Inventory;

use App\Models\Inventory\Asset;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AssetExport implements FromCollection, WithHeadings, ShouldAutoSize, WithMapping, WithStyles
{
    protected array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $f = $this->filters;

        return Asset::with(['category', 'warehouse', 'assignedTo', 'vendor'])
            ->when(!empty($f['search']), function ($q) use ($f) {
                $term = trim($f['search']);
                $q->where(function ($sub) use ($term) {
                    $sub->where('name', 'like', "%{$term}%")
                        ->orWhere('code', 'like', "%{$term}%")
                        ->orWhere('serial_number', 'like', "%{$term}%")
                        ->orWhere('mac_address', 'like', "%{$term}%");
                });
            })
            ->when(!empty($f['category']) && $f['category'] !== 'all', function ($q) use ($f) {
                $q->where('category_id', $f['category']);
            })
            ->when(!empty($f['status']) && $f['status'] !== 'all', function ($q) use ($f) {
                $q->where('status', $f['status']);
            })
            ->when(!empty($f['warehouse']) && $f['warehouse'] !== 'all', function ($q) use ($f) {
                $q->where('warehouse_id', $f['warehouse']);
            })
            ->when(!empty($f['type']) && $f['type'] !== 'all', function ($q) use ($f) {
                $q->where('type', $f['type']);
            })
            ->when(!empty($f['assigned_to']) && $f['assigned_to'] !== 'all', function ($q) use ($f) {
                $q->where('assigned_to_id', $f['assigned_to']);
            })
            ->orderBy('name')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Kode Aset',
            'Nama Aset / Alat Kerja',
            'Tipe',
            'Kategori',
            'Serial Number (SN)',
            'MAC Address',
            'Gudang / Lokasi',
            'Status',
            'Kondisi Fisik',
            'Pemegang Saat Ini',
            'Vendor / Toko',
            'Harga Beli (Rp)',
            'Tanggal Pembelian',
            'Akhir Garansi',
        ];
    }

    public function map($asset): array
    {
        return [
            $asset->code,
            $asset->name,
            strtoupper($asset->type ?? 'TOOL'),
            $asset->category?->name ?? '-',
            $asset->serial_number ?: '-',
            $asset->mac_address ?: '-',
            $asset->warehouse?->name ?? '-',
            strtoupper(str_replace('_', ' ', $asset->status ?? 'AVAILABLE')),
            strtoupper($asset->condition ?? 'GOOD'),
            $asset->assignedTo?->name ?? '-',
            $asset->vendor?->name ?? '-',
            (float) ($asset->purchase_price ?? 0),
            $asset->purchase_date ? $asset->purchase_date->format('d/m/Y') : '-',
            $asset->warranty_end_date ? $asset->warranty_end_date->format('d/m/Y') : '-',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF1E293B'],
                ],
            ],
        ];
    }
}
