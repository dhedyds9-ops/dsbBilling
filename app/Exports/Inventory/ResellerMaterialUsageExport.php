<?php

namespace App\Exports\Inventory;

use App\Models\Inventory\InventoryCustody;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ResellerMaterialUsageExport implements FromCollection, WithHeadings, ShouldAutoSize, WithMapping, WithStyles
{
    protected ?int $resellerId;
    protected string $startDate;
    protected string $endDate;
    protected string $priceBasis; // 'cost_price' atau 'selling_price'
    protected string $categoryFilter;

    public function __construct(?int $resellerId = null, string $startDate = '', string $endDate = '', string $priceBasis = 'cost_price', string $categoryFilter = '')
    {
        $this->resellerId = $resellerId ? (int) $resellerId : null;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->priceBasis = in_array($priceBasis, ['cost_price', 'selling_price']) ? $priceBasis : 'cost_price';
        $this->categoryFilter = $categoryFilter;
    }

    public function collection()
    {
        return InventoryCustody::with(['item', 'itemSerial', 'holder', 'customer'])
            ->where('holder_type', 'reseller')
            ->when($this->resellerId, fn($q) => $q->where('holder_id', $this->resellerId))
            ->when($this->startDate, fn($q) => $q->whereDate('created_at', '>=', $this->startDate))
            ->when($this->endDate, fn($q) => $q->whereDate('created_at', '<=', $this->endDate))
            ->when($this->categoryFilter, fn($q) => $q->whereHas('item', fn($i) => $i->where('category', $this->categoryFilter)))
            ->latest('id')
            ->get();
    }

    public function headings(): array
    {
        $priceLabel = $this->priceBasis === 'selling_price' ? 'Harga Jual Satuan (Rp)' : 'HPP Satuan (Rp)';
        $totalUsedLabel = $this->priceBasis === 'selling_price' ? 'Total Nilai Terpakai / Tagihan (Rp)' : 'Total HPP Terpakai (Rp)';
        $totalAvailLabel = $this->priceBasis === 'selling_price' ? 'Total Nilai Sisa di Tangan (Rp)' : 'Total HPP Sisa di Tangan (Rp)';

        return [
            'ID',
            'Nama Reseller (Mitra)',
            'Kode SKU',
            'Nama Barang',
            'Kategori',
            'Serial Number (SN)',
            'Qty Diambil',
            'Qty Terpakai (Used)',
            'Qty Retur',
            'Sisa di Tangan',
            'Satuan',
            $priceLabel,
            $totalUsedLabel,
            $totalAvailLabel,
            'Status',
            'Tanggal Distribusi',
            'Catatan',
        ];
    }

    public function map($custody): array
    {
        $price = (float) ($this->priceBasis === 'selling_price'
            ? ($custody->item?->selling_price ?? 0)
            : ($custody->item?->cost_price ?? 0));

        $usedQty = (float) $custody->used_quantity;
        $availQty = (float) $custody->available_quantity;

        $totalUsedNominal = $usedQty * $price;
        $totalAvailNominal = $availQty * $price;

        return [
            $custody->id,
            $custody->holder?->name ?? 'Reseller #' . $custody->holder_id,
            $custody->item?->sku ?? '-',
            $custody->item?->name ?? '-',
            $custody->item?->category ?? '-',
            $custody->itemSerial?->serial_number ?? '-',
            (float) $custody->quantity,
            $usedQty,
            (float) $custody->returned_quantity,
            $availQty,
            $custody->item?->unit ?? 'unit',
            $price,
            $totalUsedNominal,
            $totalAvailNominal,
            match($custody->status) {
                'used' => 'Terpasang',
                'in_custody' => 'Di Tangan Reseller',
                'returned' => 'Diretur',
                default => ucfirst((string) $custody->status),
            },
            $custody->created_at ? $custody->created_at->format('d/m/Y H:i') : '-',
            $custody->notes ?? '-',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF1E293B'], // Slate-800
                ],
            ],
        ];
    }
}
