@extends('layouts.app')

@section('title', 'Detail Pengguna')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Detail Pengguna</h2>
        <a href="{{ route('users.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="mb-3">
                <label class="form-label fw-bold">Nama</label>
                <p class="form-control-plaintext">{{ $user->name }}</p>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Email</label>
                <p class="form-control-plaintext">{{ $user->email }}</p>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Role</label>
                <p class="form-control-plaintext">
                    @foreach($user->roles as $role)
                        <span class="badge bg-primary">{{ $role->name }}</span>
                    @empty
                        <span class="badge bg-secondary">-</span>
                    @endforeach
                </p>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Dibuat Pada</label>
                <p class="form-control-plaintext">{{ $user->created_at->format('d/m/Y H:i:s') }}</p>
            </div>

            <div class="d-flex gap-2">
                <a href="{{ route('users.edit', $user->id) }}" class="btn btn-warning">
                    <i class="bi bi-pencil me-1"></i> Edit
                </a>
                <a href="{{ route('users.index') }}" class="btn btn-secondary">
                    Kembali
                </a>
            </div>
        </div>
    </div>
@endsection
