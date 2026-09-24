<div class="row g-4">
    <div class="col-12">
        <div class="card card-stat bg-white dark:bg-slate-800">
            <div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    <h2 class="h3 mb-1 fw-bold">Lead Management</h2>
                    <p class="text-muted mb-0">Total: <span class="text-primary fw-bold">{{ $leads->total() }}</span> Lead</p>
                </div>
                <button class="btn btn-primary" onclick="window.location.href='{{ route('onboarding.leads.create') }}'">
                    <i class="bi bi-plus me-1"></i> Tambah Lead
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
                            <th>Nama</th>
                            <th>Telepon</th>
                            <th>Email</th>
                            <th>Alamat</th>
                            <th>Status</th>
                            <th>Tanggal</th>
                            <th>Aksi</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($leads as $lead)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm bg-gradient-primary rounded-circle d-flex align-items-center justify-content-center text-white fw-bold me-2">
                                            {{ substr($lead->name[0] ?? 'U', 0, 1) }}
                                        </div>
                                        <div>
                                            <div class="fw-bold">{{ $lead->name }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td><div class="text-muted small">{{ $lead->phone }}</div></td>
                                <td><div class="text-muted small">{{ $lead->email }}</div></td>
                                <td><div class="text-muted small">{{ Str::limit($lead->address, 30) }}</div></td>
                                <td>
                                    <span class="badge {{ $lead->status == 'new' ? 'bg-primary' : ($lead->status == 'converted' ? 'bg-success' : 'bg-warning') }} badge-status">
                                        {{ ucfirst($lead->status) }}
                                    </span>
                                </td>
                                <td><div class="text-muted small">{{ $lead->created_at->format('d/m/Y') }}</div></td>
                                <td>
                                    <div class="d-flex gap-1">
                                        @if ($lead->status == 'new')
                                            <button class="btn btn-sm btn-success" wire:click="convertToProspect({{ $lead->id }})">
                                                <i class="bi bi-arrow-right-circle"></i> Convert
                                            </button>
                                        @endif
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
                        Menampilkan {{ $leads->firstItem() }} - {{ $leads->lastItem() }} dari {{ $leads->total() }}
                    </div>
                    <div>
                        {{ $leads->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
