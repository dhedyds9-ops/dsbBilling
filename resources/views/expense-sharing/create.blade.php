@extends('layouts.app')

@section('title', 'Tambah Pengeluaran Sharing')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Tambah Pengeluaran Sharing</h2>
        <a href="{{ route('expense-sharing.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('expense-sharing.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="cash_account_id" class="form-label">Akun Kas</label>
                    <select class="form-control @error('cash_account_id') is-invalid @enderror dark:bg-slate-900 dark:text-slate-100" id="cash_account_id" name="cash_account_id" required>
                        <option value="">Pilih Akun Kas</option>
                        @foreach($cashAccounts as $account)
                            <option value="{{ $account->id }}" {{ old('cash_account_id') == $account->id ? 'selected' : '' }}>{{ $account->name }}</option>
                        @endforeach
                    </select>
                    @error('cash_account_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label for="expense_category_id" class="form-label">Kategori Pengeluaran</label>
                    <select class="form-control @error('expense_category_id') is-invalid @enderror dark:bg-slate-900 dark:text-slate-100" id="expense_category_id" name="expense_category_id" required>
                        <option value="">Pilih Kategori</option>
                        @foreach($expenseCategories as $category)
                            <option value="{{ $category->id }}" {{ old('expense_category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                        @endforeach
                    </select>
                    @error('expense_category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label for="amount" class="form-label">Jumlah</label>
                    <input type="number" class="form-control @error('amount') is-invalid @enderror dark:bg-slate-900 dark:text-slate-100" id="amount" name="amount" value="{{ old('amount', 0) }}" required min="0">
                    @error('amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label for="transaction_date" class="form-label">Tanggal Transaksi</label>
                    <input type="date" class="form-control @error('transaction_date') is-invalid @enderror dark:bg-slate-900 dark:text-slate-100" id="transaction_date" name="transaction_date" value="{{ old('transaction_date', date('Y-m-d')) }}" required>
                    @error('transaction_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Deskripsi</label>
                    <textarea class="form-control @error('description') is-invalid @enderror dark:bg-slate-900 dark:text-slate-100" id="description" name="description" rows="3">{{ old('description') }}</textarea>
                    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-control @error('status') is-invalid @enderror dark:bg-slate-900 dark:text-slate-100" id="status" name="status" required>
                        <option value="draft" {{ old('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="posted" {{ old('status') === 'posted' ? 'selected' : '' }}>Diposting</option>
                        <option value="canceled" {{ old('status') === 'canceled' ? 'selected' : '' }}>Dibatalkan</option>
                    </select>
                    @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save me-1"></i> Simpan
                </button>
            </form>
        </div>
    </div>
@endsection
