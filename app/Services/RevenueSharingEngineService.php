<?php

namespace App\Services;

use App\Models\Finance\CashTransaction;
use App\Models\Finance\MemberIncome;
use App\Models\Finance\RevenueShareBatch;
use App\Models\Finance\RevenueShareItem;
use App\Models\Master\ExpenseCategory;
use App\Models\Master\Member;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RevenueSharingEngineService
{
    public function calculatePreview(string $period): array
    {
        $yearMonth = explode('-', $period);
        $year = $yearMonth[0];
        $month = $yearMonth[1];

        // Get all active members
        $members = Member::where('status', 'active')->get();

        // Calculate total revenue
        $totalRevenue = MemberIncome::where('period', $period)
            ->where('status', 'posted')
            ->sum('amount');

        // Calculate total expense (only affects_revenue_sharing = true)
        $expenseCategoryIds = ExpenseCategory::where('affects_revenue_sharing', true)->pluck('id');
        $totalExpense = CashTransaction::where('type', 'expense')
            ->where('status', 'posted')
            ->whereIn('expense_category_id', $expenseCategoryIds)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->sum('amount');

        $items = [];
        $totalDistributed = 0;

        foreach ($members as $member) {
            $memberRevenue = MemberIncome::where('member_id', $member->id)
                ->where('period', $period)
                ->where('status', 'posted')
                ->sum('amount');

            if ($totalRevenue > 0) {
                $percentage = ($memberRevenue / $totalRevenue) * 100;
            } else {
                $percentage = 0;
            }

            $expenseShare = ($percentage / 100) * $totalExpense;
            $netShare = max(0, $memberRevenue - $expenseShare);

            $items[] = [
                'member' => $member,
                'member_revenue' => $memberRevenue,
                'percentage' => $percentage,
                'expense_share' => $expenseShare,
                'net_share' => $netShare,
            ];

            $totalDistributed += $netShare;
        }

        return [
            'period' => $period,
            'total_revenue' => $totalRevenue,
            'total_expense' => $totalExpense,
            'total_distributed' => $totalDistributed,
            'items' => $items,
        ];
    }

    public function generateBatch(string $period, User $user): RevenueShareBatch
    {
        return DB::transaction(function () use ($period, $user) {
            // Check if there's already a batch for this period
            $existingBatch = RevenueShareBatch::where('period', $period)->first();
            if ($existingBatch) {
                $existingBatch->items()->delete();
                $existingBatch->delete();
            }

            $preview = $this->calculatePreview($period);

            $batch = RevenueShareBatch::create([
                'batch_number' => 'RSB-' . date('YmdHis'),
                'period' => $period,
                'total_revenue' => $preview['total_revenue'],
                'total_expense' => $preview['total_expense'],
                'total_distributed' => $preview['total_distributed'],
                'status' => 'generated',
                'generated_by' => $user->id,
                'generated_at' => now(),
                'transactions_changed' => false,
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ]);

            foreach ($preview['items'] as $item) {
                RevenueShareItem::create([
                    'batch_id' => $batch->id,
                    'member_id' => $item['member']->id,
                    'member_revenue' => $item['member_revenue'],
                    'percentage' => $item['percentage'],
                    'expense_share' => $item['expense_share'],
                    'net_share' => $item['net_share'],
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                ]);
            }

            // Log audit trail
            $this->logAudit($batch, 'generate_batch', $user, null, $batch->toArray());

            return $batch;
        });
    }

    public function approveBatch(RevenueShareBatch $batch, User $user): RevenueShareBatch
    {
        return DB::transaction(function () use ($batch, $user) {
            $oldStatus = $batch->status;
            $batch->update([
                'status' => 'approved',
                'approved_by' => $user->id,
                'approved_at' => now(),
                'updated_by' => $user->id,
            ]);

            $this->logAudit($batch, 'approve_batch', $user, ['status' => $oldStatus], ['status' => 'approved']);

            return $batch;
        });
    }

    public function lockBatch(RevenueShareBatch $batch, User $user): RevenueShareBatch
    {
        return DB::transaction(function () use ($batch, $user) {
            $oldStatus = $batch->status;
            $batch->update([
                'status' => 'locked',
                'locked_by' => $user->id,
                'locked_at' => now(),
                'updated_by' => $user->id,
            ]);

            $this->logAudit($batch, 'lock_batch', $user, ['status' => $oldStatus], ['status' => 'locked']);

            return $batch;
        });
    }

    public function markTransactionsChanged(string $period): void
    {
        RevenueShareBatch::where('period', $period)
            ->whereIn('status', ['generated', 'approved', 'paid'])
            ->update(['transactions_changed' => true]);
    }

    protected function logAudit(RevenueShareBatch $batch, string $action, User $user, ?array $oldValues, ?array $newValues): void
    {
        // Use existing AuditLog model or create audit trail
        if (class_exists(\App\Models\AuditLog::class)) {
            \App\Models\AuditLog::create([
                'auditable_type' => RevenueShareBatch::class,
                'auditable_id' => $batch->id,
                'event' => $action,
                'old_values' => $oldValues,
                'new_values' => $newValues,
                'user_id' => $user->id,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'created_at' => now(),
            ]);
        } else {
            Log::info('Revenue Sharing Audit', [
                'batch_id' => $batch->id,
                'event' => $action,
                'user_id' => $user->id,
                'old_values' => $oldValues,
                'new_values' => $newValues,
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        }
    }
}
