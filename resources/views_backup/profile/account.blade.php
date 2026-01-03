@extends('layouts.app')

@section('title', 'Akun Saya')

@section('content')
<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card card-info card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-user-cog mr-1"></i> Pengaturan Akun</h3>
            </div>
            <form method="POST" action="{{ route('account.update') }}">
                @csrf
                @method('PUT')
                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if(! $user->hasVerifiedEmail())
                        <div class="alert alert-warning alert-dismissible fade show" role="alert">
                            <h5><i class="icon fas fa-exclamation-triangle"></i> Email Belum Terverifikasi</h5>
                            <p class="mb-1">
                                Email akun Anda (<strong>{{ $user->email }}</strong>) belum terverifikasi.
                                Sistem tetap dapat digunakan, namun sebaiknya verifikasi email untuk memastikan
                                reset password & notifikasi berjalan lancar.
                            </p>
                            <form method="POST" action="{{ route('verification.send') }}" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-dark">
                                    <i class="fas fa-paper-plane mr-1"></i> Kirim Ulang Link Verifikasi
                                </button>
                            </form>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    <div class="form-group">
                        <label>Username <span class="text-danger">*</span></label>
                        <input
                            type="text"
                            name="username"
                            class="form-control @error('username') is-invalid @enderror"
                            value="{{ old('username', $user->username) }}"
                            required
                        >
                        @error('username')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                        <small class="form-text text-muted">
                            Username digunakan untuk login. Anda bisa mengubahnya menjadi nama Anda agar lebih mudah diingat.
                            <strong>Username harus unik</strong> dan tidak boleh sama dengan username user lain.
                        </small>
                    </div>

                    <div class="form-group">
                        <label>Email Akun <span class="text-danger">*</span></label>
                        <input
                            type="email"
                            name="email"
                            class="form-control @error('email') is-invalid @enderror"
                            value="{{ old('email', $user->email) }}"
                            required
                        >
                        @error('email')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                        <small class="form-text text-muted">
                            Email ini digunakan untuk reset password dan notifikasi penting.
                        </small>
                        @if(! $user->hasVerifiedEmail())
                            <small class="text-warning d-block mt-1">
                                <i class="fas fa-exclamation-triangle"></i>
                                Email Anda belum terverifikasi. Silakan cek inbox/spam atau kirim ulang link verifikasi.
                            </small>
                        @endif
                    </div>

                    @if (! $isWaliMurid)
                        <div class="form-group">
                            <label>Nomor HP / Kontak (Opsional)</label>
                            <input
                                type="text"
                                name="phone"
                                class="form-control @error('phone') is-invalid @enderror"
                                value="{{ old('phone', $user->phone) }}"
                                placeholder="Contoh: 0812xxxxxxx"
                            >
                            @error('phone')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                            <small class="form-text text-muted">
                                Kontak ini digunakan sebagai informasi internal agar pihak sekolah mudah menghubungi Anda.
                            </small>
                        </div>
                    @else
                        <div class="form-group">
                            <label>Nomor HP / WA Wali Murid (Dari Data Siswa)</label>
                            <input
                                type="text"
                                class="form-control"
                                value="{{ $waliMuridContact ?? 'Belum diisi pada data siswa' }}"
                                disabled
                            >
                            <small class="form-text text-muted">
                                Nomor kontak wali murid dikelola melalui data siswa.
                                Jika ingin memperbarui, silakan hubungi wali kelas atau operator sekolah.
                            </small>
                        </div>
                    @endif
                </div>
                <div class="card-footer d-flex justify-content-between">
                    <a href="{{ url()->previous() !== url()->current() ? url()->previous() : url('/') }}" class="btn btn-default">
                        <i class="fas fa-arrow-left mr-1"></i> Kembali
                    </a>
                    <div class="d-flex align-items-center">
                        @if(! $user->hasVerifiedEmail())
                            <form method="POST" action="{{ route('verification.send') }}" class="mr-2">
                                @csrf
                                <button type="submit" class="btn btn-outline-warning btn-sm">
                                    <i class="fas fa-paper-plane mr-1"></i> Kirim Ulang Verifikasi
                                </button>
                            </form>
                        @endif
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-1"></i> Simpan Perubahan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection



