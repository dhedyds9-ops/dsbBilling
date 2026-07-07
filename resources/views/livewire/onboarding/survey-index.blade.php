<div class="row g-4">
    <div class="col-12">
        <div class="card card-stat bg-white">
            <div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    <h2 class="h3 mb-1 fw-bold">Site Survey</h2>
                    <p class="text-muted mb-0">Total: <span class="text-primary fw-bold">{{ $surveys->total() }}</span> Survey</p>
                </div>
                <button class="btn btn-primary">
                    <i class="bi bi-plus me-1"></i> Tambah Survey
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
                            <th>ODP</th>
                            <th>Jarak (m)</th>
                            <th>Est Kabel (m)</th>
                            <th>Status</th>
                            <th>Tanggal</th>
                            <th>Aksi</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($surveys as $survey)
                            <tr>
                                <td>
                                    <div class="fw-bold">{{ $survey->prospect->name ?? '-' }}</div>
                                </td>
                                <td><div class="text-muted small">{{ Str::limit($survey->address, 30) }}</div></td>
                                <td><div class="text-muted small">{{ $survey->odp?->name ?? '-' }}</div></td>
                                <td><div class="text-muted small">{{ $survey->distance }} m</div></td>
                                <td><div class="text-muted small">{{ $survey->estimated_cable }} m</div></td>
                                <td>
                                    <span class="badge {{ $survey->status == 'completed' ? 'bg-success' : 'bg-warning' }} badge-status">
                                        {{ ucfirst($survey->status) }}
                                    </span>
                                </td>
                                <td><div class="text-muted small">{{ $survey->created_at->format('d/m/Y') }}</div></td>
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
                        Menampilkan {{ $surveys->firstItem() }} - {{ $surveys->lastItem() }} dari {{ $surveys->total() }}
                    </div>
                    <div>
                        {{ $surveys->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
