<?php

namespace App\Livewire\CustomerPortal\Support;

use App\Services\CustomerPortal\CustomerSupportService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.customer-app')]
class TicketCreate extends Component
{
    public $title;
    public $description;
    public $category;
    public $priority;
    public $message;
    public $messageType;

    protected $rules = [
        'title' => 'required',
        'description' => 'required',
        'category' => 'required',
        'priority' => 'required',
    ];

    public function createTicket(CustomerSupportService $supportService)
    {
        $this->validate();

        $customer = auth()->user()->customer;
        if (!$customer) {
            $this->message = "Profil pelanggan tidak ditemukan.";
            $this->messageType = 'error';
            return;
        }

        $result = $supportService->createTicket($customer->id, [
            'title' => $this->title,
            'description' => $this->description,
            'category' => $this->category,
            'priority' => $this->priority,
        ]);

        $this->message = $result['message'];
        $this->messageType = $result['success'] ? 'success' : 'error';

        if ($result['success']) {
            $this->reset(['title', 'description', 'category', 'priority']);
            session()->flash('success', 'Tiket berhasil dibuat.');
            return redirect()->route('customer-portal.support.ticket-list');
        }
    }

    public function render()
    {
        $categories = ['Teknis', 'Billing', 'Layanan', 'Lainnya'];
        $priorities = ['low', 'medium', 'high', 'critical'];
        return view('livewire.customer-portal.support.ticket-create', compact('categories', 'priorities'));
    }
}
