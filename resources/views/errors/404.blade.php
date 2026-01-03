@extends('layouts.guest')

@section('title', '404 - Halaman Tidak Ditemukan')

@section('content')
<div class="min-h-screen flex items-center justify-center p-4 bg-gradient-to-br from-slate-100 to-blue-50">
    <div class="text-center max-w-md">
        {{-- Illustration --}}
        <div class="mb-8">
            <div class="w-32 h-32 mx-auto bg-gradient-to-br from-slate-200 to-slate-300 rounded-full flex items-center justify-center">
                <span class="text-6xl font-bold text-slate-500">404</span>
            </div>
        </div>
        
        {{-- Content --}}
        <h1 class="text-2xl font-bold text-gray-800 mb-3">Halaman Tidak Ditemukan</h1>
        <p class="text-gray-500 mb-8">
            Maaf, halaman yang Anda cari tidak dapat ditemukan atau telah dipindahkan.
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
