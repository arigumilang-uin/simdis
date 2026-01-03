@extends('layouts.guest')

@section('title', '403 - Akses Ditolak')

@section('content')
<div class="min-h-screen flex items-center justify-center p-4 bg-gradient-to-br from-slate-100 to-red-50">
    <div class="text-center max-w-md">
        {{-- Illustration --}}
        <div class="mb-8">
            <div class="w-32 h-32 mx-auto bg-gradient-to-br from-red-100 to-red-200 rounded-full flex items-center justify-center">
                <x-ui.icon name="shield-x" size="64" class="text-red-500" />
            </div>
        </div>
        
        {{-- Content --}}
        <h1 class="text-2xl font-bold text-gray-800 mb-3">Akses Ditolak</h1>
        <p class="text-gray-500 mb-8">
            Maaf, Anda tidak memiliki izin untuk mengakses halaman ini. 
            Silakan hubungi administrator jika Anda merasa ini adalah kesalahan.
        </p>
        
        {{-- Actions --}}
        <div class="flex flex-col sm:flex-row gap-3 justify-center">
            <a href="{{ url()->previous() }}" class="btn btn-secondary">
                <x-ui.icon name="chevron-left" size="18" />
                <span>Kembali</span>
            </a>
            <a href="{{ route('dashboard') }}" class="btn btn-primary">
                <x-ui.icon name="home" size="18" />
                <span>Ke Dashboard</span>
            </a>
        </div>
    </div>
</div>
@endsection
