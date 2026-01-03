@extends('layouts.app')

@section('title', 'Lengkapi Profil Akun')

@section('content')
<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">Lengkapi Profil Akun</h3>
            </div>
            <form method="POST" action="{{ route('profile.complete.store') }}">
                @csrf
                <div class="card-body">
                    <p class="text-muted">
                        Untuk keamanan akun Anda, silakan ubah username dan password default yang diberikan sistem.
                        Lengkapi juga email dan kontak untuk kemudahan reset password dan notifikasi.
                    </p>

                    <!-- Username -->
                    <div class="form-group">
                        <label for="username">Username <span class="text-danger">*</span></label>
                        <input
                            type="text"
                            name="username"
                            id="username"
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
                            Username saat ini: <strong>{{ $user->username }}</strong>. 
                            Silakan ubah jika ingin menggunakan username yang lebih mudah diingat.
                        </small>
                    </div>

                    <!-- Password -->
                    <div class="form-group">
                        <label for="password">Password Baru <span class="text-danger">*</span></label>
                        <input
                            type="password"
                            name="password"
                            id="password"
                            class="form-control @error('password') is-invalid @enderror"
                            required
                        >
                        @error('password')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                        <small class="form-text text-muted">
                            Minimal 8 karakter. Gunakan kombinasi huruf, angka, dan simbol untuk keamanan lebih baik.
                        </small>
                    </div>

                    <!-- Konfirmasi Password -->
                    <div class="form-group">
                        <label for="password_confirmation">Konfirmasi Password <span class="text-danger">*</span></label>
                        <input
                            type="password"
                            name="password_confirmation"
                            id="password_confirmation"
                            class="form-control"
                            required
                        >
                        <small class="form-text text-muted">
                            Ketik ulang password baru Anda untuk konfirmasi.
                        </small>
                    </div>

                    <hr>

                    <!-- Email -->
                    <div class="form-group">
                        <label for="email">Email Aktif <span class="text-danger">*</span></label>
                        <input
                            type="email"
                            name="email"
                            id="email"
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
                            Gunakan email yang benar-benar aktif. Email ini akan digunakan
                            untuk reset password dan notifikasi penting.
                        </small>
                    </div>

                    @if (! $isWaliMurid)
                        <div class="form-group">
                            <label for="phone">Nomor HP / WA (Opsional)</label>
                            <input
                                type="text"
                                name="phone"
                                id="phone"
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
                                Opsional, namun disarankan. Nomor ini disimpan hanya sebagai
                                kontak yang dapat dilihat pihak sekolah (tidak digunakan untuk
                                broadcast WA otomatis saat ini).
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
                                Kontak utama wali murid diambil dari data siswa
                                (kolom <code>nomor_hp_wali_murid</code>). Jika ada perubahan,
                                silakan hubungi wali kelas atau operator sekolah.
                            </small>
                        </div>
                    @endif
                </div>
                <div class="card-footer text-right">
                    <button type="submit" class="btn btn-primary">
                        Simpan &amp; Lanjut
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection





