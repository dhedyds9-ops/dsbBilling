<div class="row g-4">
    <div class="col-12">
        <div class="card card-stat bg-white dark:bg-slate-800">
            <div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    <h2 class="h3 mb-1 fw-bold">Kontrak</h2>
                    <p class="text-muted mb-0">Total: <span class="text-primary fw-bold">{{ $contracts->total() }}</span> Kontrak</p>
                </div>
                <button class="btn btn-primary">
                    <i class="bi bi-plus me-1"></i> Tambah Kontrak
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
                            <th>No Kontrak</th>
                            <th>Paket</th>
                            <th>Tanggal Mulai</th>
                            <th>Tanggal Berakhir</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($contracts as $contract)
                            <tr>
                                <td>
                                    <div class="fw-bold">{{ $contract->customer->name ?? '-' }}</div>
                                </td>
                                <td><div class="text-muted small">{{ $contract->contract_number }}</div></td>
                                <td><div class="text-muted small">{{ $contract->service?->name ?? '-' }}</div></td>
                                <td><div class="text-muted small">{{ $contract->start_date->format('d/m/Y') }}</div></td>
                                <td><div class="text-muted small">{{ $contract->end_date->format('d/m/Y') }}</div></td>
                                <td>
                                    <span class="badge {{ $contract->status == 'active' ? 'bg-success' : 'bg-warning' }} badge-status">
                                        {{ ucfirst($contract->status) }}
                                    </span>
                                </td>
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
                        Menampilkan {{ $contracts->firstItem() }} - {{ $contracts->lastItem() }} dari {{ $contracts->total() }}
                    </div>
                    <div>
                        {{ $contracts->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
