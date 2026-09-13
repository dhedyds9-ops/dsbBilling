<?php

namespace App\Services\Keuangan;

use App\Models\Keuangan\Expense;
use App\Models\User;
use App\Services\Auth\UserQueryService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Src\Domain\Keuangan\Events\ExpenseApprovedEvent;
use Src\Domain\Keuangan\Events\ExpenseCreatedEvent;

class ExpenseService
{
    public function list(array $filters = [], string $search = '', string $sortField = 'created_at', string $sortDirection = 'desc', string $statusTab = '')
    {
        if (!class_exists(Expense::class)) {
            return collect();
        }

        $query = Expense::with(['requestedBy', 'approvedBy']);

        $statusMap = [
            'all' => null,
            'draft' => 'draft',
            'approval' => 'pending_approval',
            'approved' => 'approved',
            'rejected' => 'rejected',
        ];

        $effectiveStatus = $statusMap[$statusTab] ?? null;
        if ($effectiveStatus !== null) {
            $query->where('status', $effectiveStatus);
        }

        if (!empty($filters['category'])) {
            $query->where('category', $filters['category']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['start_date']) || !empty($filters['end_date'])) {
            $query->betweenDates($filters['start_date'] ?? null, $filters['end_date'] ?? null);
        }

        if (!empty($filters['approved_by'])) {
            $query->where('approved_by', $filters['approved_by']);
        }

        if ($search !== '') {
            $searchTerm = '%' . $search . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('code', 'like', $searchTerm)
                    ->orWhere('description', 'like', $searchTerm)
                    ->orWhereHas('requestedBy', function ($sq) use ($searchTerm) {
                        $sq->where('name', 'like', $searchTerm);
                    });
            });
        }

        return $query->orderBy($sortField, $sortDirection);
    }

    public function summary(): array
    {
        if (!class_exists(Expense::class)) {
            return [
                'monthly_total' => 0,
                'needs_approval' => 0,
                'approved' => 0,
                'spent_this_year' => 0,
            ];
        }

        $monthlyTotal = Expense::thisMonth()->approved()->sum('amount');
        $needsApproval = Expense::needsApproval()->count();
        $approved = Expense::approved()->count();

        $annualBudget = (float) config('keuangan.annual_budget', 500000000);
        $spentThisYear = Expense::approved()
            ->whereBetween('expense_date', [now()->startOfYear(), now()->endOfYear()])
            ->sum('amount');
        $budgetRemaining = max(0, $annualBudget - (float) $spentThisYear);

        return [
            'monthly_total' => (float) $monthlyTotal,
            'needs_approval' => $needsApproval,
            'approved' => $approved,
            'budget_remaining' => $budgetRemaining,
        ];
    }

    public function create(array $data, int $userId): Expense
    {
        return DB::transaction(function () use ($data, $userId) {
            $expense = Expense::create(array_merge($data, [
                'requested_by' => $userId,
                'status' => $data['status'] ?? 'draft',
            ]));

            Event::dispatch(new ExpenseCreatedEvent(
                $expense->uuid,
                $expense->code,
                (float) $expense->amount,
                $expense->category,
                $userId,
            ));

            return $expense;
        });
    }

    public function update(Expense $expense, array $data, int $userId): Expense
    {
        return DB::transaction(function () use ($expense, $data, $userId) {
            $expense->update($data);
            return $expense;
        });
    }

    public function delete(Expense $expense, int $userId): bool
    {
        return DB::transaction(function () use ($expense) {
            return $expense->delete();
        });
    }

    public function approve(int $id, int $userId): Expense
    {
        return DB::transaction(function () use ($id, $userId) {
            $expense = Expense::findOrFail($id);
            $expense->update([
                'status' => 'approved',
                'approved_by' => $userId,
                'approved_at' => now(),
            ]);

            Event::dispatch(new ExpenseApprovedEvent(
                $expense->uuid,
                $expense->code,
                (float) $expense->amount,
                $expense->category,
                $userId,
            ));

            return $expense;
        });
    }

    public function reject(int $id, int $userId, string $reason): Expense
    {
        return DB::transaction(function () use ($id, $userId, $reason) {
            $expense = Expense::findOrFail($id);
            $expense->update([
                'status' => 'rejected',
                'reject_reason' => $reason,
                'approved_by' => $userId,
                'approved_at' => now(),
            ]);
            return $expense;
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

    public function bulkDelete(array $ids, int $userId): int
    {
        $count = 0;
        foreach ($ids as $id) {
            try {
                $expense = Expense::findOrFail($id);
                if ($this->delete($expense, $userId)) {
                    $count++;
                }
            } catch (\Throwable) {
                continue;
            }
        }
        return $count;
    }

    public function exportCsv(array $filters = [], string $search = '', string $statusTab = ''): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $rows = $this->list($filters, $search, 'created_at', 'desc', $statusTab)->limit(10000)->get();

        $statusLabels = [
            'draft' => 'Draft',
            'pending_approval' => 'Butuh Approval',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
        ];
        $categoryLabels = [
            'operasional' => 'Operasional',
            'pegawai' => 'Pegawai',
            'isp_tools' => 'ISP/Tools',
            'marketing' => 'Marketing',
            'lain' => 'Lainnya',
        ];

        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="expense-' . now()->format('YmdHis') . '.csv"',
        ];

        $callback = function () use ($rows, $statusLabels, $categoryLabels) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Tanggal', 'Kode', 'Deskripsi', 'Kategori', 'Jumlah', 'Status', 'Pemohon', 'Penyetuju', 'Catatan']);

            foreach ($rows as $row) {
                fputcsv($handle, [
                    $row->expense_date?->format('d/m/Y') ?? $row->created_at?->format('d/m/Y'),
                    $row->code ?? '',
                    $row->description ?? '',
                    $categoryLabels[$row->category] ?? $row->category ?? '-',
                    (float) $row->amount,
                    $statusLabels[$row->status] ?? ucfirst($row->status ?? ''),
                    $row->requestedBy?->name ?? '-',
                    $row->approvedBy?->name ?? '-',
                    $row->reject_reason ?? '',
                ]);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function findById(int $id): Expense
    {
        return Expense::findOrFail($id);
    }

    public function getCategoryOptions(): array
    {
        return [
            'operasional' => 'Operasional',
            'pegawai' => 'Pegawai',
            'isp_tools' => 'ISP / Tools',
            'marketing' => 'Marketing',
            'lain' => 'Lainnya',
        ];
    }

    /**
     * Dapatkan daftar approver untuk expense.
     * Approver adalah administrator dan manager yang aktif.
     * DILARANG: menggunakan legacy roles finance/treasurer/owner yang tidak terdefinisi.
     * Untuk pembatasan lebih lanjut, gunakan permission 'finance.settlement'.
     */
    public function getApproverOptions(): array
    {
        return app(UserQueryService::class)->getEligibleApproversForDropdown();
    }
}
