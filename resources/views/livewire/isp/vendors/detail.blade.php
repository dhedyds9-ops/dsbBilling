<div>
    <div class="modal fade {{ $isOpen ? 'show d-block' : '' }}" tabindex="-1" style="{{ $isOpen ? 'background: rgba(0,0,0,0.5);' : '' }}">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail Vendor</h5>
                    <button type="button" class="btn-close" wire:click="close"></button>
                </div>
                <div class="modal-body">
                    @if($vendor)
                        <table class="table table-sm table-borderless">
                            <tr><th class="text-muted" style="width: 140px;">Kode</th><td><strong>{{ $vendor->code }}</strong></td></tr>
                            <tr><th class="text-muted">Nama</th><td><strong>{{ $vendor->name }}</strong></td></tr>
                            <tr><th class="text-muted">Telepon</th><td>{{ $vendor->phone ?: '-' }}</td></tr>
                            <tr><th class="text-muted">Email</th><td>{{ $vendor->email ?: '-' }}</td></tr>
                            <tr><th class="text-muted">Kontak Person</th><td>{{ $vendor->contact_person ?: '-' }}</td></tr>
                            <tr><th class="text-muted">Alamat</th><td>{{ $vendor->address ?: '-' }}</td></tr>
                            <tr><th class="text-muted">Deskripsi</th><td>{{ $vendor->description ?: '-' }}</td></tr>
                            <tr><th class="text-muted">Status</th><td>
                                <span class="badge {{ $vendor->status === 'active' ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $vendor->status === 'active' ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td></tr>
                        </table>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" wire:click="close" class="btn btn-secondary">Tutup</button>
                </div>
            </div>
        </div>
    </div>
</div>
