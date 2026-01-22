@extends('layouts.app')

@section('title', 'Periode Semester')

@section('page-header')
    <x-page-header 
        title="Periode Semester" 
        subtitle="Kelola tanggal mulai dan selesai semester tahun ajaran."
    >
        <x-slot:actions>
            <a href="{{ route('admin.periode-semester.create') }}" class="btn btn-primary">
                <x-ui.icon name="plus" size="18" />
                <span>Tambah Periode</span>
            </a>
        </x-slot:actions>
    </x-page-header>
@endsection

@section('content')
<div class="space-y-4">
    <div class="bg-white md:border md:border-gray-200 md:rounded-xl md:shadow-sm overflow-hidden mb-8 border-b border-gray-200 md:border-b-0">
        {{-- Toolbar --}}
        <div class="px-4 md:px-6 py-5 border-b border-gray-100 bg-white">
            <x-ui.action-bar :total="$periodes->count()" totalLabel="Periode" class="!gap-4" />
        </div>

        {{-- Table Container --}}
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Nama Periode</th>
                        <th class="text-center w-32">Semester</th>
                        <th class="text-center">Tanggal</th>
                        <th class="text-center w-28">Status</th>
                        <th class="text-center w-48">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($periodes as $p)
                        <tr class="{{ $p->is_active ? 'bg-emerald-50/50' : '' }}">
                            <td>
                                <div class="font-bold text-slate-800">{{ $p->nama_periode }}</div>
                                <div class="text-xs text-slate-500 mt-0.5">T.A. {{ $p->tahun_ajaran }}</div>
                            </td>
                            <td class="text-center">
                                @if($p->semester->value === 'Ganjil')
                                    <span class="badge badge-indigo">Ganjil</span>
                                @else
                                    <span class="badge badge-amber">Genap</span>
                                @endif
                            </td>
                            <td class="text-center text-sm text-slate-600">
                                <div class="flex flex-col text-xs">
                                    <span>{{ $p->tanggal_mulai->format('d M Y') }}</span>
                                    <span class="text-slate-300 text-[10px] my-0.5">s/d</span>
                                    <span>{{ $p->tanggal_selesai->format('d M Y') }}</span>
                                </div>
                            </td>
                            <td class="text-center">
                                @if($p->is_active)
                                    <span class="badge badge-success">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse mr-1"></span>
                                        Aktif
                                    </span>
                                @else
                                    <span class="badge badge-secondary">Nonaktif</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="flex justify-center items-center gap-1">
                                    @if(!$p->is_active)
                                        <form action="{{ route('admin.periode-semester.setActive', $p->id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-icon btn-white text-emerald-600 hover:bg-emerald-50" title="Set Aktif">
                                                <x-ui.icon name="power" size="16" />
                                            </button>
                                        </form>
                                    @endif
                                    <a href="{{ route('admin.periode-semester.tingkatKurikulum', $p->id) }}" class="btn btn-sm btn-icon btn-white text-violet-600 hover:bg-violet-50" title="Konfigurasi Kurikulum">
                                        <x-ui.icon name="layers" size="16" />
                                    </a>
                                    <form action="{{ route('admin.periode-semester.generatePertemuan', $p->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-icon btn-white text-indigo-600 hover:bg-indigo-50" title="Generate Pertemuan">
                                            <x-ui.icon name="refresh-cw" size="16" />
                                        </button>
                                    </form>
                                    <a href="{{ route('admin.periode-semester.edit', $p->id) }}" class="btn btn-sm btn-icon btn-white text-amber-600 hover:bg-amber-50" title="Edit">
                                        <x-ui.icon name="edit" size="16" />
                                    </a>
                                    <form action="{{ route('admin.periode-semester.destroy', $p->id) }}" method="POST" class="inline" onsubmit="return confirm('Yakin hapus?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-icon btn-white text-rose-600 hover:bg-rose-50" title="Hapus">
                                            <x-ui.icon name="trash" size="16" />
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <x-ui.empty-state title="Belum ada periode semester" description="Silakan tambahkan periode semester baru." icon="calendar">
                                    <x-slot:action>
                                        <a href="{{ route('admin.periode-semester.create') }}" class="btn btn-primary">
                                            <x-ui.icon name="plus" size="18" />
                                            <span>Tambah Periode</span>
                                        </a>
                                    </x-slot:action>
                                </x-ui.empty-state>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
