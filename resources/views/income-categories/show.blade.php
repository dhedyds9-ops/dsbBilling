@extends('layouts.app')

@section('title', 'Detail Kategori Pendapatan')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Detail Kategori Pendapatan</h2>
        <a href="{{ route('income-categories.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <dl class="row">
                <dt class="col-sm-3">Kode Kategori</dt>
                <dd class="col-sm-9">{{ $incomeCategory->code }}</dd>

                <dt class="col-sm-3">Nama Kategori</dt>
                <dd class="col-sm-9">{{ $incomeCategory->name }}</dd>

                <dt class="col-sm-3">Deskripsi</dt>
                <dd class="col-sm-9">{{ $incomeCategory->description ?? '-' }}</dd>

                <dt class="col-sm-3">Status</dt>
                <dd class="col-sm-9">
                    <span class="badge {{ $incomeCategory->status === 'active' ? 'bg-success' : 'bg-secondary' }}">
                        {{ $incomeCategory->status === 'active' ? 'Aktif' : 'Nonaktif' }}
                    </span>
                </dd>

                <dt class="col-sm-3">Dibuat Pada</dt>
                <dd class="col-sm-9">{{ $incomeCategory->created_at->format('d/m/Y H:i') }}</dd>

                <dt class="col-sm-3">Diperbarui Pada</dt>
                <dd class="col-sm-9">{{ $incomeCategory->updated_at->format('d/m/Y H:i') }}</dd>
            </dl>

            <div class="d-flex gap-2 mt-4">
                <a href="{{ route('income-categories.edit', $incomeCategory) }}" class="btn btn-warning">
                    <i class="bi bi-pencil me-1"></i> Edit
                </a>
                <form action="{{ route('income-categories.destroy', $incomeCategory) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus kategori ini?')">
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
