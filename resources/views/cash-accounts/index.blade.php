@extends('layouts.app')

@section('title', 'Akun Kas')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Daftar Akun Kas</h2>
        <a href="{{ route('cash-accounts.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i> Tambah Akun
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Kode</th>
                            <th>Nama</th>
                            <th>Tipe</th>
                            <th>Saldo Saat Ini</th>
                            <th>Status</th>
                            <th style="width: 220px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($accounts as $account)
                            <tr>
                                <td>{{ $account->code }}</td>
                                <td>{{ $account->name }}</td>
                                <td>
                                    @php
                                        $typeLabels = [
                                            'kas_besar' => 'Kas Besar',
                                            'kecil' => 'Kas Kecil',
                                            'tabungan' => 'Tabungan'
                                        ];
                                    @endphp
                                    {{ $typeLabels[$account->type] ?? $account->type }}
                                </td>
                                <td>Rp {{ number_format($account->balance, 0, ',', '.') }}</td>
                                <td>
                                    <span class="badge {{ $account->status === 'active' ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $account->status === 'active' ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('cash-accounts.show', $account->id) }}" class="btn btn-sm btn-info">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('cash-accounts.edit', $account->id) }}" class="btn btn-sm btn-warning">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('cash-accounts.destroy', $account->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus akun kas ini?')">
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
            {{ $accounts->links() }}
        </div>
    </div>
@endsection
