@extends('layouts.app')

@section('title', 'Tambah Kategori Pengeluaran')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Tambah Kategori Pengeluaran</h2>
        <a href="{{ route('expense-categories.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('expense-categories.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="code" class="form-label">Kode Kategori</label>
                    <input type="text" class="form-control @error('code') is-invalid @enderror dark:bg-slate-900 dark:text-slate-100" id="code" name="code" value="{{ old('code') }}" required>
                    @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label for="name" class="form-label">Nama Kategori</label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror dark:bg-slate-900 dark:text-slate-100" id="name" name="name" value="{{ old('name') }}" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Deskripsi</label>
                    <textarea class="form-control @error('description') is-invalid @enderror dark:bg-slate-900 dark:text-slate-100" id="description" name="description" rows="3">{{ old('description') }}</textarea>
                    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-control @error('status') is-invalid @enderror dark:bg-slate-900 dark:text-slate-100" id="status" name="status" required>
                        <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                        <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                    </select>
                    @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input dark:bg-slate-900 dark:text-slate-100" id="affects_revenue_sharing" name="affects_revenue_sharing" value="1" {{ old('affects_revenue_sharing') ? 'checked' : '' }}>
                    <label class="form-check-label" for="affects_revenue_sharing">
                        Mempengaruhi Revenue Sharing
                    </label>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save me-1"></i> Simpan
                </button>
            </form>
        </div>
    </div>
@endsection
