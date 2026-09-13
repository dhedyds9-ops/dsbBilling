@extends('layouts.app')

@section('title', 'Edit Paket Internet')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Edit Paket Internet</h2>
        <a href="{{ route('internet-packages.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('internet-packages.update', $internetPackage->id) }}">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label for="code" class="form-label">Kode Paket</label>
                    <input type="text" class="form-control @error('code') is-invalid @enderror dark:bg-slate-900 dark:text-slate-100" id="code" name="code" value="{{ old('code', $internetPackage->code) }}" required>
                    @error('code')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="name" class="form-label">Nama Paket</label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror dark:bg-slate-900 dark:text-slate-100" id="name" name="name" value="{{ old('name', $internetPackage->name) }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Deskripsi</label>
                    <textarea class="form-control @error('description') is-invalid @enderror dark:bg-slate-900 dark:text-slate-100" id="description" name="description" rows="3">{{ old('description', $internetPackage->description) }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label for="price" class="form-label">Harga</label>
                        <input type="number" step="0.01" class="form-control @error('price') is-invalid @enderror dark:bg-slate-900 dark:text-slate-100" id="price" name="price" value="{{ old('price', $internetPackage->price) }}" required>
                        @error('price')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label for="bandwidth_download" class="form-label">Download (Mbps)</label>
                        <input type="number" class="form-control @error('bandwidth_download') is-invalid @enderror dark:bg-slate-900 dark:text-slate-100" id="bandwidth_download" name="bandwidth_download" value="{{ old('bandwidth_download', $internetPackage->bandwidth_download) }}" required>
                        @error('bandwidth_download')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label for="bandwidth_upload" class="form-label">Upload (Mbps)</label>
                        <input type="number" class="form-control @error('bandwidth_upload') is-invalid @enderror dark:bg-slate-900 dark:text-slate-100" id="bandwidth_upload" name="bandwidth_upload" value="{{ old('bandwidth_upload', $internetPackage->bandwidth_upload) }}" required>
                        @error('bandwidth_upload')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label for="is_active" class="form-label">Status</label>
                    <select class="form-select @error('is_active') is-invalid @enderror dark:bg-slate-900 dark:text-slate-100 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100" id="is_active" name="is_active" required>
                        <option value="1" {{ old('is_active', $internetPackage->is_active ? '1' : '0') === '1' ? 'selected' : '' }}>Aktif</option>
                        <option value="0" {{ old('is_active', $internetPackage->is_active ? '1' : '0') === '0' ? 'selected' : '' }}>Nonaktif</option>
                    </select>
                    @error('is_active')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save me-1"></i> Simpan Perubahan
                </button>
            </form>
        </div>
    </div>
@endsection
