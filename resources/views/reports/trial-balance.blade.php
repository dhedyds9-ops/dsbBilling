@extends('layouts.app')

@section('title', 'Neraca Saldo (Trial Balance)')

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
        <h5 class="mb-0">Neraca Saldo Periode {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</h5>
        <div class="btn-group">
            <button class="btn btn-outline-secondary" onclick="window.print()">
                <i class="bi bi-printer"></i> Cetak
            </button>
        </div>
    </div>
    <div class="card-body">
        <div class="w-full overflow-x-auto">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Kode Akun</th>
                        <th>Nama Akun</th>
                        <th class="text-end">Debit</th>
                        <th class="text-end">Kredit</th>
                        <th class="text-end">Saldo</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($trialBalance['accounts'] as $account)
                    <tr>
                        <td><strong>{{ $account['code'] }}</strong></td>
                        <td>{{ $account['name'] }}</td>
                        <td class="text-end">Rp {{ $account['debit'] }}</td>
                        <td class="text-end">Rp {{ $account['credit'] }}</td>
                        <td class="text-end">
                            <span class="{{ (str_replace('.', '', $account['balance']) >= 0) ? 'text-success' : 'text-danger' }}">
                                Rp {{ $account['balance'] }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="table-secondary fw-bold">
                    <tr>
                        <td colspan="2" class="text-end">Total</td>
                        <td class="text-end">Rp {{ $trialBalance['total_debit'] }}</td>
                        <td class="text-end">Rp {{ $trialBalance['total_credit'] }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        
        @if($trialBalance['is_balanced'])
            <div class="alert alert-success mt-3">
                <i class="bi bi-check-circle"></i> Neraca saldo <strong>seimbang</strong>!
            </div>
        @else
            <div class="alert alert-danger mt-3">
                <i class="bi bi-exclamation-triangle"></i> Neraca saldo <strong>tidak seimbang</strong>!
            </div>
        @endif
    </div>
</div>
@endsection
