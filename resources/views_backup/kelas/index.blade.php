@extends('layouts.app')

@section('content')

<script src="https://cdn.tailwindcss.com"></script>
<script>
    tailwind.config = {
        theme: {
            extend: { colors: { primary: '#3b82f6', slate: { 800: '#1e293b', 900: '#0f172a' } } }
        },
        corePlugins: { preflight: false }
    }
</script>

<div class="page-wrap">

    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 m-0 tracking-tight">Kelola Kelas</h1>
            <p class="text-slate-500 text-sm mt-1">Manajemen data rombongan belajar sekolah.</p>
        </div>

        @if(auth()->user()->hasRole('Operator Sekolah'))
            <a href="{{ route('kelas.create') }}" class="btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                Tambah Kelas
            </a>
        @endif
    </div>

    @if(session('success'))
        <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center gap-3 shadow-sm">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
            <span class="font-medium text-sm">{{ session('success') }}</span>
        </div>
    @endif

    @if(session('wali_created'))
        @php $w = session('wali_created'); @endphp
        <div class="mb-8 bg-white border border-indigo-100 rounded-2xl p-5 shadow-sm relative overflow-hidden group hover:border-indigo-300 transition-colors">
            <div class="absolute top-0 right-0 bg-indigo-50 w-24 h-24 rounded-bl-full -mr-4 -mt-4 opacity-50"></div>
            
            <h4 class="text-indigo-700 font-bold m-0 flex items-center gap-2 mb-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                Akun Wali Kelas Baru
            </h4>
            
            <div class="flex flex-col sm:flex-row gap-4">
                <div class="bg-indigo-50/50 p-3 rounded-lg border border-indigo-100 flex-1">
                    <span class="text-xs text-indigo-400 uppercase font-bold tracking-wider">Username</span>
                    <div class="font-mono text-indigo-900 font-bold text-lg">{{ $w['username'] }}</div>
                </div>
                <div class="bg-indigo-50/50 p-3 rounded-lg border border-indigo-100 flex-1">
                    <span class="text-xs text-indigo-400 uppercase font-bold tracking-wider">Password</span>
                    <div class="font-mono text-rose-600 font-bold text-lg">{{ $w['password'] }}</div>
                </div>
            </div>
            <p class="text-xs text-slate-400 mt-3 italic">* Harap simpan kredensial ini.</p>
        </div>
    @endif

    <div class="flex justify-between items-end px-2 mb-3">
        <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">Daftar Rombel</span>
        <span class="text-xs text-slate-500 bg-white px-3 py-1 rounded-full border border-slate-200 shadow-sm">
            Total: <b class="text-blue-600">{{ method_exists($kelasList, 'total') ? $kelasList->total() : $kelasList->count() }}</b>
        </span>
    </div>

    <div class="overflow-x-auto pb-6">
        <table class="float-table text-left">
            <thead>
                <tr class="text-xs font-bold text-slate-400 uppercase tracking-wider">
                    <th class="px-6 py-3 pl-8">Identitas Kelas</th>
                    <th class="px-6 py-3">Jurusan</th>
                    <th class="px-6 py-3">Wali Kelas</th>
                    <th class="px-6 py-3 text-center">Siswa</th>
                    <th class="px-6 py-3 text-center pr-8">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($kelasList as $k)
                <tr class="float-row group">
                    
                    <td class="px-6 py-4 pl-8">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-amber-100 to-orange-100 text-orange-600 flex items-center justify-center font-bold text-sm shadow-sm border border-orange-100">
                                {{ substr($k->nama_kelas, 0, 1) }}
                            </div>
                            <div>
                                <div class="text-sm font-bold text-slate-700">{{ $k->nama_kelas }}</div>
                                <div class="text-[10px] text-slate-400 font-mono mt-0.5">ID: {{ $k->id }}</div>
                            </div>
                        </div>
                    </td>

                    <td class="px-6 py-4">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-200">
                            {{ $k->jurusan->nama_jurusan ?? '-' }}
                        </span>
                    </td>

                    <td class="px-6 py-4">
                        @if($k->waliKelas)
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center text-[10px] font-bold border border-blue-100">
                                    {{ substr($k->waliKelas->username, 0, 1) }}
                                </div>
                                <span class="text-sm font-medium text-slate-600">{{ $k->waliKelas->username }}</span>
                            </div>
                        @else
                            <span class="text-xs text-slate-300 italic flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="4.93" x2="19.07" y1="4.93" y2="19.07"/></svg>
                                Kosong
                            </span>
                        @endif
                    </td>

                    <td class="px-6 py-4 text-center">
                        <span class="text-xs font-bold text-blue-600 bg-blue-50 px-2 py-1 rounded-md border border-blue-100">
                            {{ $k->siswa()->count() }}
                        </span>
                    </td>

                    <td class="px-6 py-4 text-center pr-8">
                        <div class="flex justify-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                            
                            <a href="{{ route('kelas.show', $k) }}" class="btn-action hover:text-blue-600 hover:border-blue-100" title="Detail">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                            </a>

                            @if(auth()->user()->hasRole('Operator Sekolah'))
                                <a href="{{ route('kelas.edit', $k) }}" class="btn-action hover:text-amber-500 hover:border-amber-100" title="Edit">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/></svg>
                                </a>

                                <form action="{{ route('kelas.destroy', $k) }}" method="POST" class="m-0 inline" onsubmit="return confirm('Hapus kelas ini?');">
                                    @csrf @method('DELETE')
                                    <button class="btn-action hover:text-rose-500 hover:border-rose-100 cursor-pointer" title="Hapus">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center py-12 text-slate-400">
                        <div class="flex flex-col items-center opacity-60">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 text-slate-300 mb-2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 22h14a2 2 0 0 0 2-2V7.5L12 2 4 7.5V20a2 2 0 0 0 2 2z"/><path d="M12 18v-7"/><path d="M9 13v2"/><path d="M15 13v2"/></svg>
                            <p class="text-sm">Belum ada data kelas.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-2 flex justify-center">
        @if(method_exists($kelasList, 'links'))
            <div class="bg-white px-3 py-2 rounded-2xl shadow-sm border border-slate-100">
                {{ $kelasList->links('pagination.custom') }}
            </div>
        @endif
    </div>

