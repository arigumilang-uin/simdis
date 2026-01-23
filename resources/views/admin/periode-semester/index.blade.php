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
<div class="space-y-4" x-data="{ selectionMode: false, selected: [] }">
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
                        <x-table.action-header />
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
                            <x-table.action-column :id="$p->id">
                                @if(!$p->is_active)
                                    <form action="{{ route('admin.periode-semester.setActive', $p->id) }}" method="POST">
                                        @csrf
                                        <x-table.action-item icon="power" type="submit" class="text-emerald-600 hover:text-emerald-700 hover:bg-emerald-50">
                                            Set Aktif
                                        </x-table.action-item>
                                    </form>
                                    <x-table.action-separator />
                                @endif
                                <x-table.action-item icon="layers" :href="route('admin.periode-semester.tingkatKurikulum', $p->id)" class="text-violet-600 hover:text-violet-700 hover:bg-violet-50">
                                    Konfigurasi Kurikulum
                                </x-table.action-item>
                                <form action="{{ route('admin.periode-semester.generatePertemuan', $p->id) }}" method="POST">
                                    @csrf
                                    <x-table.action-item icon="refresh-cw" type="submit" class="text-indigo-600 hover:text-indigo-700 hover:bg-indigo-50">
                                        Generate Pertemuan
                                    </x-table.action-item>
                                </form>
                                <x-table.action-separator />
                                <x-table.action-item icon="edit" :href="route('admin.periode-semester.edit', $p->id)">
                                    Edit
                                </x-table.action-item>
                                <x-table.action-separator />
                                <form action="{{ route('admin.periode-semester.destroy', $p->id) }}" method="POST" onsubmit="return confirm('Yakin hapus periode ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <x-table.action-item icon="trash" type="submit" class="text-red-600 hover:text-red-700 hover:bg-red-50">
                                        Hapus
                                    </x-table.action-item>
                                </form>
                            </x-table.action-column>
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
