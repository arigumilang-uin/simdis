@extends('layouts.app')

@php
    $tab = $tab ?? 'activity';
    $titles = [
        'activity' => [
            'title' => 'Audit & Log Sistem',
            'subtitle' => 'Pantau integritas data dan riwayat aktivitas pengguna sistem.'
        ],
        'last-login' => [
            'title' => 'Log Login Terakhir',
            'subtitle' => 'Pantau kapan terakhir kali pengguna login ke sistem.'
        ],
        'status' => [
            'title' => 'Status Akun Pengguna',
            'subtitle' => 'Kelola status aktif/non-aktif akun pengguna.'
        ]
    ];
    $current = $titles[$tab] ?? $titles['activity'];
@endphp

@section('title', $current['title'])

@section('page-header')
    <x-page-header 
        :title="$current['title']" 
        :subtitle="$current['subtitle']"
    />
@endsection

@section('content')
<div class="space-y-6">
    {{-- Action Button --}}
    @if($tab === 'activity')
    <div class="flex justify-end">
        <a href="{{ route('audit.activity.index', array_merge(request()->query(), ['export' => 'csv'])) }}" class="btn btn-success">
            <x-ui.icon name="download" size="18" />
            <span>Export CSV</span>
        </a>
    </div>
    @endif

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
