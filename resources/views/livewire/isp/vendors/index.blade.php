<div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Vendor Management</h2>
        <div class="d-flex gap-2">
            <button wire:click="$dispatch('showVendorForm')" class="btn btn-primary">
                <i class="bi bi-plus-circle me-1"></i> Buat Vendor Baru
            </button>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form wire:submit.prevent="render">
                <div class="row g-3">
                    <div class="col-md-4">
                        <input type="text" wire:model.live="search" class="form-control" placeholder="Cari kode, nama, atau deskripsi...">
                    </div>
                    <div class="col-md-3">
                        <select wire:model.live="status" class="form-select">
                            <option value="">Semua Status</option>
                            <option value="active">Aktif</option>
                            <option value="inactive">Nonaktif</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex gap-2 align-items-center">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" wire:model.live="showTrashed" id="showTrashedVendor">
                            <label class="form-check-label" for="showTrashedVendor">Tampilkan Dihapus</label>
                        </div>
                        <button type="button" wire:click="resetFilters" class="btn btn-secondary">Reset</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if(count($selectedIds) > 0)
        <div class="card mb-4 border-primary">
            <div class="card-body bg-light">
                <div class="d-flex gap-2 align-items-center">
                    <span class="fw-bold">{{ count($selectedIds) }} vendor dipilih</span>
                    <div class="ms-auto d-flex gap-2">
                        @if(!$showTrashed)
                            <button wire:click="bulkActivate" class="btn btn-success btn-sm">
                                <i class="bi bi-check-circle"></i> Aktifkan
                            </button>
                            <button wire:click="bulkDeactivate" class="btn btn-warning btn-sm">
                                <i class="bi bi-x-circle"></i> Nonaktifkan
                            </button>
                            <button wire:click="bulkDelete" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus vendor yang dipilih?')">
                                <i class="bi bi-trash"></i> Hapus
                            </button>
                        @else
                            <span class="text-muted">Aksi tidak tersedia untuk vendor yang dihapus</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 40px;">
                                <input type="checkbox" wire:model.live="selectAll">
                            </th>
                            <th>Kode</th>
                            <th>Nama</th>
                            <th>Telepon</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th style="width: 220px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($vendors as $vendor)
                            <tr class="{{ $vendor->trashed() ? 'table-secondary' : '' }}">
                                <td>
                                    <input type="checkbox" wire:model.live="selectedIds" value="{{ $vendor->id }}">
                                </td>
                                <td>{{ $vendor->code }}</td>
                                <td>{{ $vendor->name }}</td>
                                <td>{{ $vendor->phone ?: '-' }}</td>
                                <td>{{ $vendor->email ?: '-' }}</td>
                                <td>
                                    <span class="badge {{ $vendor->status === 'active' ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $vendor->status === 'active' ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                    @if($vendor->trashed())
                                        <span class="badge bg-danger ms-1">Dihapus</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <button wire:click="$dispatch('showVendorDetail', {{ $vendor->id }})" class="btn btn-sm btn-info">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        @if(!$vendor->trashed())
                                            <button wire:click="$dispatch('showVendorForm', {{ $vendor->id }})" class="btn btn-sm btn-warning">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button wire:click="delete({{ $vendor->id }})" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus vendor ini?')">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        @else
                                            <button wire:click="restore({{ $vendor->id }})" class="btn btn-sm btn-primary">
                                                <i class="bi bi-arrow-counterclockwise"></i> Restore
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    Tidak ada data Vendor
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $vendors->links() }}
        </div>
    </div>
</div>
