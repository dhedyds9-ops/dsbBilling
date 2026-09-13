@extends('layouts.app')

@section('title', 'Tambah Pendapatan Anggota')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Tambah Pendapatan Anggota</h2>
        <a href="{{ route('member-incomes.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('member-incomes.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="member_id" class="form-label">Anggota</label>
                    <select class="form-control @error('member_id') is-invalid @enderror dark:bg-slate-900 dark:text-slate-100" id="member_id" name="member_id" required>
                        <option value="">Pilih Anggota</option>
                        @foreach($members as $member)
                            <option value="{{ $member->id }}" {{ old('member_id') == $member->id ? 'selected' : '' }}>{{ $member->name }}</option>
                        @endforeach
                    </select>
                    @error('member_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label for="income_category_id" class="form-label">Kategori Pendapatan</label>
                    <select class="form-control @error('income_category_id') is-invalid @enderror dark:bg-slate-900 dark:text-slate-100" id="income_category_id" name="income_category_id" required>
                        <option value="">Pilih Kategori</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('income_category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                        @endforeach
                    </select>
                    @error('income_category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
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
