@extends('layouts.app')

@section('title', 'Detail Pendapatan Anggota')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Detail Pendapatan Anggota</h2>
        <a href="{{ route('member-incomes.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <table class="table table-bordered">
                <tr>
                    <th style="width: 30%;">Kode</th>
                    <td>{{ $memberIncome->code }}</td>
                </tr>
                <tr>
                    <th>Tanggal</th>
                    <td>{{ $memberIncome->date->format('d/m/Y') }}</td>
                </tr>
                <tr>
                    <th>Periode</th>
                    <td>{{ $memberIncome->period }}</td>
                </tr>
                <tr>
                    <th>Anggota</th>
                    <td>{{ $memberIncome->member->name ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Kategori Pendapatan</th>
                    <td>{{ $memberIncome->incomeCategory->name ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Jumlah</th>
                    <td>Rp {{ number_format($memberIncome->amount, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <th>Status</th>
                    <td>
                        <span class="badge {{ $memberIncome->status === 'posted' ? 'bg-success' : ($memberIncome->status === 'canceled' ? 'bg-danger' : 'bg-secondary') }}">
                            {{ $memberIncome->status === 'posted' ? 'Diposting' : ($memberIncome->status === 'canceled' ? 'Dibatalkan' : 'Draft') }}
                        </span>
                    </td>
                </tr>
                <tr>
                    <th>Deskripsi</th>
                    <td>{{ $memberIncome->description ?? '-' }}</td>
                </tr>
            </table>
        </div>
    </div>
@endsection
