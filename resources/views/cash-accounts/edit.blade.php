@extends('layouts.app')

@section('title', 'Edit Akun Kas')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Edit Akun Kas</h2>
        <a href="{{ route('cash-accounts.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('cash-accounts.update', $cashAccount) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="mb-3">
                    <label for="code" class="form-label">Kode Akun</label>
                    <input type="text" class="form-control @error('code') is-invalid @enderror" id="code" name="code" value="{{ old('code', $cashAccount->code) }}" required>
                    @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label for="name" class="form-label">Nama Akun</label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $cashAccount->name) }}" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label for="type" class="form-label">Tipe Akun</label>
                    <select class="form-control @error('type') is-invalid @enderror" id="type" name="type" required>
                        <option value="">Pilih Tipe</option>
                        <option value="kas_besar" {{ old('type', $cashAccount->type) === 'kas_besar' ? 'selected' : '' }}>Kas Besar</option>
                        <option value="kecil" {{ old('type', $cashAccount->type) === 'kecil' ? 'selected' : '' }}>Kas Kecil</option>
                        <option value="tabungan" {{ old('type', $cashAccount->type) === 'tabungan' ? 'selected' : '' }}>Tabungan</option>
                    </select>
                    @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Deskripsi</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description', $cashAccount->description) }}</textarea>
                    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-control @error('status') is-invalid @enderror" id="status" name="status" required>
                        <option value="active" {{ old('status', $cashAccount->status) === 'active' ? 'selected' : '' }}>Aktif</option>
                        <option value="inactive" {{ old('status', $cashAccount->status) === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                    </select>
                    @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save me-1"></i> Perbarui
                </button>
            </form>
        </div>
    </div>
@endsection
