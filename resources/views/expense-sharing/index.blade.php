@extends('layouts.app')

@section('title', 'Pengeluaran Sharing')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>{{ $pageTitle }}</h2>
        <div class="d-flex gap-2">
            <form method="GET" action="{{ route('expense-sharing.index') }}" class="d-flex gap-2">
                <input type="month" name="period" class="form-control dark:bg-slate-900 dark:text-slate-100" value="{{ request('period') }}" placeholder="Periode">
                <button type="submit" class="btn btn-secondary">Filter</button>
                @if(request('period'))
                    <a href="{{ route('expense-sharing.index') }}" class="btn btn-light">Reset</a>
                @endif
            </form>
            <a href="{{ route('expense-sharing.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-1"></i> Tambah Pengeluaran Sharing
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card">
        <div class="card-body">
            <div class="w-full overflow-x-auto">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Kode</th>
                            <th>Tanggal</th>
                            <th>Akun Kas</th>
                            <th>Kategori Pengeluaran</th>
                            <th>Jumlah</th>
                            <th>Status</th>
                            <th style="width: 220px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($transactions as $transaction)
                            <tr>
                                <td>{{ $transaction->code }}</td>
                                <td>{{ $transaction->date->format('d/m/Y') }}</td>
                                <td>{{ $transaction->cashAccount->name ?? '-' }}</td>
                                <td>{{ $transaction->expenseCategory->name ?? '-' }}</td>
                                <td>Rp {{ number_format($transaction->amount, 0, ',', '.') }}</td>
                                <td>
                                    <span class="badge {{ $transaction->status === 'posted' ? 'bg-success' : ($transaction->status === 'canceled' ? 'bg-danger' : 'bg-secondary') }}">
                                        {{ $transaction->status === 'posted' ? 'Diposting' : ($transaction->status === 'canceled' ? 'Dibatalkan' : 'Draft') }}
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('expense-sharing.show', $transaction->id) }}" class="btn btn-sm btn-info">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('expense-sharing.edit', $transaction->id) }}" class="btn btn-sm btn-warning">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('expense-sharing.destroy', $transaction->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus pengeluaran sharing ini?')">
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
            {{ $transactions->links() }}
        </div>
    </div>
@endsection
