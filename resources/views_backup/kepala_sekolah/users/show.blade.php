@extends('layouts.app')

@section('title', 'Detail Pengguna')

@section('content')
<div class="container-fluid">

    <div class="row mb-3">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="text-dark font-weight-bold">
                    <i class="fas fa-user-circle mr-2"></i> Detail Pengguna
                </h3>
                <a href="{{ route('kepala-sekolah.users.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-left mr-1"></i> Kembali
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- User Info -->
        <div class="col-md-6">
            <div class="card card-outline card-primary shadow-sm">
                <div class="card-header bg-primary">
                    <h6 class="card-title text-white font-weight-bold mb-0">
                        <i class="fas fa-id-card mr-2"></i> Informasi Pengguna
                    </h6>
                </div>

                <div class="card-body">
                    <div class="form-group">
                        <label class="font-weight-bold">Nama</label>
                        <p class="text-primary font-weight-bold text-lg">{{ $user->nama }}</p>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold">Username</label>
                        <p><code>{{ $user->username }}</code></p>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold">Email</label>
                        <p><a href="mailto:{{ $user->email }}">{{ $user->email }}</a></p>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold">Role</label>
                        <p><span class="badge badge-secondary p-2">{{ $user->role?->nama_role ?? 'N/A' }}</span></p>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold">Status Akun</label>
                        <p>
                            @if($user->is_active ?? true)
                                <span class="badge badge-success p-2">Aktif</span>
                            @else
                                <span class="badge badge-danger p-2">Nonaktif</span>
                            @endif
                        </p>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold">Terakhir Login</label>
                        <p>{{ $user->last_login_at ? formatDateTime($user->last_login_at) : 'Belum login' }}</p>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold">Dibuat pada</label>
                        <p>{{ $user->created_at->format('d M Y H:i') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="col-md-6">
            <!-- Reset Password -->
            <div class="card card-outline card-warning shadow-sm mb-3">
                <div class="card-header bg-warning">
                    <h6 class="card-title text-white font-weight-bold mb-0">
                        <i class="fas fa-key mr-2"></i> Reset Password
                    </h6>
                </div>

                <form action="{{ route('kepala-sekolah.users.reset-password', $user->id) }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="form-group">
                            <label for="new_password" class="font-weight-bold">Password Baru</label>
                            <input type="password" id="new_password" name="new_password" class="form-control form-control-sm" required>
                            <small class="form-text text-muted">Minimal 6 karakter</small>
                        </div>

                        <div class="form-group">
                            <label for="new_password_confirmation" class="font-weight-bold">Konfirmasi Password</label>
                            <input type="password" id="new_password_confirmation" name="new_password_confirmation" class="form-control form-control-sm" required>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-warning btn-block font-weight-bold">
                            <i class="fas fa-redo mr-1"></i> Reset Password
                        </button>
                    </div>
                </form>
            </div>

            <!-- Toggle Status -->
            <div class="card card-outline card-danger shadow-sm">
                <div class="card-header bg-danger">
                    <h6 class="card-title text-white font-weight-bold mb-0">
                        <i class="fas fa-lock mr-2"></i> Aktifasi / Nonaktifkan
                    </h6>
                </div>

                <div class="card-body">
                    <p class="text-muted">
                        @if($user->is_active ?? true)
                            Akun ini sedang <strong>Aktif</strong>. User dapat login dan menggunakan sistem.
                        @else
                            Akun ini sedang <strong>Nonaktif</strong>. User tidak dapat login ke sistem.
                        @endif
                    </p>
                </div>

                <div class="card-footer">
                    <form action="{{ route('kepala-sekolah.users.toggle-status', $user->id) }}" method="POST" style="display: inline;">
                        @csrf
                        @method('PUT')
                        <button type="submit" class="btn btn-danger btn-block font-weight-bold" 
                                onclick="return confirm('Apakah Anda yakin?')">
                            <i class="fas {{ ($user->is_active ?? true) ? 'fa-lock' : 'fa-lock-open' }} mr-1"></i>
                            {{ ($user->is_active ?? true) ? 'Nonaktifkan Akun' : 'Aktifkan Akun' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
