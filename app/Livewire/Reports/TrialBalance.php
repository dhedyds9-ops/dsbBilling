<?php

namespace App\Livewire\Reports;

use App\Livewire\AdminComponent;

class TrialBalance extends AdminComponent
{
    public array $filters = [
        'start_date' => '',
        'end_date' => '',
    ];

    public function mount(): void
    {
        parent::mount();
        $this->activeModule = 'reports';
        $this->activePage = 'trial-balance';
        $this->filters['start_date'] = now()->startOfMonth()->format('Y-m-d');
        $this->filters['end_date'] = now()->endOfMonth()->format('Y-m-d');
    }

    public function getTrialBalanceData(): array
    {
        return [
            'assets' => [
                'label' => 'Aset',
                'items' => [
                    ['code' => '1101', 'name' => 'Kas', 'debit' => 5000000, 'credit' => 0],
                    ['code' => '1102', 'name' => 'Bank', 'debit' => 25000000, 'credit' => 0],
                    ['code' => '1201', 'name' => 'Piutang Usaha', 'debit' => 15000000, 'credit' => 0],
                    ['code' => '1301', 'name' => 'Peralatan', 'debit' => 10000000, 'credit' => 0],
                ],
                'total_debit' => 55000000,
                'total_credit' => 0,
            ],
            'liabilities' => [
                'label' => 'Kewajiban',
                'items' => [
                    ['code' => '2101', 'name' => 'Hutang Usaha', 'debit' => 0, 'credit' => 8000000],
                    ['code' => '2201', 'name' => 'Hutang Pajak', 'debit' => 0, 'credit' => 2000000],
                ],
                'total_debit' => 0,
                'total_credit' => 10000000,
            ],
            'equity' => [
                'label' => 'Modal',
                'items' => [
                    ['code' => '3101', 'name' => 'Modal Awal', 'debit' => 0, 'credit' => 30000000],
                    ['code' => '3201', 'name' => 'Laba Ditahan', 'debit' => 0, 'credit' => 15000000],
                ],
                'total_debit' => 0,
                'total_credit' => 45000000,
            ],
            'income' => [
                'label' => 'Pendapatan',
                'items' => [
                    ['code' => '4101', 'name' => 'Pendapatan Layanan', 'debit' => 0, 'credit' => 25000000],
                    ['code' => '4201', 'name' => 'Pendapatan Lain-lain', 'debit' => 0, 'credit' => 5000000],
                ],
                'total_debit' => 0,
                'total_credit' => 30000000,
            ],
            'expenses' => [
                'label' => 'Biaya',
                'items' => [
                    ['code' => '5101', 'name' => 'Biaya Gaji', 'debit' => 12000000, 'credit' => 0],
                    ['code' => '5102', 'name' => 'Biaya Listrik', 'debit' => 2000000, 'credit' => 0],
                    ['code' => '5201', 'name' => 'Biaya Operasional', 'debit' => 6000000, 'credit' => 0],
                ],
                'total_debit' => 20000000,
                'total_credit' => 0,
            ],
        ];
    }

    public function getTotals(): array
    {
        $data = $this->getTrialBalanceData();
        $totalDebit = 0;
        $totalCredit = 0;

        foreach ($data as $section) {
            $totalDebit += $section['total_debit'];
            $totalCredit += $section['total_credit'];
        }

        return [
            'debit' => $totalDebit,
            'credit' => $totalCredit,
            'is_balanced' => $totalDebit === $totalCredit,
        ];
    }

    public function render()
    {
        return view('livewire.reports.trial-balance', [
            'trialBalance' => $this->getTrialBalanceData(),
            'totals' => $this->getTotals(),
        ]);
    }
}
