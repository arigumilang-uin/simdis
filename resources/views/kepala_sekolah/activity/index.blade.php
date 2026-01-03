@extends('layouts.app')

@section('title', 'Audit & Log Sistem')
@section('subtitle', 'Pantau integritas data dan riwayat aktivitas pengguna sistem.')
@section('page-header', true)

@section('content')
<div class="space-y-6">
    {{-- Action Button --}}
    @if(!isset($tab) || $tab === 'activity')
    <div class="flex justify-end">
        <a href="{{ route('audit.activity.index', array_merge(request()->query(), ['export' => 'csv'])) }}" class="btn btn-success">
            <x-ui.icon name="download" size="18" />
            <span>Export CSV</span>
        </a>
    </div>
    @endif
    {{-- Tabs --}}
    <div class="card">
        <div class="p-4 flex flex-wrap gap-2">
            <a href="{{ route('audit.activity.index', ['tab' => 'activity']) }}" 
               class="px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-wide transition-all {{ (!isset($tab) || $tab === 'activity') ? 'bg-indigo-600 text-white shadow-md' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-100' }}">
                <x-ui.icon name="clock" size="14" class="inline mr-1" />
                Log Aktivitas
            </a>
            
            <a href="{{ route('audit.activity.index', ['tab' => 'last-login']) }}" 
               class="px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-wide transition-all {{ (isset($tab) && $tab === 'last-login') ? 'bg-indigo-600 text-white shadow-md' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-100' }}">
                <x-ui.icon name="log-in" size="14" class="inline mr-1" />
                Last Login
            </a>
            
            <a href="{{ route('audit.activity.index', ['tab' => 'status']) }}" 
               class="px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-wide transition-all {{ (isset($tab) && $tab === 'status') ? 'bg-indigo-600 text-white shadow-md' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-100' }}">
                <x-ui.icon name="user-check" size="14" class="inline mr-1" />
                Status Akun
            </a>
        </div>
    </div>

    {{-- Tab Content --}}
    @if(!isset($tab) || $tab === 'activity')
        @include('kepala_sekolah.activity.tabs.activity')
    @elseif($tab === 'last-login')
        @include('kepala_sekolah.activity.tabs.last-login')
    @elseif($tab === 'status')
        @include('kepala_sekolah.activity.tabs.status')
    @endif

    {{-- Info Box --}}
    <div class="p-4 bg-indigo-50 rounded-xl border border-indigo-100">
        <div class="flex items-start gap-3">
            <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 shrink-0">
                <x-ui.icon name="shield" size="16" />
            </div>
            <p class="text-xs text-indigo-700/80">
                Log sistem mencatat setiap perubahan data penting untuk keamanan. Gunakan data ini untuk meninjau validasi aktivitas atau mendeteksi akses yang tidak sah secara berkala.
            </p>
        </div>
    </div>
</div>
@endsection
