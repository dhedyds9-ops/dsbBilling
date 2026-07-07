@extends('layouts.app')

@section('title', 'Transaksi Kas')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>{{ $pageTitle }}</h2>
        <div class="d-flex gap-2">
            <form method="GET" action="{{ route('cash-transactions.index') }}" class="d-flex gap-2">
                <input type="hidden" name="type" value="{{ request('type') }}">
                <input type="month" name="period" class="form-control" value="{{ request('period') }}" placeholder="Periode">
                <button type="submit" class="btn btn-secondary">Filter</button>
                @if(request('period') || request('type'))
                    <a href="{{ route('cash-transactions.index') }}" class="btn btn-light">Reset</a>
                @endif
            </form>
            <a href="{{ route('cash-transactions.create', ['type' => request('type')]) }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-1"></i> Tambah {{ request('type') === 'income' ? 'Pendapatan' : (request('type') === 'expense' ? 'Pengeluaran' : (request('type') === 'transfer' ? 'Transfer' : 'Transaksi')) }}
            </a>
        </div>
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
                            <th>Tanggal</th>
                            @if(!request('type'))
                                <th>Tipe</th>
                            @endif
                            <th>Akun Kas</th>
                            <th>Akun Tujuan</th>
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
                                @if(!request('type'))
                                    <td>
                                        <span class="badge {{ $transaction->type === 'income' ? 'bg-success' : ($transaction->type === 'expense' ? 'bg-danger' : 'bg-info') }}">
                                            {{ $transaction->type === 'income' ? 'Pendapatan' : ($transaction->type === 'expense' ? 'Pengeluaran' : 'Transfer') }}
                                        </span>
                                    </td>
                                @endif
                                <td>{{ $transaction->cashAccount->name ?? '-' }}</td>
                                <td>{{ $transaction->relatedCashAccount->name ?? '-' }}</td>
                                <td>{{ $transaction->expenseCategory->name ?? '-' }}</td>
                                <td>Rp {{ number_format($transaction->amount, 0, ',', '.') }}</td>
                                <td>
                                    <span class="badge {{ $transaction->status === 'posted' ? 'bg-success' : ($transaction->status === 'canceled' ? 'bg-danger' : 'bg-secondary') }}">
                                        {{ $transaction->status === 'posted' ? 'Diposting' : ($transaction->status === 'canceled' ? 'Dibatalkan' : 'Draft') }}
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('cash-transactions.show', $transaction->id) }}" class="btn btn-sm btn-info">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('cash-transactions.edit', $transaction->id) }}" class="btn btn-sm btn-warning">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('cash-transactions.destroy', $transaction->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus transaksi kas ini?')">
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
