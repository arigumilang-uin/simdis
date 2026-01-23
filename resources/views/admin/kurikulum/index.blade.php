@extends('layouts.app')

@section('title', 'Kurikulum')

@section('page-header')
    <x-page-header 
        title="Kurikulum" 
        subtitle="Kelola data master kurikulum yang digunakan di sekolah"
    >
        <x-slot:actions>
            <a href="{{ route('admin.kurikulum.create') }}" class="btn btn-primary">
                <x-ui.icon name="plus" size="16" />
                <span>Tambah Kurikulum</span>
            </a>
        </x-slot:actions>
    </x-page-header>
@endsection

@section('content')
<div class="space-y-4" x-data="{ selectionMode: false, selected: [] }">

    <div class="bg-white md:border md:border-gray-200 md:rounded-xl md:shadow-sm overflow-hidden mb-8 border-b border-gray-200 md:border-b-0">
        {{-- Toolbar --}}
        <div class="px-4 md:px-6 py-5 border-b border-gray-100 bg-white">
            <x-ui.action-bar :total="$kurikulums->count()" totalLabel="Kurikulum" class="!gap-4" />
        </div>

        {{-- Table --}}
        @if($kurikulums->count() > 0)
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th class="w-20">Kode</th>
                            <th>Nama Kurikulum</th>
                            <th class="w-24">Tahun</th>
                            <th class="w-28 text-center">Mapel</th>
                            <th class="w-24 text-center">Status</th>
                            <x-table.action-header />
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($kurikulums as $kurikulum)
                            <tr>
                                <td>
                                    <span class="font-mono text-sm font-medium text-slate-700">{{ $kurikulum->kode }}</span>
                                </td>
                                <td>
                                    <div class="font-medium text-slate-900">{{ $kurikulum->nama }}</div>
                                    @if($kurikulum->deskripsi)
                                        <div class="text-sm text-slate-500 truncate max-w-md">{{ $kurikulum->deskripsi }}</div>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($kurikulum->tahun_berlaku)
                                        <span class="badge badge-slate">{{ $kurikulum->tahun_berlaku }}</span>
                                    @else
                                        <span class="text-slate-400">-</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-indigo">{{ $kurikulum->mata_pelajaran_count }} mapel</span>
                                </td>
                                <td class="text-center">
                                    @if($kurikulum->is_active)
                                        <span class="badge badge-success">Aktif</span>
                                    @else
                                        <span class="badge badge-secondary">Nonaktif</span>
                                    @endif
                                </td>
                                <x-table.action-column :id="$kurikulum->id">
                                    <x-table.action-item icon="edit" :href="route('admin.kurikulum.edit', $kurikulum->id)">
                                        Edit
                                    </x-table.action-item>
                                    <x-table.action-separator />
                                    <form action="{{ route('admin.kurikulum.destroy', $kurikulum->id) }}" method="POST" onsubmit="return confirm('Arsipkan kurikulum ini beserta {{ $kurikulum->mata_pelajaran_count }} mata pelajaran?')">
                                        @csrf
                                        @method('DELETE')
                                        <x-table.action-item icon="archive" type="submit" class="text-amber-600 hover:text-amber-700 hover:bg-amber-50">
                                            Arsipkan
                                        </x-table.action-item>
                                    </form>
                                </x-table.action-column>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <x-ui.empty-state
                icon="layers"
                title="Belum Ada Kurikulum"
                description="Tambahkan kurikulum untuk memulai."
                :actionUrl="route('admin.kurikulum.create')"
                actionLabel="Tambah Kurikulum"
            />
        @endif
    </div>
</div>
@endsection
