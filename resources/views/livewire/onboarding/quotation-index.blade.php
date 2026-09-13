<div class="row g-4">
    <div class="col-12">
        <div class="card card-stat bg-white dark:bg-slate-800">
            <div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    <h2 class="h3 mb-1 fw-bold">Quotation</h2>
                    <p class="text-muted mb-0">Total: <span class="text-primary fw-bold">{{ $quotations->total() }}</span> Quotation</p>
                </div>
                <button class="btn btn-primary">
                    <i class="bi bi-plus me-1"></i> Tambah Quotation
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
                            <th>Prospek</th>
                            <th>Paket</th>
                            <th>Total Harga</th>
                            <th>Status Approval</th>
                            <th>Tanggal</th>
                            <th>Aksi</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($quotations as $quotation)
                            <tr>
                                <td>
                                    <div class="fw-bold">{{ $quotation->prospect->name ?? '-' }}</div>
                                </td>
                                <td><div class="text-muted small">{{ $quotation->service?->name ?? '-' }}</div></td>
                                <td><div class="fw-bold text-primary">Rp {{ number_format($quotation->total_amount, 0, ',', '.') }}</div></td>
                                <td>
                                    <span class="badge {{ $quotation->status == 'approved' ? 'bg-success' : ($quotation->status == 'rejected' ? 'bg-danger' : 'bg-warning') }} badge-status">
                                        {{ ucfirst($quotation->status) }}
                                    </span>
                                </td>
                                <td><div class="text-muted small">{{ $quotation->created_at->format('d/m/Y') }}</div></td>
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
                        Menampilkan {{ $quotations->firstItem() }} - {{ $quotations->lastItem() }} dari {{ $quotations->total() }}
                    </div>
                    <div>
                        {{ $quotations->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
