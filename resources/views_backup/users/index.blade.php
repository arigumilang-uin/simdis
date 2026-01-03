@extends('layouts.app')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/users/index.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pages/users/filters.css') }}">
@endsection

@section('content')

{{-- TAILWIND CONFIG & STYLES (Dibiarkan tetap di sini sesuai permintaan awal) --}}
<script src="https://cdn.tailwindcss.com"></script>
<script>
    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    // Kita kunci warna primary jadi BLUE (sesuai dashboard), bukan Indigo lagi
                    primary: '#3b82f6', 
                    slate: {
                        800: '#1e293b', 
                        900: '#0f172a',
                    }
                }
            }
        },
        corePlugins: {
            preflight: false, /* Wajib False */
        }
    }
</script>

<style>
    .page-container {
        font-family: 'Inter', system-ui, -apple-system, sans-serif;
        background-color: #f8fafc;
        min-height: 100vh;
        padding: 1.5rem;
    }
    
    .floating-table {
        border-collapse: separate;
        border-spacing: 0 12px;
        width: 100%;
    }
    .floating-row {
        background: white;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 2px 5px rgba(0,0,0,0.02);
        border: 1px solid #f1f5f9;
    }
    /* Round corners untuk baris tabel */
    .floating-row td:first-child { border-top-left-radius: 12px; border-bottom-left-radius: 12px; }
    .floating-row td:last-child { border-top-right-radius: 12px; border-bottom-right-radius: 12px; }
    
    /* Hover Effect Dashboard Style */
    .floating-row:hover {
        transform: translateY(-4px);
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
        border-color: #bfdbfe; /* Blue-200 saat hover */
    }
</style>

