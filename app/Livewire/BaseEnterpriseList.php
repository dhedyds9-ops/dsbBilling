<?php

namespace App\Livewire;

use Livewire\WithPagination;
use Livewire\Attributes\Url;

abstract class BaseEnterpriseList extends AdminComponent
{
    use WithPagination;

    #[Url]
    public string $search = '';
    public string $sortField = 'created_at';
    public string $sortDirection = 'desc';
    public int $perPage = 25;
    
    #[Url]
    public array $filters = [];
    public bool $showFilters = false;
    public bool $loading = false;
    public ?string $errorMessage = null;

    public array $selected = [];
    public bool $selectAll = false;
    public string $bulkAction = '';

    #[Url]
    public string $activeTab = '';
    public array $tabs = [];

    public string $confirmModal = 'confirm';
    public string $confirmTitle = 'Konfirmasi';
    public string $confirmMessage = 'Apakah Anda yakin?';
    public string $confirmAction = '';
    public array $confirmParams = [];
    public string $confirmBtnText = 'Ya';
    public string $confirmBtnClass = 'bg-red-600 hover:bg-red-700';

    public function mount(): void
    {
        parent::mount();
        if (count($this->tabs) > 0 && $this->activeTab === '') {
            $first = array_key_first($this->tabs);
            $this->activeTab = $first ?? '';
        }
    }

    public function getListeners(): array
    {
        return array_merge(parent::getListeners() ?? [], [
            'sort-by' => 'sortBy',
            'confirm-action' => 'handleConfirm',
            'execute-bulk' => 'executeBulk',
            'refreshPage' => '$refresh',
        ]);
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc';
        }
        $this->sortField = $field;
    }

    public function updatedSearch(string $value = ''): void
    {
        $this->resetPage();
    }

    public function updatedPerPage(int|string $value = 25): void
    {
        $this->resetPage();
    }

    public function updatedActiveTab(string $value = ''): void
    {
        $this->resetPage();
        $this->selected = [];
        $this->selectAll = false;
    }

    public function updatedFilters(mixed $value = null, ?string $key = null): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->filters = [];
        $this->search = '';
        $this->showFilters = false;
        $this->resetPage();
    }

    public function updatedSelectAll(bool $value): void
    {
        if ($value) {
            $query = $this->getRowsQuery();
            $limit = min(500, $this->perPage * 5);
            $ids = $query instanceof \Illuminate\Support\Collection 
                ? $query->take($limit)->pluck('id')->all()
                : $query->limit($limit)->pluck('id')->all();
                
            $this->selected = collect($ids)
                ->map(fn($v) => (string) $v)
                ->all();
        } else {
            $this->selected = [];
        }
    }

    public function updatedSelected(array $value): void
    {
        $query = $this->getRowsQuery();
        $limit = min(500, $this->perPage * 5);
        $ids = $query instanceof \Illuminate\Support\Collection 
            ? $query->take($limit)->pluck('id')->map(fn($v) => (string) $v)->all()
            : $query->limit($limit)->pluck('id')->map(fn($v) => (string) $v)->all();
            
        $this->selectAll = count($value) > 0 && count(array_diff($ids, $value)) === 0;
    }

    public function applyBulk(string $action): void
    {
        if (count($this->selected) === 0) {
            $this->errorMessage = 'Pilih setidaknya satu data terlebih dahulu.';
            return;
        }

        $labels = [
            'delete' => 'menghapus',
            'disable' => 'menonaktifkan',
            'enable' => 'mengaktifkan',
            'sync' => 'menyinkronkan',
            'export' => 'mengekspor',
            'activate' => 'mengaktifkan',
            'suspend' => 'menyuspend',
            'send-wa' => 'mengirim WhatsApp',
            'regenerate' => 'meregenerate',
            'approve' => 'menyetujui',
            'reject' => 'menolak',
        ];

        $verb = $labels[$action] ?? 'memproses';
        $this->confirmTitle = ucfirst($verb) . ' terpilih';
        $this->confirmMessage = "Anda akan {$verb} " . count($this->selected) . " data. Lanjutkan?";
        $this->confirmAction = 'execute-bulk';
        $this->confirmParams = ['action' => $action];
        $this->confirmBtnText = $action === 'delete' ? 'Hapus' : ($action === 'approve' ? 'Setujui' : 'Ya');
        $this->confirmBtnClass = $action === 'delete' || $action === 'reject'
            ? 'bg-red-600 hover:bg-red-700 text-white'
            : 'bg-blue-600 hover:bg-blue-700 text-white';

        $this->dispatch('open-modal', name: $this->confirmModal);
    }

    public function handleConfirm(): void
    {
        if ($this->confirmAction === 'execute-bulk') {
            $this->executeBulk($this->confirmParams['action'] ?? '');
        }
        $this->dispatch('close-modal', name: $this->confirmModal);
        $this->confirmAction = '';
        $this->confirmParams = [];
    }

    public function executeBulk(string $action): void
    {
        if (count($this->selected) === 0) {
            return;
        }

        $count = $this->handleBulkAction($action, $this->selected);

        $messages = [
            'delete' => 'Berhasil menghapus',
            'disable' => 'Berhasil menonaktifkan',
            'enable' => 'Berhasil mengaktifkan',
            'sync' => 'Berhasil menyinkronkan',
            'export' => 'Berhasil mengekspor',
            'activate' => 'Berhasil mengaktifkan',
            'suspend' => 'Berhasil menyuspend',
            'send-wa' => 'WhatsApp dikirim untuk',
            'regenerate' => 'Berhasil meregenerate',
            'approve' => 'Berhasil menyetujui',
            'reject' => 'Berhasil menolak',
        ];

        $prefix = $messages[$action] ?? 'Berhasil memproses';
        $msg = "{$prefix} {$count} data.";
        session()->flash('success', $msg);
        $this->dispatch('toast', type: 'success', message: $msg);

        $this->selected = [];
        $this->selectAll = false;
        $this->bulkAction = '';
        $this->resetPage();
    }

    protected function withLoading(\Closure $fn, string $errMsg = 'Terjadi kesalahan'): mixed
    {
        $this->loading = true;
        $this->errorMessage = null;

        try {
            return $fn();
        } catch (\Throwable $e) {
            $this->errorMessage = $errMsg . ': ' . $e->getMessage();
            report($e);
            return null;
        } finally {
            $this->loading = false;
        }
    }

    public function hasFilter(): bool
    {
        return $this->search !== '' || collect($this->filters)->filter(fn($v) => $v !== '' && $v !== null)->isNotEmpty();
    }

    abstract public function getRowsQuery();
    abstract public function getRows();
    abstract public function handleBulkAction(string $action, array $ids): int;
    abstract public function exportCsv(): \Symfony\Component\HttpFoundation\StreamedResponse|\Symfony\Component\HttpFoundation\BinaryFileResponse|\Illuminate\Http\RedirectResponse;
}
