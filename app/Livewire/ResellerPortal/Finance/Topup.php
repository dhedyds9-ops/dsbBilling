<?php

namespace App\Livewire\ResellerPortal\Finance;

use App\Livewire\AdminComponent;
use App\Models\Payment\Payment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Topup extends AdminComponent
{
    public $amount = '';
    public $method = 'bank_transfer';
    public $notes = '';
    public $showModal = false;
    
    // List state
    public $search = '';
    public $perPage = 10;
    public $status = '';

    protected $rules = [
        'amount' => 'required|numeric|min:10000',
        'method' => 'required|string',
    ];

    protected $messages = [
        'amount.required' => 'Jumlah top up wajib diisi.',
        'amount.min' => 'Minimal top up adalah Rp 10.000',
    ];

    public function mount()
    {
        parent::mount();
        $this->activeModule = 'reseller-portal';
        $this->activePage = 'reseller-portal.finance.topup';
        
        $this->breadcrumbs = [
            ['label' => 'Reseller Portal', 'url' => '#'],
            ['label' => 'Data Keuangan', 'url' => '#'],
            ['label' => 'Top Up', 'url' => route('reseller-portal.finance.topup')],
        ];
    }

    public function submit()
    {
        $this->validate();

        try {
            DB::beginTransaction();

            Payment::create([
                'uuid' => (string) Str::uuid(),
                'customer_id' => Auth::id(), // Reseller ID
                'amount' => $this->amount,
                'currency' => 'IDR',
                'method' => $this->method,
                'status' => 'pending',
                'reference_number' => 'RT-' . now()->format('YmdHis') . '-' . strtoupper(Str::random(6)),
                'gateway' => 'reseller_topup',
                'created_by' => Auth::id(),
                'gateway_transaction_id' => $this->notes, // Store notes here as fallback
            ]);

            DB::commit();

            $this->showModal = false;
            $this->reset(['amount', 'notes']);
            
            // Dispatch event to show success popup
            $this->dispatch('swal:success', [
                'title' => 'Berhasil!',
                'text' => 'Pengajuan Top Up berhasil dikirim. Silakan tunggu konfirmasi Admin.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('swal:error', [
                'title' => 'Gagal!',
                'text' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

    public function render()
    {
        $query = Payment::where('customer_id', Auth::id())
            ->where('gateway', 'reseller_topup');

        if ($this->search) {
            $query->where('reference_number', 'like', '%' . $this->search . '%');
        }

        if ($this->status) {
            $query->where('status', $this->status);
        }

        $topups = $query->orderBy('created_at', 'desc')->paginate($this->perPage);

        return view('livewire.reseller-portal.finance.topup', [
            'topups' => $topups
        ]);
    }
}
