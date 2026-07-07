@extends('layouts.app')

@section('title', 'Edit Transaksi Kas')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Edit {{ $cashTransaction->type === 'income' ? 'Pendapatan' : ($cashTransaction->type === 'expense' ? 'Pengeluaran' : 'Transfer') }} Kas</h2>
        <a href="{{ route('cash-transactions.index', ['type' => $cashTransaction->type]) }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('cash-transactions.update', $cashTransaction) }}" method="POST" id="transactionForm">
                @csrf
                @method('PUT')
                <div class="mb-3">
                    <label for="type" class="form-label">Tipe Transaksi</label>
                    <select class="form-control @error('type') is-invalid @enderror" id="type" name="type" required>
                        <option value="">Pilih Tipe</option>
                        <option value="income" {{ old('type', $cashTransaction->type) === 'income' ? 'selected' : '' }}>Pendapatan</option>
                        <option value="expense" {{ old('type', $cashTransaction->type) === 'expense' ? 'selected' : '' }}>Pengeluaran</option>
                        <option value="transfer" {{ old('type', $cashTransaction->type) === 'transfer' ? 'selected' : '' }}>Transfer</option>
                    </select>
                    @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label for="cash_account_id" class="form-label">Akun Kas</label>
                    <select class="form-control @error('cash_account_id') is-invalid @enderror" id="cash_account_id" name="cash_account_id" required>
                        <option value="">Pilih Akun Kas</option>
                        @foreach($cashAccounts as $account)
                            <option value="{{ $account->id }}" {{ old('cash_account_id', $cashTransaction->cash_account_id) == $account->id ? 'selected' : '' }}>{{ $account->name }}</option>
                        @endforeach
                    </select>
                    @error('cash_account_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3" id="relatedAccountField" style="display: none;">
                    <label for="related_cash_account_id" class="form-label">Akun Tujuan</label>
                    <select class="form-control @error('related_cash_account_id') is-invalid @enderror" id="related_cash_account_id" name="related_cash_account_id">
                        <option value="">Pilih Akun Tujuan</option>
                        @foreach($cashAccounts as $account)
                            <option value="{{ $account->id }}" {{ old('related_cash_account_id', $cashTransaction->related_cash_account_id) == $account->id ? 'selected' : '' }}>{{ $account->name }}</option>
                        @endforeach
                    </select>
                    @error('related_cash_account_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3" id="expenseCategoryField" style="display: none;">
                    <label for="expense_category_id" class="form-label">Kategori Pengeluaran</label>
                    <select class="form-control @error('expense_category_id') is-invalid @enderror" id="expense_category_id" name="expense_category_id">
                        <option value="">Pilih Kategori</option>
                        @foreach($expenseCategories as $category)
                            <option value="{{ $category->id }}" {{ old('expense_category_id', $cashTransaction->expense_category_id) == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                        @endforeach
                    </select>
                    @error('expense_category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label for="amount" class="form-label">Jumlah</label>
                    <input type="number" class="form-control @error('amount') is-invalid @enderror" id="amount" name="amount" value="{{ old('amount', $cashTransaction->amount) }}" required min="0">
                    @error('amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label for="transaction_date" class="form-label">Tanggal Transaksi</label>
                    <input type="date" class="form-control @error('transaction_date') is-invalid @enderror" id="transaction_date" name="transaction_date" value="{{ old('transaction_date', $cashTransaction->date->format('Y-m-d')) }}" required>
                    @error('transaction_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Deskripsi</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description', $cashTransaction->description) }}</textarea>
                    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-control @error('status') is-invalid @enderror" id="status" name="status" required>
                        <option value="draft" {{ old('status', $cashTransaction->status) === 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="posted" {{ old('status', $cashTransaction->status) === 'posted' ? 'selected' : '' }}>Diposting</option>
                        <option value="canceled" {{ old('status', $cashTransaction->status) === 'canceled' ? 'selected' : '' }}>Dibatalkan</option>
                    </select>
                    @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save me-1"></i> Simpan
                </button>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const typeSelect = document.getElementById('type');
            const relatedAccountField = document.getElementById('relatedAccountField');
            const expenseCategoryField = document.getElementById('expenseCategoryField');
            
            function updateFields() {
                const type = typeSelect.value;
                if (type === 'transfer') {
                    relatedAccountField.style.display = 'block';
                    expenseCategoryField.style.display = 'none';
                } else if (type === 'expense') {
                    relatedAccountField.style.display = 'none';
                    expenseCategoryField.style.display = 'block';
                } else {
                    relatedAccountField.style.display = 'none';
                    expenseCategoryField.style.display = 'none';
                }
            }
            
            typeSelect.addEventListener('change', updateFields);
            updateFields();
        });
    </script>
@endsection
