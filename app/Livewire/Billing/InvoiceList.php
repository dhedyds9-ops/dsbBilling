<?php

namespace App\Livewire\Billing;

use App\Livewire\AdminComponent;

class InvoiceList extends AdminComponent
{
    public array $filters = [
        'search' => '',
        'status' => 'all',
        'billing_cycle' => 'all',
        'date_from' => '',
        'date_to' => '',
    ];

    public string $sortField = 'created_at';
    public string $sortDirection = 'desc';
    public int $perPage = 15;
    public array $selected = [];

    public function mount(): void
    {
        parent::mount();
        $this->activeModule = 'billing';
        $this->activePage = 'invoices';
    }

    public function getInvoices(): \Illuminate\Support\Collection
    {
        // Placeholder - dalam implementasi nyata, fetch dari repository
        return collect([
            ['id' => 1, 'number' => 'INV-2024-001', 'customer' => 'John Doe', 'amount' => 500000, 'status' => 'paid', 'due_date' => now()->addDays(7), 'created_at' => now()->subDays(5)],
            ['id' => 2, 'number' => 'INV-2024-002', 'customer' => 'Jane Smith', 'amount' => 350000, 'status' => 'pending', 'due_date' => now()->addDays(14), 'created_at' => now()->subDays(3)],
            ['id' => 3, 'number' => 'INV-2024-003', 'customer' => 'Bob Wilson', 'amount' => 750000, 'status' => 'overdue', 'due_date' => now()->subDays(3), 'created_at' => now()->subDays(20)],
            ['id' => 4, 'number' => 'INV-2024-004', 'customer' => 'Alice Brown', 'amount' => 450000, 'status' => 'paid', 'due_date' => now()->addDays(10), 'created_at' => now()->subDays(2)],
        ]);
    }

    public function getInvoiceStats(): array
    {
        $invoices = $this->getInvoices();
        return [
            'total' => $invoices->count(),
            'total_amount' => $invoices->sum('amount'),
            'paid' => $invoices->where('status', 'paid')->count(),
            'pending' => $invoices->where('status', 'pending')->count(),
            'overdue' => $invoices->where('status', 'overdue')->count(),
            'collection_rate' => 75.5,
        ];
    }

    public function markAsPaid(int $invoiceId): void
    {
        // Logic to mark invoice as paid
    }

    public function sendReminder(int $invoiceId): void
    {
        // Logic to send reminder
    }

    public function render()
    {
        return view('livewire.billing.invoice-list', [
            'invoices' => $this->getInvoices(),
            'stats' => $this->getInvoiceStats(),
        ]);
    }
}
