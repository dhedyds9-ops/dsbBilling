<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Cache;

class GlobalSearch extends Component
{
    public string $query = '';
    public bool $isOpen = false;
    public array $results = [];
    public string $selectedCategory = 'all';
    public int $limit = 10;

    protected $listeners = ['openSearch' => 'open', 'closeSearch' => 'close'];

    public function open(): void
    {
        $this->isOpen = true;
        $this->query = '';
        $this->results = [];
    }

    public function close(): void
    {
        $this->isOpen = false;
        $this->query = '';
        $this->results = [];
    }

    public function updatedQuery(): void
    {
        if (strlen($this->query) < 2) {
            $this->results = [];
            return;
        }

        $this->search();
    }

    public function search(): void
    {
        $cacheKey = "search:{$this->query}:{$this->selectedCategory}";

        $this->results = Cache::remember($cacheKey, 60, function () {
            return $this->performSearch();
        });
    }

    protected function performSearch(): array
    {
        $results = [];
        $user = auth()->user();
        
        $isCustomer = false;
        $customerId = 0;
        
        if ($user && $user->customer) {
            $isCustomer = true;
            $customerId = $user->customer->id;
        }

        // Only admins search customers
        if (!$isCustomer && ($this->selectedCategory === 'all' || $this->selectedCategory === 'customers')) {
            $customers = \App\Models\CRM\Customer::where('name', 'like', '%' . $this->query . '%')
                ->orWhere('email', 'like', '%' . $this->query . '%')
                ->limit($this->limit)->get();
            foreach ($customers as $customer) {
                $results[] = [
                    'type' => 'customer',
                    'title' => $customer->name,
                    'subtitle' => $customer->email,
                    'url' => "#",
                    'icon' => 'user',
                ];
            }
        }

        // Search Invoices
        if ($this->selectedCategory === 'all' || $this->selectedCategory === 'invoices') {
            $query = \App\Models\Billing\Invoice::where('invoice_number', 'like', '%' . $this->query . '%');
            if ($isCustomer) {
                $query->where('customer_id', $customerId);
            }
            $invoices = $query->limit($this->limit)->get();
            
            foreach ($invoices as $invoice) {
                $results[] = [
                    'type' => 'invoice',
                    'title' => $invoice->invoice_number,
                    'subtitle' => 'Rp ' . number_format($invoice->total_amount, 0, ',', '.'),
                    'url' => $isCustomer ? route('customer-portal.billing.invoice-show', $invoice->id) : route('billing.invoices.show', $invoice->id),
                    'icon' => 'file-text',
                ];
            }
        }

        // Search Tickets
        if ($this->selectedCategory === 'all' || $this->selectedCategory === 'tickets') {
            $query = \App\Models\Support\Ticket::where('title', 'like', '%' . $this->query . '%');
            if ($isCustomer) {
                $query->where('customer_id', $user->id);
            }
            $tickets = $query->limit($this->limit)->get();
            
            foreach ($tickets as $ticket) {
                $results[] = [
                    'type' => 'ticket',
                    'title' => $ticket->title,
                    'subtitle' => 'Status: ' . ucfirst($ticket->status),
                    'url' => "#",
                    'icon' => 'support',
                ];
            }
        }

        return $results;
    }

    public function goToResult(string $url)
    {
        $this->close();
        return redirect($url);
    }

    public function render()
    {
        return view('livewire.global-search');
    }
}
