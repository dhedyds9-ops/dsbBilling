@extends('layouts.app')

@section('title', 'Detail Kategori Pengeluaran')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Detail Kategori Pengeluaran</h2>
        <a href="{{ route('expense-categories.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <dl class="row">
                <dt class="col-sm-3">Kode Kategori</dt>
                <dd class="col-sm-9">{{ $expenseCategory->code }}</dd>

                <dt class="col-sm-3">Nama Kategori</dt>
                <dd class="col-sm-9">{{ $expenseCategory->name }}</dd>

                <dt class="col-sm-3">Deskripsi</dt>
                <dd class="col-sm-9">{{ $expenseCategory->description ?? '-' }}</dd>

                <dt class="col-sm-3">Status</dt>
                <dd class="col-sm-9">
                    <span class="badge {{ $expenseCategory->status === 'active' ? 'bg-success' : 'bg-secondary' }}">
                        {{ $expenseCategory->status === 'active' ? 'Aktif' : 'Nonaktif' }}
                    </span>
                </dd>

                <dt class="col-sm-3">Dibuat Pada</dt>
                <dd class="col-sm-9">{{ $expenseCategory->created_at->format('d/m/Y H:i') }}</dd>

                <dt class="col-sm-3">Diperbarui Pada</dt>
                <dd class="col-sm-9">{{ $expenseCategory->updated_at->format('d/m/Y H:i') }}</dd>
            </dl>

            <div class="d-flex gap-2 mt-4">
                <a href="{{ route('expense-categories.edit', $expenseCategory) }}" class="btn btn-warning">
                    <i class="bi bi-pencil me-1"></i> Edit
                </a>
                <form action="{{ route('expense-categories.destroy', $expenseCategory) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus kategori ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash me-1"></i> Hapus
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection
