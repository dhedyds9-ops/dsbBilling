<?php

namespace App\Livewire\Pengaturan\PaymentGateway;

use App\Livewire\AdminComponent;

class History extends AdminComponent
{
    public string $activeModule = 'pengaturan';
    public string $activePage = 'payment-gateway';

    public $search = '';
    public $statusFilter = '';

    public array $payments = [
        ['id' => 'TRX-1001', 'invoice_no' => 'INV-202308001', 'gateway' => 'Midtrans', 'amount' => 250000, 'status' => 'settlement', 'date' => '2023-08-01 10:15'],
        ['id' => 'TRX-1002', 'invoice_no' => 'INV-202308002', 'gateway' => 'Xendit', 'amount' => 150000, 'status' => 'pending', 'date' => '2023-08-02 11:30'],
        ['id' => 'TRX-1003', 'invoice_no' => 'INV-202308003', 'gateway' => 'Tripay', 'amount' => 300000, 'status' => 'expire', 'date' => '2023-08-03 14:00'],
    ];

    public $showDetailModal = false;
    public $selectedPayment = null;

    public function mount()
    {
        parent::mount();
        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Pengaturan', 'url' => '#'],
            ['label' => 'Payment Gateway', 'url' => route('pengaturan.payment-gateway')],
            ['label' => 'Payment History'],
        ];
    }

    public function openDetail($index)
    {
        $this->selectedPayment = collect($this->payments)->firstWhere('id', $index);
        $this->showDetailModal = true;
    }

    public function checkStatus($id)
    {
        session()->flash('success', "Status untuk transaksi $id berhasil dicek ulang ke Payment Gateway.");
        $this->showDetailModal = false;
    }

    public function verifyPayment($id)
    {
        session()->flash('success', "Transaksi $id berhasil diverifikasi secara manual.");
        $this->showDetailModal = false;
    }

    public function render()
    {
        $filtered = collect($this->payments)->filter(function($item) {
            $matchSearch = str_contains(strtolower($item['invoice_no']), strtolower($this->search)) ||
                           str_contains(strtolower($item['id']), strtolower($this->search));
            $matchStatus = $this->statusFilter ? $item['status'] === $this->statusFilter : true;
            return $matchSearch && $matchStatus;
        })->toArray();

        return view('livewire.pengaturan.payment-gateway.history', [
            'filteredPayments' => $filtered,
        ]);
    }
}