</div>

<style>
    .page-wrap { background: #f8fafc; min-height: 100vh; padding: 1.5rem; font-family: 'Inter', sans-serif; }
    
    /* Tabel Clean Floating */
    .float-table { border-collapse: separate; border-spacing: 0 10px; width: 100%; }
    .float-row { background: white; transition: 0.2s cubic-bezier(0.4, 0, 0.2, 1); border: 1px solid #f1f5f9; box-shadow: 0 2px 4px rgba(0,0,0,0.02); }
    .float-row td:first-child { border-radius: 10px 0 0 10px; border-left: 1px solid #f1f5f9; }
    .float-row td:last-child { border-radius: 0 10px 10px 0; border-right: 1px solid #f1f5f9; }
    .float-row:hover { transform: translateY(-3px); border-color: #bfdbfe; box-shadow: 0 10px 20px -5px rgba(59, 130, 246, 0.1); z-index: 10; position: relative; }
    
    /* Tombol Primary (Solid Blue) */
    .btn-primary { background-color: #2563eb; color: white; padding: 0.6rem 1.2rem; border-radius: 0.75rem; font-weight: 600; font-size: 0.875rem; text-decoration: none; display: inline-flex; align-items: center; gap: 0.5rem; transition: 0.2s; border: 1px solid #1d4ed8; box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.2); }
    .btn-primary:hover { background-color: #1d4ed8; transform: translateY(-1px); box-shadow: 0 6px 8px -1px rgba(37, 99, 235, 0.3); }
    
    /* Tombol Aksi Kecil */
    .btn-action { padding: 6px; border-radius: 8px; transition: 0.2s; color: #64748b; border: 1px solid transparent; background: transparent; }
    .btn-action:hover { background: white; border-color: #e2e8f0; color: #3b82f6; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
</style>
@endsection