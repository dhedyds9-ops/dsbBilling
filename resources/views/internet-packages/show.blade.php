@extends('layouts.app')

@section('title', 'Detail Paket Internet')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Detail Paket Internet</h2>
        <a href="{{ route('internet-packages.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-4">
                    <label class="form-label fw-bold">Kode Paket</label>
                    <p class="form-control-plaintext">{{ $internetPackage->code }}</p>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Nama Paket</label>
                    <p class="form-control-plaintext">{{ $internetPackage->name }}</p>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Status</label>
                    <p class="form-control-plaintext">
                        <span class="badge {{ $internetPackage->is_active ? 'bg-success' : 'bg-secondary' }}">
                            {{ $internetPackage->is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </p>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-4">
                    <label class="form-label fw-bold">Download (Mbps)</label>
                    <p class="form-control-plaintext">{{ $internetPackage->bandwidth_download }} Mbps</p>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Upload (Mbps)</label>
                    <p class="form-control-plaintext">{{ $internetPackage->bandwidth_upload }} Mbps</p>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Harga</label>
                    <p class="form-control-plaintext">Rp {{ number_format($internetPackage->price, 0, ',', '.') }}</p>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Deskripsi</label>
                <p class="form-control-plaintext">{{ $internetPackage->description ?? '-' }}</p>
            </div>

            <div class="d-flex gap-2">
                <a href="{{ route('internet-packages.edit', $internetPackage->id) }}" class="btn btn-warning">
                    <i class="bi bi-pencil me-1"></i> Edit
                </a>
                <a href="{{ route('internet-packages.index') }}" class="btn btn-secondary">
                    Kembali
                </a>
            </div>
        </div>
    </div>
@endsection
