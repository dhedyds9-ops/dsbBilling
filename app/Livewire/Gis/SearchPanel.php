<?php

namespace App\Livewire\Gis;

use Livewire\Component;

class SearchPanel extends Component
{
    public string $query = '';
    public array $results = [];
    public bool $isSearching = false;
    public string $searchType = 'all';
    
    public array $recentSearches = [];

    protected $listeners = [
        'performSearch',
    ];

    public function updatedQuery($value)
    {
        if (strlen($value) >= 2) {
            $this->search();
        } else {
            $this->results = [];
        }
    }

    public function search()
    {
        $this->isSearching = true;
        
        $query = strtolower($this->query);

        $this->results = [
            [
                'type' => 'olt',
                'id' => 'OLT-001',
                'name' => 'OLT Central Jakarta',
                'location' => 'Jakarta Pusat',
                'status' => 'active',
            ],
            [
                'type' => 'odp',
                'id' => 'ODP-042',
                'name' => 'ODP Kebayoran',
                'location' => 'Jakarta Selatan',
                'status' => 'warning',
            ],
            [
                'type' => 'customer',
                'id' => 'CUST-12345',
                'name' => 'PTABC Indonesia',
                'location' => 'Jakarta',
                'status' => 'active',
            ],
        ];

        $this->isSearching = false;
    }

    public function selectResult($result)
    {
        $this->addToRecentSearches($result);
        $this->dispatch('navigateToNode', nodeId: $result['id'], nodeType: $result['type']);
        $this->query = '';
        $this->results = [];
    }

    public function clearSearch()
    {
        $this->query = '';
        $this->results = [];
    }

    public function useRecentSearch($query)
    {
        $this->query = $query;
        $this->search();
    }

    private function addToRecentSearches($result)
    {
        $search = [
            'query' => $result['name'],
            'type' => $result['type'],
            'timestamp' => now()->toIso8601String(),
        ];

        array_unshift($this->recentSearches, $search);
        $this->recentSearches = array_slice($this->recentSearches, 0, 5);
    }

    public function performSearch($query)
    {
        $this->query = $query;
        $this->search();
    }

    public function render()
    {
        return view('livewire.gis.search-panel');
    }
}
