<?php

namespace App\Services\Billing;

use App\Models\Billing\Invoice;
use App\Models\Billing\Subscription;
use App\Repositories\Billing\InvoiceRepository;
use App\Repositories\Billing\InvoiceItemRepository;
use App\Repositories\Billing\SubscriptionRepository;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Src\Domain\Billing\Events\InvoiceBatchGeneratedEvent;

class PeriodeTagihanService
{
    public function __construct(
        protected InvoiceRepository $invoiceRepository,
        protected InvoiceItemRepository $invoiceItemRepository,
        protected SubscriptionRepository $subscriptionRepository,
    ) {}

    protected function invoiceBase(?string $search, array $filters): \Illuminate\Database\Eloquent\Builder
    {
        $query = Invoice::query()
            ->leftJoin('members', 'invoices.customer_id', '=', 'members.id')
            ->leftJoin('billing_subscriptions', 'invoices.customer_id', '=', 'billing_subscriptions.customer_id')
            ->leftJoin('customer_services', 'billing_subscriptions.customer_service_id', '=', 'customer_services.id')
            ->leftJoin('users as creators', 'invoices.created_by', '=', 'creators.id')
            ->select([
                'invoices.id',
                'invoices.customer_id',
                'invoices.invoice_number',
                'invoices.issue_date',
                'invoices.due_date',
                'invoices.total_amount',
                'invoices.paid_amount',
                'invoices.status',
                'invoices.created_by',
                'members.name as customer_name',
                'customer_services.service_profile_id as package_id',
                'customer_services.id as customer_service_id',
            ]);

        if ($search) {
            $query->where(function ($sq) use ($search) {
                $sq->where('invoices.invoice_number', 'like', "%{$search}%")
                   ->orWhere('members.name', 'like', "%{$search}%");
            });
        }
        if (!empty($filters['status'])) {
            $query->where('invoices.status', $filters['status']);
        }
        if (!empty($filters['tahun'])) {
            $query->whereYear('invoices.issue_date', (int)$filters['tahun']);
        }
        if (!empty($filters['bulan'])) {
            $query->whereMonth('invoices.issue_date', (int)$filters['bulan']);
        }
        if (!empty($filters['sales_id'])) {
            $query->whereExists(function ($sq) use ($filters) {
                $sq->select(DB::raw(1))
                    ->from('customer_services as cs_sales')
                    ->whereRaw('cs_sales.customer_id = invoices.customer_id')
                    ->where('cs_sales.created_by', $filters['sales_id']);
            });
        }
        if (!empty($filters['reseller_id'])) {
            $query->where('invoices.created_by', $filters['reseller_id']);
        }
        if (!empty($filters['package_id'])) {
            $query->whereExists(function ($sq) use ($filters) {
                $sq->select(DB::raw(1))
                    ->from('invoice_items as ii')
                    ->whereRaw('ii.invoice_id = invoices.id')
                    ->where('ii.package_id', $filters['package_id']);
            });
        }
        if (!empty($filters['router_id'])) {
            $query->whereExists(function ($sq) use ($filters) {
                $sq->select(DB::raw(1))
                    ->from('customer_services as cs_router')
                    ->leftJoin('pppoe_users as pu', 'cs_router.id', '=', 'pu.customer_service_id')
                    ->leftJoin('hotspot_users as hu', 'cs_router.id', '=', 'hu.customer_service_id')
                    ->leftJoin('nas_devices as nd_p', 'pu.nas_device_id', '=', 'nd_p.id')
                    ->leftJoin('nas_devices as nd_h', 'hu.nas_device_id', '=', 'nd_h.id')
                    ->whereRaw('cs_router.customer_id = invoices.customer_id')
                    ->where(function ($qq) use ($filters) {
                        $qq->where('nd_p.id', $filters['router_id'])
                           ->orWhere('nd_h.id', $filters['router_id']);
                    });
            });
        }

        return $query;
    }

    public function aggregatePeriode(array $filters, ?string $search, string $sort, string $dir): Collection
    {
        $base = $this->invoiceBase($search, $filters);

        $raw = DB::query()
            ->select([
                DB::raw("YEAR(issue_date) as tahun"),
                DB::raw("MONTH(issue_date) as bulan"),
                DB::raw("COUNT(DISTINCT id) as jumlah_invoice"),
                DB::raw("COALESCE(SUM(total_amount), 0) as total_tagihan"),
                DB::raw("COALESCE(SUM(paid_amount), 0) as sudah_dibayar"),
                DB::raw("COALESCE(SUM(CASE WHEN status = 'paid' THEN total_amount ELSE 0 END), 0) as total_lunas"),
                DB::raw("COALESCE(SUM(total_amount), 0) - COALESCE(SUM(paid_amount), 0) as belum_dibayar"),
                DB::raw("SUM(CASE WHEN status = 'paid' THEN 1 ELSE 0 END) as paid_count"),
                DB::raw("SUM(CASE WHEN status = 'overdue' THEN 1 ELSE 0 END) as overdue_count"),
            ])
            ->from(DB::raw("({$base->toSql()}) as inv_base"))
            ->mergeBindings($base->getQuery())
            ->groupBy('tahun', 'bulan');

        if (!empty($filters['tahun'])) {
            $raw->having('tahun', (int)$filters['tahun']);
        }
        if (!empty($filters['bulan'])) {
            $raw->having('bulan', (int)$filters['bulan']);
        }

        $sortCol = match ($sort) {
            'tahun' => 'tahun',
            'bulan' => 'bulan',
            'jumlah_invoice' => 'jumlah_invoice',
            'total_tagihan' => 'total_tagihan',
            'sudah_dibayar' => 'sudah_dibayar',
            'belum_dibayar' => 'belum_dibayar',
            'overdue_count' => 'overdue_count',
            default => 'tahun',
        };
        $sortDir = strtolower($dir) === 'asc' ? 'asc' : 'desc';

        $rows = $raw->orderBy($sortCol, $sortDir)->get();

        return $rows->map(function ($r) {
            $r->lunas_pct = $r->total_tagihan > 0
                ? (int) round(($r->sudah_dibayar / $r->total_tagihan) * 100)
                : 0;
            $r->label_periode = $this->periodeLabel((int)$r->tahun, (int)$r->bulan);
            return $r;
        });
    }

    protected function periodeLabel(int $tahun, int $bulan): string
    {
        $namaBulan = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];
        return ($namaBulan[$bulan] ?? $bulan) . ' ' . $tahun;
    }

    public function summary(array $filters): array
    {
        $base = $this->invoiceBase(null, $filters);
        $agg = DB::query()
            ->select([
                DB::raw("COUNT(DISTINCT id) as total_invoice"),
                DB::raw("COALESCE(SUM(total_amount), 0) as total_tagihan"),
                DB::raw("COALESCE(SUM(CASE WHEN status IN ('unpaid','partial','pending') THEN (total_amount - paid_amount) ELSE 0 END), 0) as belum_bayar"),
                DB::raw("COALESCE(SUM(CASE WHEN status = 'paid' THEN total_amount ELSE 0 END), 0) as lunas"),
                DB::raw("COALESCE(SUM(CASE WHEN status = 'overdue' THEN (total_amount - paid_amount) ELSE 0 END), 0) as overdue"),
            ])
            ->from(DB::raw("({$base->toSql()}) as inv_base"))
            ->mergeBindings($base->getQuery())
            ->first();

        return [
            'total_tagihan' => (float)($agg->total_tagihan ?? 0),
            'belum_bayar' => (float)($agg->belum_bayar ?? 0),
            'lunas' => (float)($agg->lunas ?? 0),
            'overdue' => (float)($agg->overdue ?? 0),
        ];
    }

    public function generateForPeriod(int $tahun, int $bulan, int $userId): array
    {
        return DB::transaction(function () use ($tahun, $bulan, $userId) {
            $subs = Subscription::with(['customer', 'customerService', 'customerService.serviceProfile'])
                ->where('status', 'active')
                ->get();

            $createdIds = collect();
            $totalAmount = 0;

            foreach ($subs as $sub) {
                try {
                    $invoice = $this->ensureInvoiceForPeriod($sub, $tahun, $bulan, $userId);
                    if ($invoice) {
                        $createdIds->push($invoice->id);
                        $totalAmount += (float) $invoice->total_amount;
                    }
                } catch (\Throwable $e) {
                    Log::warning('Generate invoice periode gagal', [
                        'sub_id' => $sub->id,
                        'err' => $e->getMessage(),
                    ]);
                }
            }

            try {
                Event::dispatch(new InvoiceBatchGeneratedEvent(
                    tahun: $tahun,
                    bulan: $bulan,
                    invoiceIds: $createdIds,
                    generatedByUserId: $userId,
                    totalGenerated: $createdIds->count(),
                    totalAmount: $totalAmount,
                ));
            } catch (\Throwable $e) {
                Log::warning('InvoiceBatchGeneratedEvent dispatch failed', ['err' => $e->getMessage()]);
            }

            return [
                'count' => $createdIds->count(),
                'ids' => $createdIds->all(),
                'total_amount' => $totalAmount,
            ];
        });
    }

    protected function ensureInvoiceForPeriod($sub, int $tahun, int $bulan, int $userId)
    {
        $start = \Illuminate\Support\Carbon::create($tahun, $bulan, 1, 0, 0, 0);
        $end = $start->copy()->endOfMonth();

        $exists = Invoice::where('customer_id', $sub->customer_id)
            ->whereYear('issue_date', $tahun)
            ->whereMonth('issue_date', $bulan)
            ->first();
        if ($exists) {
            return null;
        }

        $profile = $sub->customerService?->serviceProfile;
        $amount = (float)($profile?->base_price ?? $profile?->promo_price ?? 0);
        if ($amount <= 0) {
            return null;
        }

        $invoiceNumber = 'INV-' . $tahun . str_pad($bulan, 2, '0', STR_PAD_LEFT)
            . '-' . str_pad(Invoice::count() + 1, 6, '0', STR_PAD_LEFT);

        $invoice = $this->invoiceRepository->create([
            'uuid' => (string) Str::uuid(),
            'customer_id' => $sub->customer_id,
            'contract_id' => $sub->contract_id ?? null,
            'invoice_number' => $invoiceNumber,
            'issue_date' => $start,
            'due_date' => $start->copy()->addDays(7),
            'total_amount' => $amount,
            'paid_amount' => 0,
            'currency' => 'IDR',
            'status' => 'unpaid',
            'created_by' => $userId,
            'updated_by' => $userId,
        ]);

        $this->invoiceItemRepository->create([
            'uuid' => (string) Str::uuid(),
            'invoice_id' => $invoice->id,
            'description' => 'Tagihan ' . $this->periodeLabel($tahun, $bulan)
                . ' - ' . ($profile?->name ?? 'Paket Langganan'),
            'quantity' => 1,
            'unit_price' => $amount,
            'subtotal' => $amount,
            'package_id' => $profile?->id ?? null,
            'created_by' => $userId,
            'updated_by' => $userId,
        ]);

        return $invoice;
    }

    public function sendWaBulk(array $invoiceIds): int
    {
        $count = 0;
        $invoices = Invoice::with('customer')->whereIn('id', $invoiceIds)->get();
        foreach ($invoices as $inv) {
            if (!empty($inv->customer?->phone)) {
                $count++;
            }
        }
        return $count;
    }

    public function exportCsv($rows): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $filename = 'periode_tagihan_' . date('Ymd_His') . '.csv';
        $path = storage_path('app/private/' . $filename);
        $handle = fopen($path, 'w+');
        fputcsv($handle, [
            'Periode', 'Jumlah Invoice', 'Total Tagihan',
            'Sudah Dibayar', 'Belum Dibayar', 'Lunas (%)', 'Overdue',
        ]);
        foreach ($rows as $r) {
            fputcsv($handle, [
                $r->label_periode ?? ($r->tahun . '-' . $r->bulan),
                $r->jumlah_invoice,
                (string)$r->total_tagihan,
                (string)$r->sudah_dibayar,
                (string)$r->belum_dibayar,
                ($r->lunas_pct ?? 0) . '%',
                (string)($r->overdue_count ?? 0),
            ]);
        }
        fclose($handle);
        return response()->download($path, $filename)->deleteFileAfterSend(true);
    }
}
