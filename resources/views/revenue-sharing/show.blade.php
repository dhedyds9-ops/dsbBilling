@extends('layouts.app')

@section('title', 'Detail Revenue Sharing')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Detail {{ $batch->batch_number }}</h2>
            <a href="{{ route('revenue-sharing.index', ['period' => $batch->period]) }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Informasi Batch</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <strong>Batch Number:</strong>
                        <p class="mb-0">{{ $batch->batch_number }}</p>
                    </div>
                    <div class="col-md-3 mb-3">
                        <strong>Periode:</strong>
                        <p class="mb-0">{{ $batch->period }}</p>
                    </div>
                    <div class="col-md-3 mb-3">
                        <strong>Status:</strong>
                        <p class="mb-0">
                            <span class="badge bg-{{ $batch->status === 'locked' ? 'secondary' : ($batch->status === 'approved' ? 'success' : 'warning') }}">
                                {{ ucfirst($batch->status) }}
                            </span>
                        </p>
                    </div>
                    <div class="col-md-3 mb-3">
                        <strong>Total Pendapatan:</strong>
                        <p class="mb-0">Rp {{ number_format($batch->total_revenue, 0, ',', '.') }}</p>
                    </div>
                    <div class="col-md-3 mb-3">
                        <strong>Total Pengeluaran:</strong>
                        <p class="mb-0">Rp {{ number_format($batch->total_expense, 0, ',', '.') }}</p>
                    </div>
                    <div class="col-md-3 mb-3">
                        <strong>Total Didistribusikan:</strong>
                        <p class="mb-0">Rp {{ number_format($batch->total_distributed, 0, ',', '.') }}</p>
                    </div>
                    <div class="col-md-3 mb-3">
                        <strong>Dibuat oleh:</strong>
                        <p class="mb-0">{{ $batch->generatedBy?->name }}</p>
                    </div>
                    <div class="col-md-3 mb-3">
                        <strong>Dibuat pada:</strong>
                        <p class="mb-0">{{ $batch->generated_at?->format('d/m/Y H:i') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Detail Anggota</h5>
            </div>
            <div class="card-body">
                <div class="w-full overflow-x-auto">
                    <table class="table table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Nama Anggota</th>
                                <th>Pendapatan</th>
                                <th>Persentase</th>
                                <th>Beban</th>
                                <th>Hak Bersih</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($batch->items as $item)
                                <tr>
                                    <td>{{ $item->member->name }}</td>
                                    <td>Rp {{ number_format($item->member_revenue, 0, ',', '.') }}</td>
                                    <td>{{ number_format($item->percentage, 2) }}%</td>
                                    <td>Rp {{ number_format($item->expense_share, 0, ',', '.') }}</td>
                                    <td class="fw-bold text-success">Rp {{ number_format($item->net_share, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-secondary">
                            <tr>
                                <th>Total</th>
                                <th>Rp {{ number_format($batch->total_revenue, 0, ',', '.') }}</th>
                                <th>100%</th>
                                <th>Rp {{ number_format($batch->total_expense, 0, ',', '.') }}</th>
                                <th>Rp {{ number_format($batch->total_distributed, 0, ',', '.') }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
