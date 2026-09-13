@extends('layouts.app')

@section('title', 'Audit Trail')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Audit Trail - Log Aktivitas</h5>
    </div>
    <div class="card-body">
        <form method="GET" class="mb-3">
            <div class="input-group">
                <input type="text" name="search" value="{{ request('search') }}" class="form-control dark:bg-slate-900 dark:text-slate-100" placeholder="Cari event atau catatan...">
                <button class="btn btn-primary" type="submit">
                    <i class="bi bi-search"></i> Cari
                </button>
            </div>
        </form>

        <div class="w-full overflow-x-auto">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Tanggal & Waktu</th>
                        <th>Pengguna</th>
                        <th>Event</th>
                        <th>Catatan</th>
                        <th>IP Address</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                    <tr>
                        <td>{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                        <td>{{ $log->user?->name ?? 'System' }}</td>
                        <td>
                            <span class="badge bg-{{ match($log->event) {
                                'created' => 'success',
                                'updated' => 'warning',
                                'deleted' => 'danger',
                                default => 'secondary'
                            } }}">
                                {{ $log->event }}
                            </span>
                        </td>
                        <td>{{ $log->notes ?? '-' }}</td>
                        <td>{{ $log->ip_address ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center">Tidak ada log aktivitas</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $logs->links() }}
    </div>
</div>
@endsection
