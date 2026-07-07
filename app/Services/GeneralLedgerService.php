<?php

namespace App\Services;

use App\Models\Finance\ChartOfAccount;
use App\Models\Finance\Journal;
use App\Models\Finance\JournalEntry;
use App\Models\Finance\JournalEntryLine;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GeneralLedgerService
{
    public function createJournalEntry(array $data, User $user): JournalEntry
    {
        return DB::transaction(function () use ($data, $user) {
            // Validate that debits equal credits
            $totalDebit = collect($data['lines'])->where('type', 'debit')->sum('amount');
            $totalCredit = collect($data['lines'])->where('type', 'credit')->sum('amount');

            if (abs($totalDebit - $totalCredit) > 0.01) {
                throw new Exception("Total Debit must equal Total Credit!");
            }

            // Find or get default journal
            $journal = Journal::where('code', $data['journal_code'] ?? 'JRN-GENERAL')->first();
            if (!$journal) {
                $journal = Journal::first();
            }

            $journalEntry = JournalEntry::create([
                'code' => 'JE-' . date('YmdHis') . '-' . rand(1000, 9999),
                'date' => $data['date'],
                'journal_id' => $journal->id,
                'description' => $data['description'] ?? null,
                'status' => 'posted',
                'transactionable_type' => $data['transactionable_type'] ?? null,
                'transactionable_id' => $data['transactionable_id'] ?? null,
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ]);

            foreach ($data['lines'] as $line) {
                JournalEntryLine::create([
                    'journal_entry_id' => $journalEntry->id,
                    'chart_of_account_id' => $line['chart_of_account_id'],
                    'type' => $line['type'],
                    'amount' => $line['amount'],
                    'description' => $line['description'] ?? null,
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                ]);
            }

            return $journalEntry;
        });
    }

    public function reverseJournalEntry(JournalEntry $journalEntry, User $user, string $notes = ''): JournalEntry
    {
        return DB::transaction(function () use ($journalEntry, $user, $notes) {
            $reversalLines = [];
            foreach ($journalEntry->lines as $line) {
                $reversalLines[] = [
                    'chart_of_account_id' => $line->chart_of_account_id,
                    'type' => $line->type === 'debit' ? 'credit' : 'debit',
                    'amount' => $line->amount,
                    'description' => 'Reversal of JE #' . $journalEntry->code,
                ];
            }

            return $this->createJournalEntry([
                'date' => now(),
                'journal_code' => 'JRN-GENERAL',
                'description' => "Reversal of Journal Entry {$journalEntry->code}: {$notes}",
                'lines' => $reversalLines,
            ], $user);
        });
    }

    public function getTrialBalance(string $startDate, string $endDate): array
    {
        $accounts = ChartOfAccount::with(['journalEntryLines' => function ($query) use ($startDate, $endDate) {
            $query->whereHas('journalEntry', function ($q) use ($startDate, $endDate) {
                $q->whereBetween('date', [$startDate, $endDate])->where('status', 'posted');
            });
        }])->orderBy('code')->get();

        $totalDebit = 0;
        $totalCredit = 0;

        $trialBalance = [];

        foreach ($accounts as $account) {
            $debit = 0;
            $credit = 0;

            foreach ($account->journalEntryLines as $line) {
                if ($line->type === 'debit') {
                    $debit += $line->amount;
                } else {
                    $credit += $line->amount;
                }
            }

            $balance = $account->normal_balance === 'debit'
                ? $debit - $credit
                : $credit - $debit;

            $trialBalance[] = [
                'code' => $account->code,
                'name' => $account->name,
                'type' => $account->account_type,
                'debit' => number_format($debit, 0, ',', '.'),
                'credit' => number_format($credit, 0, ',', '.'),
                'balance' => number_format($balance, 0, ',', '.'),
            ];

            $totalDebit += $debit;
            $totalCredit += $credit;
        }

        return [
            'accounts' => $trialBalance,
            'total_debit' => number_format($totalDebit, 0, ',', '.'),
            'total_credit' => number_format($totalCredit, 0, ',', '.'),
            'is_balanced' => abs($totalDebit - $totalCredit) < 0.01,
        ];
    }

    public function getProfitLoss(string $startDate, string $endDate): array
    {
        $revenueAccounts = ChartOfAccount::whereIn('account_type', ['revenue', 'other_income'])
            ->with(['journalEntryLines' => function ($query) use ($startDate, $endDate) {
                $query->whereHas('journalEntry', fn($q) => $q->whereBetween('date', [$startDate, $endDate])->where('status', 'posted'));
            }])->get();

        $expenseAccounts = ChartOfAccount::whereIn('account_type', ['expense', 'other_expense'])
            ->with(['journalEntryLines' => function ($query) use ($startDate, $endDate) {
                $query->whereHas('journalEntry', fn($q) => $q->whereBetween('date', [$startDate, $endDate])->where('status', 'posted'));
            }])->get();

        $totalRevenue = 0;
        foreach ($revenueAccounts as $account) {
            foreach ($account->journalEntryLines as $line) {
                $totalRevenue += $line->type === 'credit' ? $line->amount : -$line->amount;
            }
        }

        $totalExpenses = 0;
        foreach ($expenseAccounts as $account) {
            foreach ($account->journalEntryLines as $line) {
                $totalExpenses += $line->type === 'debit' ? $line->amount : -$line->amount;
            }
        }

        return [
            'total_revenue' => number_format($totalRevenue, 0, ',', '.'),
            'total_expenses' => number_format($totalExpenses, 0, ',', '.'),
            'net_profit' => number_format($totalRevenue - $totalExpenses, 0, ',', '.'),
        ];
    }
}
