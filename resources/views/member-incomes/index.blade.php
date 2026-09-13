@extends('layouts.app')

@section('title', 'Pendapatan Anggota')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Daftar Pendapatan Anggota</h2>
        <div class="d-flex gap-2">
            <form method="GET" action="{{ route('member-incomes.index') }}" class="d-flex gap-2">
                <input type="month" name="period" class="form-control dark:bg-slate-900 dark:text-slate-100" value="{{ request('period') }}" placeholder="Periode">
                <button type="submit" class="btn btn-secondary">Filter</button>
                @if(request('period'))
                    <a href="{{ route('member-incomes.index') }}" class="btn btn-light">Reset</a>
                @endif
            </form>
            <a href="{{ route('member-incomes.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-1"></i> Tambah Pendapatan
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
                            <th>Periode</th>
                            <th>Anggota</th>
                            <th>Kategori</th>
                            <th>Jumlah</th>
                            <th>Status</th>
                            <th style="width: 220px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($incomes as $income)
                            <tr>
                                <td>{{ $income->code }}</td>
                                <td>{{ $income->date->format('d/m/Y') }}</td>
                                <td>{{ $income->period }}</td>
                                <td>{{ $income->member->name ?? '-' }}</td>
                                <td>{{ $income->incomeCategory->name ?? '-' }}</td>
                                <td>Rp {{ number_format($income->amount, 0, ',', '.') }}</td>
                                <td>
                                    <span class="badge {{ $income->status === 'posted' ? 'bg-success' : ($income->status === 'canceled' ? 'bg-danger' : 'bg-secondary') }}">
                                        {{ $income->status === 'posted' ? 'Diposting' : ($income->status === 'canceled' ? 'Dibatalkan' : 'Draft') }}
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('member-incomes.show', $income->id) }}" class="btn btn-sm btn-info">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('member-incomes.edit', $income->id) }}" class="btn btn-sm btn-warning">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('member-incomes.destroy', $income->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus pendapatan anggota ini?')">
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
            {{ $incomes->links() }}
        </div>
    </div>
@endsection
