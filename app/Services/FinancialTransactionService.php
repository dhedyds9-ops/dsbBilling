<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Finance\CashAccount;
use App\Models\Finance\CashTransaction;
use App\Models\Finance\ChartOfAccount;
use App\Models\Finance\JournalEntry;
use App\Models\Finance\MemberIncome;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FinancialTransactionService
{
    protected GeneralLedgerService $ledgerService;
    protected RevenueSharingEngineService $revenueSharingEngine;

    public function __construct(GeneralLedgerService $ledgerService, RevenueSharingEngineService $revenueSharingEngine)
    {
        $this->ledgerService = $ledgerService;
        $this->revenueSharingEngine = $revenueSharingEngine;
    }

    public function createMemberIncome(array $data, User $user): MemberIncome
    {
        return DB::transaction(function () use ($data, $user) {
            $data['date'] = $data['transaction_date'];
            $data['period'] = date('Y-m', strtotime($data['transaction_date']));
            $data['code'] = 'MI-' . date('YmdHis');
            $data['created_by'] = $user->id;
            $data['updated_by'] = $user->id;

            $income = MemberIncome::create($data);

            $this->logAudit($income, 'created', null, $income->toArray(), $user);

            if ($data['status'] === 'posted') {
                $this->postMemberIncome($income, $user);
                $this->revenueSharingEngine->markTransactionsChanged($income->period);
            }

            return $income;
        });
    }

    public function updateMemberIncome(MemberIncome $income, array $data, User $user): MemberIncome
    {
        return DB::transaction(function () use ($income, $data, $user) {
            $oldStatus = $income->status;
            $oldPeriod = $income->period;
            $oldValues = $income->toArray();

            $data['date'] = $data['transaction_date'];
            $data['period'] = date('Y-m', strtotime($data['transaction_date']));
            $data['updated_by'] = $user->id;

            if ($oldStatus === 'posted') {
                $this->reverseMemberIncome($income, $user);
                $this->revenueSharingEngine->markTransactionsChanged($oldPeriod);
            }

            $income->update($data);

            $this->logAudit($income, 'updated', $oldValues, $income->toArray(), $user);

            if ($data['status'] === 'posted') {
                $this->postMemberIncome($income, $user);
                $this->revenueSharingEngine->markTransactionsChanged($income->period);
            }

            return $income;
        });
    }

    public function deleteMemberIncome(MemberIncome $income, User $user): void
    {
        DB::transaction(function () use ($income, $user) {
            $oldPeriod = $income->period;
            if ($income->status === 'posted') {
                $this->reverseMemberIncome($income, $user);
                $this->revenueSharingEngine->markTransactionsChanged($oldPeriod);
            }

            $oldValues = $income->toArray();
            $income->delete();
            $this->logAudit($income, 'deleted', $oldValues, null, $user);
        });
    }

    protected function postMemberIncome(MemberIncome $income, User $user): void
    {
        // Tidak mengubah saldo kas dan tidak membuat jurnal otomatis
    }

    protected function reverseMemberIncome(MemberIncome $income, User $user): void
    {
        // Tidak mengubah saldo kas dan tidak membalik jurnal
    }

    public function createCashTransaction(array $data, User $user): CashTransaction
    {
        return DB::transaction(function () use ($data, $user) {
            $data['code'] = 'CT-' . date('YmdHis');
            $data['date'] = $data['transaction_date'];
            $data['created_by'] = $user->id;
            $data['updated_by'] = $user->id;

            $transaction = CashTransaction::create($data);
            $period = date('Y-m', strtotime($data['transaction_date']));

            $this->logAudit($transaction, 'created', null, $transaction->toArray(), $user);

            if ($data['status'] === 'posted') {
                $this->postCashTransaction($transaction, $user);
                $this->revenueSharingEngine->markTransactionsChanged($period);
            }

            return $transaction;
        });
    }

    public function updateCashTransaction(CashTransaction $transaction, array $data, User $user): CashTransaction
    {
        return DB::transaction(function () use ($transaction, $data, $user) {
            $oldStatus = $transaction->status;
            $oldData = $transaction->toArray();
            $oldPeriod = date('Y-m', strtotime($transaction->date));

            $data['date'] = $data['transaction_date'];
            $data['updated_by'] = $user->id;
            $newPeriod = date('Y-m', strtotime($data['transaction_date']));

            if ($oldStatus === 'posted') {
                $this->reverseCashTransaction($transaction, $oldData, $user);
                $this->revenueSharingEngine->markTransactionsChanged($oldPeriod);
            }

            $transaction->update($data);

            $this->logAudit($transaction, 'updated', $oldData, $transaction->toArray(), $user);

            if ($data['status'] === 'posted') {
                $this->postCashTransaction($transaction, $user);
                $this->revenueSharingEngine->markTransactionsChanged($newPeriod);
            }

            return $transaction;
        });
    }

    public function deleteCashTransaction(CashTransaction $transaction, User $user): void
    {
        DB::transaction(function () use ($transaction, $user) {
            $period = date('Y-m', strtotime($transaction->date));
            if ($transaction->status === 'posted') {
                $oldData = $transaction->toArray();
                $this->reverseCashTransaction($transaction, $oldData, $user);
                $this->revenueSharingEngine->markTransactionsChanged($period);
            }

            $oldValues = $transaction->toArray();
            $transaction->delete();
            $this->logAudit($transaction, 'deleted', $oldValues, null, $user);
        });
    }

    protected function postCashTransaction(CashTransaction $transaction, User $user): void
    {
        $account = CashAccount::lockForUpdate()->findOrFail($transaction->cash_account_id);
        $lines = [];
        $cashAccount = ChartOfAccount::where('code', '1010')->first();

        if ($transaction->type === 'income') {
            $account->balance += $transaction->amount;
            $revenueAccount = ChartOfAccount::where('code', '4010')->first() ?? $cashAccount;
            $lines = [
                ['chart_of_account_id' => $cashAccount?->id, 'type' => 'debit', 'amount' => $transaction->amount],
                ['chart_of_account_id' => $revenueAccount?->id, 'type' => 'credit', 'amount' => $transaction->amount],
            ];
        } elseif ($transaction->type === 'expense') {
            $account->balance -= $transaction->amount;
            $expenseAccount = ChartOfAccount::where('code', '5010')->first() ?? $cashAccount;
            $lines = [
                ['chart_of_account_id' => $expenseAccount?->id, 'type' => 'debit', 'amount' => $transaction->amount],
                ['chart_of_account_id' => $cashAccount?->id, 'type' => 'credit', 'amount' => $transaction->amount],
            ];
        } elseif ($transaction->type === 'transfer' && $transaction->related_cash_account_id) {
            $relatedAccount = CashAccount::lockForUpdate()->findOrFail($transaction->related_cash_account_id);
            $account->balance -= $transaction->amount;
            $relatedAccount->balance += $transaction->amount;
            $relatedAccount->updated_by = $user->id;
            $relatedAccount->save();
            $lines = [
                ['chart_of_account_id' => $cashAccount?->id, 'type' => 'debit', 'amount' => $transaction->amount],
                ['chart_of_account_id' => $cashAccount?->id, 'type' => 'credit', 'amount' => $transaction->amount],
            ];
        }

        $account->updated_by = $user->id;
        $account->save();

        if ($cashAccount && !empty($lines)) {
            $this->ledgerService->createJournalEntry([
                'date' => $transaction->date,
                'journal_code' => 'JRN-CASH',
                'description' => "Cash Transaction #{$transaction->code}",
                'transactionable_type' => CashTransaction::class,
                'transactionable_id' => $transaction->id,
                'lines' => $lines,
            ], $user);
        }
    }

    protected function reverseCashTransaction(CashTransaction $transaction, array $oldData, User $user): void
    {
        try {
            if (isset($oldData['cash_account_id'])) {
                $account = CashAccount::lockForUpdate()->find($oldData['cash_account_id']);
                if ($account) {
                    if ($oldData['type'] === 'income') {
                        $account->balance -= $oldData['amount'];
                    } elseif ($oldData['type'] === 'expense') {
                        $account->balance += $oldData['amount'];
                    } elseif ($oldData['type'] === 'transfer' && isset($oldData['related_cash_account_id'])) {
                        $relatedAccount = CashAccount::lockForUpdate()->find($oldData['related_cash_account_id']);
                        if ($relatedAccount) {
                            $account->balance += $oldData['amount'];
                            $relatedAccount->balance -= $oldData['amount'];
                            $relatedAccount->updated_by = $user->id;
                            $relatedAccount->save();
                        }
                    }

                    $account->updated_by = $user->id;
                    $account->save();
                }
            }
        } catch (\Exception $e) {
            Log::error('Reverse cash transaction failed: ' . $e->getMessage(), ['transaction_id' => $transaction->id]);
        }

        $journalEntry = JournalEntry::where('transactionable_type', CashTransaction::class)
            ->where('transactionable_id', $transaction->id)
            ->where('status', 'posted')
            ->first();

        if ($journalEntry) {
            try {
                $this->ledgerService->reverseJournalEntry($journalEntry, $user, 'Cash Transaction Updated/Deleted');
            } catch (\Exception $e) {
                Log::error('Reverse journal entry failed: ' . $e->getMessage(), ['journal_id' => $journalEntry->id]);
            }
        }
    }

    protected function logAudit($model, string $event, ?array $oldValues, ?array $newValues, User $user): void
    {
        try {
            AuditLog::create([
                'auditable_type' => get_class($model),
                'auditable_id' => $model->id,
                'event' => $event,
                'old_values' => $oldValues,
                'new_values' => $newValues,
                'user_id' => $user->id,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        } catch (\Exception $e) {
            Log::error('Audit log failed: ' . $e->getMessage(), [
                'model' => get_class($model),
                'id' => $model->id,
            ]);
        }
    }
}
