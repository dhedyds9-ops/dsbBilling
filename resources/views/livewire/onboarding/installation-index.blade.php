<div class="row g-4">
    <div class="col-12">
        <div class="card card-stat bg-white dark:bg-slate-800">
            <div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    <h2 class="h3 mb-1 fw-bold">Instalasi</h2>
                    <p class="text-muted mb-0">Total: <span class="text-primary fw-bold">{{ $installations->total() }}</span> Instalasi</p>
                </div>
                <button class="btn btn-primary">
                    <i class="bi bi-plus me-1"></i> Jadwalkan Instalasi
                </button>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card card-stat bg-white dark:bg-slate-800">
            <div class="card-body p-0">
                <div class="w-full overflow-x-auto">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                        <tr>
                            <th>Pelanggan</th>
                            <th>Teknisi</th>
                            <th>Jadwal</th>
                            <th>ONU</th>
                            <th>Status</th>
                            <th>Tanggal</th>
                            <th>Aksi</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($installations as $installation)
                            <tr>
                                <td>
                                    <div class="fw-bold">{{ $installation->customer->name ?? '-' }}</div>
                                </td>
                                <td><div class="text-muted small">{{ $installation->technician?->name ?? '-' }}</div></td>
                                <td><div class="text-muted small">{{ $installation->schedule_date->format('d/m/Y H:i') }}</div></td>
                                <td><div class="text-muted small">{{ $installation->onu?->serial_number ?? '-' }}</div></td>
                                <td>
                                    <span class="badge {{ $installation->status == 'completed' ? 'bg-success' : ($installation->status == 'in_progress' ? 'bg-primary' : 'bg-warning') }} badge-status">
                                        {{ ucfirst(str_replace('_', ' ', $installation->status)) }}
                                    </span>
                                </td>
                                <td><div class="text-muted small">{{ $installation->created_at->format('d/m/Y') }}</div></td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <button class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-between align-items-center p-3 border-top">
                    <div class="text-muted small">
                        Menampilkan {{ $installations->firstItem() }} - {{ $installations->lastItem() }} dari {{ $installations->total() }}
                    </div>
                    <div>
                        {{ $installations->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
