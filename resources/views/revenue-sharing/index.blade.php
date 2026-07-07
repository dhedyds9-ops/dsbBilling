@extends('layouts.app')

@section('title', 'Revenue Sharing')

@section('content')
    <div class="container-fluid">
        <h2 class="mb-4">Revenue Sharing</h2>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        @if($existingBatch && $existingBatch->transactions_changed)
            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle-fill"></i>
                Data transaksi periode ini telah berubah. Revenue Sharing perlu diregenerate.
            </div>
        @endif

        <div class="row mb-4">
            <div class="col-md-6">
                <form method="GET" class="d-flex gap-2">
                    <div class="form-group flex-grow-1">
                        <label for="period">Periode</label>
                        <input type="month" name="period" id="period" class="form-control" value="{{ $period }}">
                    </div>
                    <div class="form-group align-self-end">
                        <button type="submit" class="btn btn-primary">Lihat</button>
                    </div>
                </form>
            </div>
            <div class="col-md-6 text-end align-self-end">
                @if(!$existingBatch || $existingBatch->transactions_changed || $existingBatch->status === 'draft')
                    <form method="POST" action="{{ route('revenue-sharing.generate') }}" class="d-inline-block">
                        @csrf
                        <input type="hidden" name="period" value="{{ $period }}">
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-magic"></i> Generate Batch
                        </button>
                    </form>
                @endif
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card card-stat h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="card-icon bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                <i class="bi bi-cash-coin" style="font-size: 24px;"></i>
                            </div>
                            <div class="ms-3">
                                <h5 class="mb-1">Total Pendapatan</h5>
                                <h3 class="text-primary">Rp {{ number_format($preview['total_revenue'], 0, ',', '.') }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card card-stat h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="card-icon bg-danger text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                <i class="bi bi-wallet2" style="font-size: 24px;"></i>
                            </div>
                            <div class="ms-3">
                                <h5 class="mb-1">Total Pengeluaran</h5>
                                <h3 class="text-danger">Rp {{ number_format($preview['total_expense'], 0, ',', '.') }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card card-stat h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="card-icon bg-success text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                <i class="bi bi-graph-up-arrow" style="font-size: 24px;"></i>
                            </div>
                            <div class="ms-3">
                                <h5 class="mb-1">Total Bersih</h5>
                                <h3 class="text-success">Rp {{ number_format($preview['total_distributed'], 0, ',', '.') }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card card-stat h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="card-icon bg-info text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                <i class="bi bi-people" style="font-size: 24px;"></i>
                            </div>
                            <div class="ms-3">
                                <h5 class="mb-1">Jumlah Anggota</h5>
                                <h3 class="text-info">{{ count($preview['items']) }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detail Anggota -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Detail Anggota</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
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
                            @foreach($preview['items'] as $item)
                                <tr>
                                    <td>{{ $item['member']->name }}</td>
                                    <td>Rp {{ number_format($item['member_revenue'], 0, ',', '.') }}</td>
                                    <td>{{ number_format($item['percentage'], 2) }}%</td>
                                    <td>Rp {{ number_format($item['expense_share'], 0, ',', '.') }}</td>
                                    <td class="fw-bold text-success">Rp {{ number_format($item['net_share'], 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-secondary">
                            <tr>
                                <th>Total</th>
                                <th>Rp {{ number_format($preview['total_revenue'], 0, ',', '.') }}</th>
                                <th>100%</th>
                                <th>Rp {{ number_format($preview['total_expense'], 0, ',', '.') }}</th>
                                <th>Rp {{ number_format($preview['total_distributed'], 0, ',', '.') }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        @if($existingBatch)
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Batch {{ $existingBatch->batch_number }}</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-3"><strong>Periode:</strong> {{ $existingBatch->period }}</div>
                        <div class="col-md-3"><strong>Status:</strong> 
                            <span class="badge bg-{{ $existingBatch->status === 'locked' ? 'secondary' : ($existingBatch->status === 'approved' ? 'success' : 'warning') }}">
                                {{ ucfirst($existingBatch->status) }}
                            </span>
                        </div>
                        <div class="col-md-3"><strong>Dibuat pada:</strong> {{ $existingBatch->generated_at?->format('d/m/Y H:i') }}</div>
                        <div class="col-md-3"><strong>Dibuat oleh:</strong> {{ $existingBatch->generatedBy?->name }}</div>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('revenue-sharing.show', $existingBatch) }}" class="btn btn-info">
                            <i class="bi bi-eye"></i> Lihat Detail
                        </a>
                        @if($existingBatch->status === 'generated')
                            <form method="POST" action="{{ route('revenue-sharing.approve', $existingBatch) }}" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-check-circle"></i> Approve
                                </button>
                            </form>
                        @endif
                        @if($existingBatch->status === 'approved')
                            <form method="POST" action="{{ route('revenue-sharing.lock', $existingBatch) }}" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-warning">
                                    <i class="bi bi-lock"></i> Lock
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        <!-- History Batches -->
        @if($batches->count() > 0)
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">Riwayat Batch</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Batch Number</th>
                                    <th>Periode</th>
                                    <th>Status</th>
                                    <th>Dibuat pada</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($batches as $batch)
                                    <tr>
                                        <td>{{ $batch->batch_number }}</td>
                                        <td>{{ $batch->period }}</td>
                                        <td>
                                            <span class="badge bg-{{ $batch->status === 'locked' ? 'secondary' : ($batch->status === 'approved' ? 'success' : 'warning') }}">
                                                {{ ucfirst($batch->status) }}
                                            </span>
                                        </td>
                                        <td>{{ $batch->generated_at?->format('d/m/Y H:i') }}</td>
                                        <td>
                                            <a href="{{ route('revenue-sharing.show', $batch) }}" class="btn btn-sm btn-info">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    {{ $batches->links() }}
                </div>
            </div>
        @endif
    </div>
@endsection