<div class="page-container">

    {{-- HEADER WITH GRADIENT BACKGROUND --}}
    <div class="relative rounded-2xl bg-gradient-to-r from-slate-800 to-blue-900 p-6 shadow-lg mb-8 overflow-hidden text-white flex flex-col md:flex-row items-center justify-between gap-4 border border-blue-800/50">
        
        <div class="absolute top-0 right-0 w-64 h-64 bg-blue-500 opacity-10 rounded-full blur-3xl -mr-20 -mt-20 pointer-events-none"></div>
        <div class="absolute bottom-0 left-0 w-40 h-40 bg-cyan-400 opacity-10 rounded-full blur-2xl -ml-10 -mb-10 pointer-events-none"></div>
        
        <div class="relative z-10">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 backdrop-blur-sm border border-white/10 text-[10px] font-medium text-blue-200 mb-2">
                <span class="w-1.5 h-1.5 rounded-full bg-blue-400 animate-pulse"></span>
                Manajemen Data
            </div>
            <h1 class="text-2xl font-bold flex items-center gap-3 m-0 leading-tight">
                Data Pengguna
            </h1>
            <p class="text-blue-100 text-sm mt-1 opacity-80">Kelola akun Guru, Staff, dan Wali Murid.</p>
        </div>

        <div class="flex gap-3 relative z-10">
            <a href="{{ route('users.create') }}" class="group relative inline-flex items-center gap-2 px-5 py-2.5 bg-white/10 text-white rounded-xl hover:bg-white/20 transition-all duration-300 border border-white/20 shadow-sm backdrop-blur-md overflow-hidden font-medium text-sm no-underline">
                <div class="absolute inset-0 w-full h-full bg-gradient-to-r from-transparent via-white/20 to-transparent -translate-x-full group-hover:animate-shimmer"></div>
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                Tambah User
            </a>
        </div>
    </div>

    {{-- FILTER SECTION - SINGLE CARD LAYER --}}
    {{-- Hapus wrapper card ganda, panggil partial yang diasumsikan sudah berisi satu card utuh --}}
    @include('components.users.filter-form')

    {{-- ALERTS --}}
    @if(session('success'))
        <div class="mb-6 p-4 rounded-xl bg-emerald-50/80 border border-emerald-100 text-emerald-700 flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-3">
                <div class="p-1.5 bg-emerald-100 rounded-full text-emerald-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                </div>
                <span class="font-medium text-sm">{{ session('success') }}</span>
            </div>
            <button type="button" class="text-emerald-400 hover:text-emerald-600" data-dismiss="alert" onclick="this.closest('div').style.display='none'">&times;</button>
        </div>
    @endif
    
    @if(session('error'))
        <div class="mb-6 p-4 rounded-xl bg-rose-50/80 border border-rose-100 text-rose-700 flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-3">
                   <div class="p-1.5 bg-rose-100 rounded-full text-rose-600">
                     <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M15 9l-6 6"/><path d="M9 9l6 6"/></svg>
                </div>
                <span class="font-medium text-sm">{{ session('error') }}</span>
            </div>
            <button type="button" class="text-rose-400 hover:text-rose-600" data-dismiss="alert" onclick="this.closest('div').style.display='none'">&times;</button>
        </div>
    @endif

    {{-- DAFTAR PENGGUNA HEADER --}}
    <div class="mb-3 px-2 flex justify-between items-end">
        <h3 class="text-base font-bold text-slate-700 m-0">Daftar Pengguna</h3>
        <p class="text-xs text-slate-500 m-0">
            Total: <strong class="text-blue-600">{{ $users->total() }}</strong> User
        </p>
    </div>

    {{-- DATA TABLE --}}
    <div class="overflow-x-auto pb-4">
        <table class="floating-table text-left">
            <thead>
                <tr class="text-xs font-bold text-slate-400 uppercase tracking-wider">
                    <th class="px-6 py-3 pb-4 pl-8">User Profile</th>
                    <th class="px-6 py-3 pb-4">Role Access</th>
                    <th class="px-6 py-3 pb-4">Kontak</th>
                    <th class="px-6 py-3 pb-4">Identitas (NIP)</th>
                    <th class="px-6 py-3 pb-4 text-center pr-8">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $key => $u)
                <tr class="floating-row group">
                    
                    <td class="px-6 py-4 pl-8">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-slate-500 flex items-center justify-center text-white font-bold text-sm shadow-sm shrink-0">
                                {{ substr($u->nama, 0, 1) }}
                            </div>
                            <div>
                                <div class="text-sm font-bold text-slate-700">{{ $u->username }}</div>
                                <div class="text-[10px] font-mono text-blue-500 mt-0.5 bg-blue-50 px-1.5 py-0.5 rounded-md inline-block border border-blue-100">
                                    {{ $u->nama }}
                                </div>
                            </div>
                        </div>
                    </td>

                    <td class="px-6 py-4">
                        @php $roleName = $u->role?->nama_role ?? 'N/A'; @endphp
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-medium bg-white border border-slate-200 text-slate-500">
                            {{ $roleName }}
                        </span>
                    </td>

                    <td class="px-6 py-4 text-sm">
                        @php
                            if ($u->isWaliMurid()) {
                                $kontak = $u->anakWali->whereNotNull('nomor_hp_wali_murid')->pluck('nomor_hp_wali_murid')->first();
                            } else {
                                $kontak = $u->phone;
                            }
                        @endphp
                        <div class="flex flex-col">
                            <span class="text-xs text-slate-500">{{ $u->email }}</span>
                            <span class="text-xs text-slate-400 mt-0.5 flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                                {{ $kontak ?? '-' }}
                            </span>
                        </div>
                    </td>

                    <td class="px-6 py-4">
                        @php $tandaPengenal = $u->nip ?? $u->nuptk ?? null; @endphp
                        @if($tandaPengenal)
                            <code class="text-[10px] font-mono bg-slate-100 px-2 py-1 rounded text-slate-600 border border-slate-200">{{ $tandaPengenal }}</code>
                        @else
                            <span class="text-slate-300 text-xs">-</span>
                        @endif
                    </td>

                    <td class="px-6 py-4 text-center pr-8">
                        <div class="inline-flex items-center justify-center gap-2 bg-slate-50 border border-slate-200 p-1 rounded-lg opacity-0 group-hover:opacity-100 transition-all duration-200">
                            <a href="{{ route('users.edit', $u->id) }}" class="p-1.5 text-slate-400 hover:text-blue-600 hover:bg-white rounded-md transition" title="Edit">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/></svg>
                            </a>

                            @if(Auth::id() != $u->id)
                                <form onsubmit="return confirm('Yakin ingin menghapus user {{ $u->nama }}?');" action="{{ route('users.destroy', $u->id) }}" method="POST" class="inline m-0">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-500 hover:bg-white rounded-md transition border-0 bg-transparent cursor-pointer" title="Hapus">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                                    </button>
                                </form>
                            @else
                                <button class="p-1.5 text-slate-200 cursor-not-allowed" disabled>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                                </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center py-12 text-slate-400">
                        <div class="flex flex-col items-center justify-center">
                            <div class="bg-slate-100 p-3 rounded-full mb-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-slate-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" x2="9" y1="9" y2="15"/><line x1="9" x2="15" y1="9" y2="15"/></svg>
                            </div>
                            <p class="text-sm">Data tidak ditemukan.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- PAGINATION --}}
    <div class="mt-6 flex flex-col md:flex-row justify-between items-center gap-4 bg-white p-4 rounded-2xl shadow-sm border border-slate-100">
        
        <div class="text-sm text-slate-500 text-center md:text-left">
            Menampilkan 
            <span class="font-bold text-slate-800">{{ $users->firstItem() ?? 0 }}</span> 
            sampai 
            <span class="font-bold text-slate-800">{{ $users->lastItem() ?? 0 }}</span> 
            dari total 
            <span class="font-bold text-blue-600 bg-blue-50 px-2 py-0.5 rounded-md border border-blue-100">{{ $users->total() }}</span> 
            data
        </div>

        <div class="flex items-center gap-2">
            
            @if ($users->onFirstPage())
                <span class="px-4 py-2 text-sm text-slate-300 bg-slate-50 border border-slate-100 rounded-xl cursor-not-allowed flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                    Prev
                </span>
            @else
                <a href="{{ $users->previousPageUrl() }}" class="px-4 py-2 text-sm text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-blue-50 hover:text-blue-600 hover:border-blue-200 transition-all flex items-center gap-2 shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                    Prev
                </a>
            @endif

            <div class="hidden sm:flex items-center px-2 text-xs font-bold text-slate-400 gap-1">
                <span>Page</span>
                <span class="w-6 h-6 flex items-center justify-center bg-blue-600 text-white rounded-full shadow-md shadow-blue-200">
                    {{ $users->currentPage() }}
                </span>
            </div>

            @if ($users->hasMorePages())
                <a href="{{ $users->nextPageUrl() }}" class="px-4 py-2 text-sm text-white bg-blue-600 border border-blue-600 rounded-xl hover:bg-blue-700 hover:shadow-lg hover:shadow-blue-200 transition-all flex items-center gap-2">
                    Next
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                </a>
            @else
                <span class="px-4 py-2 text-sm text-slate-300 bg-slate-50 border border-slate-100 rounded-xl cursor-not-allowed flex items-center gap-2">
                    Next
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                </span>
            @endif
        </div>
    </div>

</div>
@endsection

@push('scripts')
    <script src="{{ asset('js/pages/users/filters.js') }}"></script>
    <script src="{{ asset('js/pages/users/index.js') }}"></script>
@endpush