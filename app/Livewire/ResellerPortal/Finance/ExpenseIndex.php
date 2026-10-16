<?php

namespace App\Livewire\ResellerPortal\Finance;

use App\Livewire\AdminComponent;
use App\Models\Keuangan\Expense;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class ExpenseIndex extends AdminComponent
{
    use WithPagination, WithFileUploads;

    public string $search = '';
    public string $filterCategory = '';
    public string $filterStatus = '';
    public string $startDate = '';
    public string $endDate = '';
    public int $perPage = 10;

    // Modal state
    public bool $showModal = false;
    public bool $isEditing = false;
    public ?int $selectedExpenseId = null;

    // Form fields
    public string $expense_date = '';
    public string $category = 'operasional';
    public $amount = 0;
    public string $description = '';
    public string $status = 'approved';
    public $attachment;
    public ?string $existingAttachment = null;

    // Delete modal
    public bool $showDeleteModal = false;
    public ?int $expenseIdToDelete = null;

    protected $paginationTheme = 'tailwind';

    protected function rules(): array
    {
        return [
            'expense_date' => 'required|date',
            'category' => 'required|string|max:50',
            'amount' => 'required|numeric|min:1',
            'description' => 'required|string|max:500',
            'status' => 'required|in:approved,pending_approval,draft',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf,webp|max:3072',
        ];
    }

    protected $validationAttributes = [
        'expense_date' => 'Tanggal Pengeluaran',
        'category' => 'Kategori',
        'amount' => 'Nominal',
        'description' => 'Keterangan',
        'status' => 'Status',
        'attachment' => 'Bukti / Lampiran',
    ];

    public function mount()
    {
        parent::mount();
        $this->activeModule = 'reseller-portal';
        $this->activePage = 'reseller-portal.finance.expenses';

        $this->breadcrumbs = [
            ['label' => 'Reseller Portal', 'url' => '#'],
            ['label' => 'Data Keuangan', 'url' => route('reseller-portal.finance.balance')],
            ['label' => 'Pengeluaran', 'url' => route('reseller-portal.finance.expenses')],
        ];

        $this->expense_date = today()->toDateString();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterCategory()
    {
        $this->resetPage();
    }

    public function updatingFilterStatus()
    {
        $this->resetPage();
    }

    public function updatingStartDate()
    {
        $this->resetPage();
    }

    public function updatingEndDate()
    {
        $this->resetPage();
    }

    public function getCategoriesProperty(): array
    {
        return [
            'operasional' => 'Operasional & Listrik',
            'perawatan' => 'Perawatan & Maintenance',
            'pegawai' => 'Gaji & Upah Teknisi/Karyawan',
            'isp_tools' => 'Alat & Bahan (Kabel, Dropcore, ODP)',
            'sewa' => 'Sewa Tiang / Tempat / Bandwidth',
            'marketing' => 'Promosi & Pemasaran',
            'lain' => 'Lain-lain',
        ];
    }

    public function openCreateModal(): void
    {
        $this->resetErrorBag();
        $this->reset(['selectedExpenseId', 'attachment', 'existingAttachment']);
        $this->isEditing = false;
        $this->expense_date = today()->toDateString();
        $this->category = 'operasional';
        $this->amount = 0;
        $this->description = '';
        $this->status = 'approved';
        $this->showModal = true;
    }

    public function openEditModal(int $id): void
    {
        $this->resetErrorBag();
        $this->reset(['attachment']);
        
        $resellerId = Auth::user()->getEffectiveResellerId() ?? Auth::id();
        $expense = Expense::where('id', $id)
            ->where(function ($q) use ($resellerId) {
                $q->where('reseller_id', $resellerId)->orWhere('requested_by', Auth::id());
            })->firstOrFail();

        $this->selectedExpenseId = $expense->id;
        $this->isEditing = true;
        $this->expense_date = $expense->expense_date ? $expense->expense_date->format('Y-m-d') : today()->toDateString();
        $this->category = $expense->category ?? 'operasional';
        $this->amount = (float) $expense->amount;
        $this->description = $expense->description ?? '';
        $this->status = $expense->status ?? 'approved';
        $this->existingAttachment = $expense->attachment_file;

        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->reset(['selectedExpenseId', 'attachment', 'existingAttachment', 'isEditing']);
    }

    public function save(): void
    {
        $this->validate();

        $user = Auth::user();
        $resellerId = $user->getEffectiveResellerId() ?? $user->id;

        $attachmentPath = $this->existingAttachment;
        if ($this->attachment) {
            $attachmentPath = $this->attachment->store('expenses', 'public');
        }

        if ($this->isEditing && $this->selectedExpenseId) {
            $expense = Expense::where('id', $this->selectedExpenseId)
                ->where(function ($q) use ($resellerId) {
                    $q->where('reseller_id', $resellerId)->orWhere('requested_by', Auth::id());
                })->firstOrFail();

            $expense->update([
                'expense_date' => $this->expense_date,
                'category' => $this->category,
                'amount' => $this->amount,
                'description' => $this->description,
                'status' => $this->status,
                'attachment_file' => $attachmentPath,
            ]);

            session()->flash('success', 'Data pengeluaran berhasil diperbarui.');
        } else {
            Expense::create([
                'reseller_id' => $resellerId,
                'requested_by' => $user->id,
                'expense_date' => $this->expense_date,
                'category' => $this->category,
                'amount' => $this->amount,
                'description' => $this->description,
                'status' => $this->status,
                'attachment_file' => $attachmentPath,
                'approved_by' => $this->status === 'approved' ? $user->id : null,
                'approved_at' => $this->status === 'approved' ? now() : null,
            ]);

            session()->flash('success', 'Pengeluaran baru berhasil dicatat.');
        }

        $this->closeModal();
    }

    public function confirmDelete(int $id): void
    {
        $this->expenseIdToDelete = $id;
        $this->showDeleteModal = true;
    }

    public function cancelDelete(): void
    {
        $this->showDeleteModal = false;
        $this->expenseIdToDelete = null;
    }

    public function deleteExpense(): void
    {
        if (! $this->expenseIdToDelete) {
            return;
        }

        $resellerId = Auth::user()->getEffectiveResellerId() ?? Auth::id();
        $expense = Expense::where('id', $this->expenseIdToDelete)
            ->where(function ($q) use ($resellerId) {
                $q->where('reseller_id', $resellerId)->orWhere('requested_by', Auth::id());
            })->first();

        if ($expense) {
            if ($expense->attachment_file && Storage::disk('public')->exists($expense->attachment_file)) {
                Storage::disk('public')->delete($expense->attachment_file);
            }
            $expense->delete();
            session()->flash('success', 'Data pengeluaran berhasil dihapus.');
        }

        $this->showDeleteModal = false;
        $this->expenseIdToDelete = null;
    }

    public function render()
    {
        $user = Auth::user();
        $resellerId = $user->getEffectiveResellerId() ?? $user->id;

        // Base scoped query
        $query = Expense::query()->where(function ($q) use ($resellerId, $user) {
            $q->where('reseller_id', $resellerId)->orWhere('requested_by', $user->id);
        });

        // Summary Calculations (Real Database Values)
        $monthTotal = (clone $query)
            ->whereMonth('expense_date', now()->month)
            ->whereYear('expense_date', now()->year)
            ->where('status', '!=', 'rejected')
            ->sum('amount');

        $todayTotal = (clone $query)
            ->whereDate('expense_date', today())
            ->where('status', '!=', 'rejected')
            ->sum('amount');

        $totalTransactions = (clone $query)->count();
        $pendingApproval = (clone $query)->where('status', 'pending_approval')->count();

        // Apply filters for listing
        if ($this->search) {
            $searchTerm = '%' . trim($this->search) . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('code', 'like', $searchTerm)
                  ->orWhere('description', 'like', $searchTerm)
                  ->orWhere('category', 'like', $searchTerm);
            });
        }

        if ($this->filterCategory) {
            $query->where('category', $this->filterCategory);
        }

        if ($this->filterStatus) {
            $query->where('status', $this->filterStatus);
        }

        if ($this->startDate) {
            $query->whereDate('expense_date', '>=', $this->startDate);
        }

        if ($this->endDate) {
            $query->whereDate('expense_date', '<=', $this->endDate);
        }

        $expenses = $query->orderBy('expense_date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate($this->perPage);

        return view('livewire.reseller-portal.finance.expense-index', [
            'expenses' => $expenses,
            'monthTotal' => $monthTotal,
            'todayTotal' => $todayTotal,
            'totalTransactions' => $totalTransactions,
            'pendingApproval' => $pendingApproval,
            'categories' => $this->categories,
        ]);
    }
}
