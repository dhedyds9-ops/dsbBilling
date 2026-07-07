@extends('layouts.app')

@section('title', 'Detail Pengeluaran Sharing')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Detail Pengeluaran Sharing</h2>
        <a href="{{ route('expense-sharing.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <table class="table table-bordered">
                <tr>
                    <th style="width: 30%;">Kode</th>
                    <td>{{ $cashTransaction->code }}</td>
                </tr>
                <tr>
                    <th>Tanggal</th>
                    <td>{{ $cashTransaction->date->format('d/m/Y') }}</td>
                </tr>
                <tr>
                    <th>Akun Kas</th>
                    <td>{{ $cashTransaction->cashAccount->name ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Kategori Pengeluaran</th>
                    <td>{{ $cashTransaction->expenseCategory->name ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Jumlah</th>
                    <td>Rp {{ number_format($cashTransaction->amount, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <th>Status</th>
                    <td>
                        <span class="badge {{ $cashTransaction->status === 'posted' ? 'bg-success' : ($cashTransaction->status === 'canceled' ? 'bg-danger' : 'bg-secondary') }}">
                            {{ $cashTransaction->status === 'posted' ? 'Diposting' : ($cashTransaction->status === 'canceled' ? 'Dibatalkan' : 'Draft') }}
                        </span>
                    </td>
                </tr>
                <tr>
                    <th>Deskripsi</th>
                    <td>{{ $cashTransaction->description ?? '-' }}</td>
                </tr>
            </table>
        </div>
    </div>
@endsection
