@extends('layouts.app')

@section('title', 'Laporan Laba Rugi')

@section('content')
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-5">
                        <label class="form-label">Tanggal Mulai</label>
                        <input type="date" name="start_date" value="{{ $startDate }}" class="form-control dark:bg-slate-900 dark:text-slate-100">
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">Tanggal Selesai</label>
                        <input type="date" name="end_date" value="{{ $endDate }}" class="form-control dark:bg-slate-900 dark:text-slate-100">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-funnel"></i> Filter
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Laporan Laba Rugi Periode {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</h5>
        <div class="btn-group">
            <button class="btn btn-outline-secondary" onclick="window.print()">
                <i class="bi bi-printer"></i> Cetak
            </button>
        </div>
    </div>
    <div class="card-body">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <table class="table table-bordered">
                    <tr class="table-primary">
                        <th colspan="2" class="text-center">PENDAPATAN</th>
                    </tr>
                    <tr>
                        <td>Total Pendapatan</td>
                        <td class="text-end fw-bold text-success">Rp {{ $profitLoss['total_revenue'] }}</td>
                    </tr>
                    <tr class="table-danger">
                        <th colspan="2" class="text-center">PENGELUARAN</th>
                    </tr>
                    <tr>
                        <td>Total Pengeluaran</td>
                        <td class="text-end fw-bold text-danger">Rp {{ $profitLoss['total_expenses'] }}</td>
                    </tr>
                    <tr class="table-secondary">
                        <th class="text-center">LABA BERSIH</th>
                        <td class="text-end fw-bold fs-4">
                            <span class="{{ (str_replace('.', '', $profitLoss['net_profit']) >= 0) ? 'text-success' : 'text-danger' }}">
                                Rp {{ $profitLoss['net_profit'] }}
                            </span>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
