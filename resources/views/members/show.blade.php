@extends('layouts.app')

@section('title', 'Detail Anggota')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Detail Anggota</h2>
        <a href="{{ route('members.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <dl class="row">
                <dt class="col-sm-3">Kode Anggota</dt>
                <dd class="col-sm-9">{{ $member->code }}</dd>

                <dt class="col-sm-3">Nama</dt>
                <dd class="col-sm-9">{{ $member->name }}</dd>

                <dt class="col-sm-3">Telepon</dt>
                <dd class="col-sm-9">{{ $member->phone }}</dd>

                <dt class="col-sm-3">Email</dt>
                <dd class="col-sm-9">{{ $member->email ?? '-' }}</dd>

                <dt class="col-sm-3">Alamat</dt>
                <dd class="col-sm-9">{{ $member->address ?? '-' }}</dd>

                <dt class="col-sm-3">Status</dt>
                <dd class="col-sm-9">
                    <span class="badge {{ $member->status === 'active' ? 'bg-success' : 'bg-secondary' }}">
                        {{ $member->status === 'active' ? 'Aktif' : 'Nonaktif' }}
                    </span>
                </dd>

                <dt class="col-sm-3">Dibuat Pada</dt>
                <dd class="col-sm-9">{{ $member->created_at->format('d/m/Y H:i') }}</dd>

                <dt class="col-sm-3">Diperbarui Pada</dt>
                <dd class="col-sm-9">{{ $member->updated_at->format('d/m/Y H:i') }}</dd>
            </dl>

            <div class="d-flex gap-2 mt-4">
                <a href="{{ route('members.edit', $member) }}" class="btn btn-warning">
                    <i class="bi bi-pencil me-1"></i> Edit
                </a>
                <form action="{{ route('members.destroy', $member) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus anggota ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash me-1"></i> Hapus
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection
