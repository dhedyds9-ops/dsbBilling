<div class="row g-4">
    <div class="col-12">
        <div class="card card-stat bg-white">
            <div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    <h2 class="h3 mb-1 fw-bold">Coverage Check</h2>
                    <p class="text-muted mb-0">Total: <span class="text-primary fw-bold">{{ $coverageChecks->total() }}</span> Coverage Check</p>
                </div>
                <button class="btn btn-primary">
                    <i class="bi bi-plus me-1"></i> Tambah Coverage Check
                </button>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card card-stat bg-white">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                        <tr>
                            <th>Prospek</th>
                            <th>Alamat</th>
                            <th>GPS (Lat/Lng)</th>
                            <th>ODP</th>
                            <th>Status</th>
                            <th>Tanggal</th>
                            <th>Aksi</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($coverageChecks as $check)
                            <tr>
                                <td>
                                    <div class="fw-bold">{{ $check->prospect->name ?? '-' }}</div>
                                </td>
                                <td><div class="text-muted small">{{ Str::limit($check->address, 30) }}</div></td>
                                <td><div class="text-muted small">{{ $check->latitude }}, {{ $check->longitude }}</div></td>
                                <td><div class="text-muted small">{{ $check->odp?->name ?? '-' }}</div></td>
                                <td>
                                    <span class="badge {{ $check->status == 'covered' ? 'bg-success' : ($check->status == 'not_covered' ? 'bg-danger' : 'bg-warning') }} badge-status">
                                        {{ ucfirst(str_replace('_', ' ', $check->status)) }}
                                    </span>
                                </td>
                                <td><div class="text-muted small">{{ $check->created_at->format('d/m/Y') }}</div></td>
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
                        Menampilkan {{ $coverageChecks->firstItem() }} - {{ $coverageChecks->lastItem() }} dari {{ $coverageChecks->total() }}
                    </div>
                    <div>
                        {{ $coverageChecks->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
