@extends('layouts.app')

@section('title', 'Dashboard Wali Murid')
@section('page-header', true)

@section('content')
<div class="flex items-center justify-center min-h-[400px]">
    <div class="text-center max-w-md">
        <div class="w-24 h-24 mx-auto bg-blue-100 rounded-full flex items-center justify-center mb-6">
            <x-ui.icon name="users" size="48" class="text-blue-600" />
        </div>
        
        <h2 class="text-2xl font-bold text-gray-800 mb-3">Data Anak Belum Terhubung</h2>
        <p class="text-gray-500 mb-6">
            Akun Anda belum terhubung dengan data siswa manapun. 
            Silakan hubungi Operator Sekolah untuk menghubungkan akun Anda dengan data anak Anda.
        </p>
        
        <a href="{{ route('account.edit') }}" class="btn btn-primary">
            <x-ui.icon name="user" size="18" />
            <span>Lihat Profil Saya</span>
        </a>
    </div>
</div>
@endsection
