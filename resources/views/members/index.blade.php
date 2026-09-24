@extends('layouts.app')

@section('title', 'Master Anggota')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Daftar Anggota</h2>
        <a href="{{ route('members.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i> Tambah Anggota
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <div class="w-full overflow-x-auto">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Kode</th>
                            <th>Nama</th>
                            <th>Telepon</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th style="width: 220px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($members as $member)
                            <tr>
                                <td>{{ $member->code }}</td>
                                <td>{{ $member->name }}</td>
                                <td>{{ $member->phone }}</td>
                                <td>{{ $member->email ?? '-' }}</td>
                                <td>
                                    <span class="badge {{ $member->status === 'active' ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $member->status === 'active' ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('members.show', $member->id) }}" class="btn btn-sm btn-info">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('members.edit', $member->id) }}" class="btn btn-sm btn-warning">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('members.destroy', $member->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus anggota ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{ $members->links() }}
        </div>
    </div>
@endsection
