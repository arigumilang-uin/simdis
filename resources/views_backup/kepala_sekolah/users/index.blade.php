@extends('layouts.app')

@section('title', 'Manajemen Pengguna')

@section('content')
<div class="container-fluid">

    <div class="row mb-3">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="text-dark font-weight-bold">
                    <i class="fas fa-users mr-2"></i> Manajemen Pengguna
                </h3>
                <a href="{{ route('dashboard.kepsek') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-left mr-1"></i> Kembali
                </a>
            </div>
        </div>
    </div>

    <!-- Filter & Search -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card card-outline card-primary shadow-sm">
                <div class="card-header bg-primary">
                    <h6 class="card-title text-white font-weight-bold mb-0">
                        <i class="fas fa-filter mr-2"></i> Filter & Pencarian
                    </h6>
                </div>

                <form method="GET" action="{{ route('kepala-sekolah.users.index') }}" class="form-inline p-3">
                    <div class="form-group mr-2 flex-grow-1">
                        <input type="text" name="search" class="form-control form-control-sm w-100" 
                               placeholder="Cari nama, username, atau email..." 
                               value="{{ request('search') }}">
                    </div>

                    <select name="role_id" class="form-control form-control-sm mr-2" style="width: 200px;">
                        <option value="">-- Semua Role --</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}" {{ request('role_id') == $role->id ? 'selected' : '' }}>
                                {{ $role->nama_role }}
                            </option>
                        @endforeach
                    </select>

                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fas fa-search mr-1"></i> Cari
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Users Table -->
    <div class="row">
        <div class="col-12">
            <div class="card card-outline card-info">
                <div class="card-header bg-info">
                    <h6 class="card-title text-white font-weight-bold mb-0">
                        Total: {{ $users->total() }} Pengguna
                    </h6>
                </div>

                <div class="card-body table-responsive p-0">
                    @if($users->isEmpty())
                        <div class="text-center p-5 text-muted">
                            <i class="fas fa-users-slash fa-4x mb-3"></i>
                            <p>Tidak ada pengguna ditemukan.</p>
                        </div>
                    @else
                        <table class="table table-sm table-striped table-hover">
                            <thead class="bg-light">
                                <tr>
                                    <th>Nama</th>
                                    <th>Username</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center">Terakhir Login</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($users as $user)
                                <tr>
                                    <td><strong>{{ $user->nama }}</strong></td>
                                    <td><code>{{ $user->username }}</code></td>
                                    <td><small class="text-muted">{{ $user->email }}</small></td>
                                    <td>
                                        <span class="badge badge-secondary">{{ $user->role?->nama_role ?? 'N/A' }}</span>
                                    </td>
                                    <td class="text-center">
                                        @if($user->is_active ?? true)
                                            <span class="badge badge-success">Aktif</span>
                                        @else
                                            <span class="badge badge-danger">Nonaktif</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <small class="text-muted">
                                            {{ $user->last_login_at ? formatRelative($user->last_login_at) : 'Belum login' }}
                                        </small>
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('kepala-sekolah.users.show', $user->id) }}" class="btn btn-primary btn-xs" title="Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>

                <!-- Pagination -->
                <div class="card-footer">
                    {{ $users->links('pagination::bootstrap-4') }}
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
