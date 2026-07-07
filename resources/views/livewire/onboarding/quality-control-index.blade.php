<div class="row g-4">
    <div class="col-12">
        <div class="card card-stat bg-white">
            <div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    <h2 class="h3 mb-1 fw-bold">Quality Control</h2>
                    <p class="text-muted mb-0">Total: <span class="text-primary fw-bold">{{ $qualityControls->total() }}</span> QC</p>
                </div>
                <button class="btn btn-primary">
                    <i class="bi bi-plus me-1"></i> Tambah QC
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
                            <th>Instalasi</th>
                            <th>Status QC</th>
                            <th>Catatan</th>
                            <th>Tanggal</th>
                            <th>Aksi</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($qualityControls as $qc)
                            <tr>
                                <td>
                                    <div class="fw-bold">{{ $qc->installation->customer->name ?? '-' }}</div>
                                </td>
                                <td>
                                    <span class="badge {{ $qc->status == 'passed' ? 'bg-success' : 'bg-danger' }} badge-status">
                                        {{ ucfirst($qc->status) }}
                                    </span>
                                </td>
                                <td><div class="text-muted small">{{ Str::limit($qc->notes, 50) }}</div></td>
                                <td><div class="text-muted small">{{ $qc->created_at->format('d/m/Y') }}</div></td>
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
                        Menampilkan {{ $qualityControls->firstItem() }} - {{ $qualityControls->lastItem() }} dari {{ $qualityControls->total() }}
                    </div>
                    <div>
                        {{ $qualityControls->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
