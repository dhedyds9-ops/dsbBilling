@extends('layouts.app')

@section('title', 'Master Paket Internet')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Daftar Paket Internet</h2>
        <a href="{{ route('internet-packages.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i> Tambah Paket
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('internet-packages.index') }}">
                <div class="row g-3">
                    <div class="col-md-4">
                        <input type="text" name="search" value="{{ request('search') }}" class="form-control dark:bg-slate-900 dark:text-slate-100" placeholder="Cari kode atau nama paket...">
                    </div>
                    <div class="col-md-3">
                        <select name="is_active" class="form-select dark:bg-slate-900 dark:text-slate-100 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
                            <option value="">Semua Status</option>
                            <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>Aktif</option>
                            <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Nonaktif</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search"></i> Filter
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="w-full overflow-x-auto">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Kode</th>
                            <th>Nama Paket</th>
                            <th>Download (Mbps)</th>
                            <th>Upload (Mbps)</th>
                            <th>Harga</th>
                            <th>Status</th>
                            <th style="width: 220px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($packages as $package)
                            <tr>
                                <td>{{ $package->code }}</td>
                                <td>{{ $package->name }}</td>
                                <td>{{ $package->bandwidth_download }}</td>
                                <td>{{ $package->bandwidth_upload }}</td>
                                <td>Rp {{ number_format($package->price, 0, ',', '.') }}</td>
                                <td>
                                    <span class="badge {{ $package->is_active ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $package->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('internet-packages.show', $package->id) }}" class="btn btn-sm btn-info">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('internet-packages.edit', $package->id) }}" class="btn btn-sm btn-warning">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('internet-packages.destroy', $package->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus paket ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{ $packages->links() }}
        </div>
    </div>
@endsection
