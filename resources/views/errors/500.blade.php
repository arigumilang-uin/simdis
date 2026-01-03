@extends('layouts.guest')

@section('title', '500 - Server Error')

@section('content')
<div class="min-h-screen flex items-center justify-center p-4 bg-gradient-to-br from-slate-100 to-amber-50">
    <div class="text-center max-w-md">
        {{-- Illustration --}}
        <div class="mb-8">
            <div class="w-32 h-32 mx-auto bg-gradient-to-br from-amber-100 to-amber-200 rounded-full flex items-center justify-center">
                <x-ui.icon name="alert-triangle" size="64" class="text-amber-600" />
            </div>
        </div>
        
        {{-- Content --}}
        <h1 class="text-2xl font-bold text-gray-800 mb-3">Terjadi Kesalahan</h1>
        <p class="text-gray-500 mb-8">
            Maaf, terjadi kesalahan pada server. Tim kami sedang menangani masalah ini. 
            Silakan coba lagi beberapa saat.
        </p>
        
        {{-- Actions --}}
        <div class="flex flex-col sm:flex-row gap-3 justify-center">
            <button onclick="window.location.reload()" class="btn btn-secondary">
                <x-ui.icon name="refresh-cw" size="18" />
                <span>Muat Ulang</span>
            </button>
            <a href="{{ route('dashboard') }}" class="btn btn-primary">
                <x-ui.icon name="home" size="18" />
                <span>Ke Dashboard</span>
            </a>
        </div>
    </div>
</div>
@endsection
