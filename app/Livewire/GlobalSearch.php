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

        // Search Customers
        if ($this->selectedCategory === 'all' || $this->selectedCategory === 'customers') {
            // $customers = Customer::search($this->query)->limit($this->limit)->get();
            // foreach ($customers as $customer) {
            //     $results[] = [
            //         'type' => 'customer',
            //         'title' => $customer->name,
            //         'subtitle' => $customer->email,
            //         'url' => "/customers/{$customer->id}",
            //         'icon' => 'heroicon-o-user',
            //     ];
            // }
        }

        // Search Invoices
        if ($this->selectedCategory === 'all' || $this->selectedCategory === 'invoices') {
            // $invoices = Invoice::search($this->query)->limit($this->limit)->get();
            // foreach ($invoices as $invoice) {
            //     $results[] = [
            //         'type' => 'invoice',
            //         'title' => $invoice->number,
            //         'subtitle' => number_format($invoice->total, 0, ',', '.'),
            //         'url' => "/billing/invoices/{$invoice->id}",
            //         'icon' => 'heroicon-o-document-text',
            //     ];
            // }
        }

        // Search Tickets
        if ($this->selectedCategory === 'all' || $this->selectedCategory === 'tickets') {
            // $tickets = Ticket::search($this->query)->limit($this->limit)->get();
            // foreach ($tickets as $ticket) {
            //     $results[] = [
            //         'type' => 'ticket',
            //         'title' => $ticket->subject,
            //         'subtitle' => $ticket->status,
            //         'url' => "/tickets/{$ticket->id}",
            //         'icon' => 'heroicon-o-support',
            //     ];
            // }
        }

        // Search Assets
        if ($this->selectedCategory === 'all' || $this->selectedCategory === 'assets') {
            // $assets = Asset::search($this->query)->limit($this->limit)->get();
            // foreach ($assets as $asset) {
            //     $results[] = [
            //         'type' => 'asset',
            //         'title' => $asset->name,
            //         'subtitle' => $asset->serial_number,
            //         'url' => "/inventory/assets/{$asset->id}",
            //         'icon' => 'heroicon-o-cube',
            //     ];
            // }
        }

        return $results;
    }

    public function goToResult(string $url): void
    {
        $this->close();
        return redirect($url);
    }

    public function render()
    {
        return view('livewire.global-search');
    }
}
