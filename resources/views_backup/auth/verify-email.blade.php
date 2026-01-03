@extends('layouts.app')

@section('title', 'Verifikasi Email')

@section('styles')
<style>
    /* Styling Khusus Halaman Verifikasi agar Modern */
    .verify-wrapper {
        min-height: 80vh; /* Agar posisi di tengah vertikal */
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }
    .verify-card {
        border: none;
        border-radius: 20px; /* Sudut membulat modern */
        box-shadow: 0 15px 35px rgba(0,0,0,0.08); /* Shadow halus tapi dalam */
        background: #fff;
        overflow: hidden;
        position: relative;
    }
    .verify-icon-box {
        width: 90px;
        height: 90px;
        background: #fffbeb; /* Warna background kuning lembut */
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 25px;
        color: #f59e0b; /* Warna ikon kuning/amber */
        font-size: 2.5rem;
        box-shadow: 0 5px 15px rgba(245, 158, 11, 0.2);
    }
    .btn-gacor {
        border-radius: 12px;
        padding: 14px;
        font-weight: 700;
        font-size: 0.95rem;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        box-shadow: 0 4px 10px rgba(245, 158, 11, 0.3);
        transition: all 0.3s;
    }
    .btn-gacor:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(245, 158, 11, 0.4);
    }
    .text-email-highlight {
        background-color: #f3f4f6;
        padding: 2px 8px;
        border-radius: 4px;
        color: #1f2937;
        font-weight: 700;
    }
</style>
@endsection

@section('content')
<div class="verify-wrapper">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                
                <div class="verify-card p-5 text-center">
                    
                    <div class="verify-icon-box animate__animated animate__bounceIn">
                        <i class="fas fa-envelope-open-text"></i>
                    </div>

                    <h2 class="font-weight-bold text-dark mb-3">Verifikasi Email Anda</h2>
                    
                    <p class="text-muted mb-4" style="line-height: 1.6;">
                        Kami telah mengirimkan tautan verifikasi ke: <br>
                        <span class="text-email-highlight mt-2 d-inline-block">{{ auth()->user()->email }}</span>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <div class="alert alert-success border-0 shadow-sm mb-4" style="border-radius: 10px; font-size: 0.9rem;">
                            <i class="fas fa-check-circle mr-2"></i> Tautan verifikasi baru berhasil dikirim!
                        </div>
                    @endif

                    <p class="small text-secondary mb-4">
                        Belum menerima email? Coba periksa folder <strong>Spam</strong> atau klik tombol di bawah untuk kirim ulang.
                    </p>

                    <form method="POST" action="{{ route('verification.send') }}">
                        @csrf
                        <button type="submit" class="btn btn-warning btn-block text-white btn-gacor">
                            <i class="fas fa-paper-plane mr-2"></i> Kirim Ulang Verifikasi
                        </button>
                    </form>

                    <div class="mt-4 pt-4 border-top">
                        <div class="row">
                            <div class="col-6 text-left">
                                <a href="{{ url()->previous() !== url()->current() ? url()->previous() : url('/') }}" class="text-muted small font-weight-bold" style="text-decoration: none;">
                                    <i class="fas fa-arrow-left mr-1"></i> Kembali
                                </a>
                            </div>
                            <div class="col-6 text-right">
                                <form action="{{ route('logout') }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-link p-0 text-muted small font-weight-bold text-decoration-none">
                                        Logout <i class="fas fa-sign-out-alt ml-1"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </div>
</div>
@endsection