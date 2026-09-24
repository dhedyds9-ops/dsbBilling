<?php

namespace App\Services\Keuangan;

use App\Models\Keuangan\ResellerTopup;
use App\Models\Payment\Payment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Src\Domain\Keuangan\Events\TopupApprovedEvent;
use Src\Domain\Keuangan\Events\TopupRejectedEvent;

class ResellerTopupService
{
    public function list(array $filters = [], string $search = '', string $sortField = 'created_at', string $sortDirection = 'desc', string $statusTab = '')
    {
        $query = $this->getBaseQuery($filters, $search, $statusTab);

        return $query->orderBy($sortField, $sortDirection);
    }

    protected function getBaseQuery(array $filters, string $search, string $statusTab)
    {
        if (class_exists(ResellerTopup::class) && $this->hasResellerTopupTable()) {
            $query = ResellerTopup::with(['reseller', 'submittedBy', 'verifiedBy']);
        } else {
            $query = Payment::query()
                ->leftJoin('users as reseller', 'reseller.id', '=', 'payments.customer_id')
                ->leftJoin('users as submitter', 'submitter.id', '=', 'payments.created_by')
                ->select(
                    'payments.*',
                    'reseller.name as reseller_name',
                    'reseller.email as reseller_email',
                    'submitter.name as submitted_by_name',
                    DB::raw('payments.created_by as submitted_by'),
                    DB::raw('payments.updated_by as verified_by'),
                    DB::raw('payments.reference_number as reference_code'),
                    DB::raw("'approved' as status"),
                    DB::raw('payments.method as method'),
                    DB::raw('null as proof_file'),
                    DB::raw('payments.paid_at as verified_at'),
                )
                ->where('payments.gateway', 'reseller_topup');
        }

        if ($statusTab !== '' && $statusTab !== 'all') {
            $query->where('status', $statusTab);
        }

        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['start_date']) || !empty($filters['end_date'])) {
            if (method_exists($query, 'getModel') && $query->getModel() instanceof ResellerTopup) {
                $query->betweenDates($filters['start_date'] ?? null, $filters['end_date'] ?? null);
            } else {
                if (!empty($filters['start_date'])) {
                    $query->where('payments.created_at', '>=', now()->parse($filters['start_date'])->startOfDay());
                }
                if (!empty($filters['end_date'])) {
                    $query->where('payments.created_at', '<=', now()->parse($filters['end_date'])->endOfDay());
                }
            }
        }

        if (!empty($filters['method'])) {
            $query->where('method', $filters['method']);
        }

        if ($search !== '') {
            $searchTerm = '%' . $search . '%';
            $query->where(function ($q) use ($searchTerm) {
                if (method_exists($q, 'getModel') && $q->getModel() instanceof ResellerTopup) {
                    $q->where('reference_code', 'like', $searchTerm)
                        ->orWhereHas('reseller', function ($sq) use ($searchTerm) {
                            $sq->where('name', 'like', $searchTerm)->orWhere('email', 'like', $searchTerm);
                        });
                } else {
                    $q->where('payments.reference_number', 'like', $searchTerm)
                        ->orWhere('reseller.name', 'like', $searchTerm)
                        ->orWhere('reseller.email', 'like', $searchTerm);
                }
            });
        }

        return $query;
    }

    protected function hasResellerTopupTable(): bool
    {
        try {
            return DB::getSchemaBuilder()->hasTable('reseller_topups');
        } catch (\Throwable) {
            return false;
        }
    }

    public function summary(): array
    {
        if (class_exists(ResellerTopup::class) && $this->hasResellerTopupTable()) {
            $monthlyTotal = ResellerTopup::thisMonth()->approved()->sum('amount');
            $pendingCount = ResellerTopup::pending()->count();
            $approvedCount = ResellerTopup::approved()->count();
            $rejectedCount = ResellerTopup::rejected()->count();
        } else {
            $monthlyTotal = Payment::where('gateway', 'reseller_topup')
                ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])
                ->where('status', 'success')
                ->sum('amount');
            $pendingCount = 0;
            $approvedCount = Payment::where('gateway', 'reseller_topup')->where('status', 'success')->count();
            $rejectedCount = Payment::where('gateway', 'reseller_topup')->where('status', 'failed')->count();
        }

        $resellers = User::whereHas('roles', function ($q) {
            $q->where('name', 'reseller');
        })->get();

        $totalSaldo = 0;
        foreach ($resellers as $reseller) {
            $totalSaldo += $this->getResellerBalance($reseller->id);
        }

        return [
            'monthly_total' => $monthlyTotal,
            'pending_count' => $pendingCount,
            'approved_count' => $approvedCount,
            'rejected_count' => $rejectedCount,
            'total_reseller_balance' => $totalSaldo,
        ];
    }

    protected function getResellerBalance(int $userId): float
    {
        if (class_exists(ResellerTopup::class) && $this->hasResellerTopupTable()) {
            $credit = ResellerTopup::where('user_id', $userId)->approved()->sum('amount');
        } else {
            $credit = Payment::where('gateway', 'reseller_topup')
                ->where('customer_id', $userId)
                ->where('status', 'success')
                ->sum('amount');
        }
        $debit = Payment::where('customer_id', $userId)
            ->where('gateway', '!=', 'reseller_topup')
            ->where('status', 'success')
            ->sum('amount');

        return max(0, $credit - $debit);
    }

    public function approve(int $id, int $userId): ?ResellerTopup
    {
        if (!class_exists(ResellerTopup::class) || !$this->hasResellerTopupTable()) {
            return null;
        }

        return DB::transaction(function () use ($id, $userId) {
            $topup = ResellerTopup::findOrFail($id);
            $topup->update([
                'status' => 'approved',
                'verified_by' => $userId,
                'verified_at' => now(),
            ]);

            Event::dispatch(new TopupApprovedEvent(
                $topup->uuid,
                $topup->user_id,
                (float) $topup->amount,
                $userId,
            ));

            return $topup;
        });
    }

    public function reject(int $id, int $userId, string $reason): ?ResellerTopup
    {
        if (!class_exists(ResellerTopup::class) || !$this->hasResellerTopupTable()) {
            return null;
        }

        return DB::transaction(function () use ($id, $userId, $reason) {
            $topup = ResellerTopup::findOrFail($id);
            $topup->update([
                'status' => 'rejected',
                'reject_reason' => $reason,
                'verified_by' => $userId,
                'verified_at' => now(),
            ]);

            Event::dispatch(new TopupRejectedEvent(
                $topup->uuid,
                $topup->user_id,
                (float) $topup->amount,
                $userId,
                $reason,
            ));

            return $topup;
        });
    }

    public function bulkApprove(array $ids, int $userId): int
    {
        $count = 0;
        foreach ($ids as $id) {
            try {
                $this->approve((int) $id, $userId);
                $count++;
            } catch (\Throwable) {
                continue;
            }
        }
        return $count;
    }

    public function bulkReject(array $ids, int $userId, string $reason = 'Ditolak via bulk action'): int
    {
        $count = 0;
        foreach ($ids as $id) {
            try {
                $this->reject((int) $id, $userId, $reason);
                $count++;
            } catch (\Throwable) {
                continue;
            }
        }
        return $count;
    }

    public function exportCsv(array $filters = [], string $search = '', string $statusTab = ''): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $rows = $this->list($filters, $search, 'created_at', 'desc', $statusTab)->limit(10000)->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="reseller-topup-' . now()->format('YmdHis') . '.csv"',
        ];

        $callback = function () use ($rows) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Tanggal', 'Reseller', 'Kode Referensi', 'Jumlah', 'Metode', 'Status', 'Diajukan Oleh', 'Diverifikasi Oleh', 'Catatan']);

            foreach ($rows as $row) {
                $resellerName = $row->reseller->name ?? $row->reseller_name ?? '-';
                $submitterName = $row->submittedBy?->name ?? $row->submitted_by_name ?? '-';
                $verifierName = $row->verifiedBy?->name ?? '-';
                $refCode = $row->reference_code ?? $row->reference_number ?? '-';
                $statusLabel = match ($row->status) {
                    'pending' => 'Pending Approval',
                    'approved', 'success' => 'Disetujui',
                    'rejected', 'failed' => 'Ditolak',
                    default => ucfirst($row->status),
                };

                fputcsv($handle, [
                    $row->created_at?->format('d/m/Y H:i') ?? '',
                    $resellerName,
                    $refCode,
                    (float) $row->amount,
                    ucfirst(str_replace('_', ' ', $row->method ?? '')),
                    $statusLabel,
                    $submitterName,
                    $verifierName,
                    $row->reject_reason ?? '',
                ]);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function findById(int $id): mixed
    {
        if (class_exists(ResellerTopup::class) && $this->hasResellerTopupTable()) {
            return ResellerTopup::findOrFail($id);
        }
        return Payment::where('gateway', 'reseller_topup')->findOrFail($id);
    }

    public function getResellerOptions(): array
    {
        return User::whereHas('roles', function ($q) {
            $q->where('name', 'reseller');
        })->pluck('name', 'id')->toArray();
    }
}
