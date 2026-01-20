@extends('layouts.app')

@section('title', 'Riwayat Saya')

@section('page-header')
    <x-page-header 
        title="Riwayat Saya" 
        subtitle="Pelanggaran yang dicatat oleh Anda."
    >
        <x-slot:actions>
            <a href="{{ route('riwayat.create') }}" class="btn btn-primary">
                <x-ui.icon name="plus" size="18" />
                <span>Catat Pelanggaran</span>
            </a>
        </x-slot:actions>
    </x-page-header>
@endsection

@section('content')
<div class="space-y-4">
    
    <div class="bg-white md:border md:border-gray-200 md:rounded-xl md:shadow-sm overflow-hidden mb-8 border-b border-gray-200 md:border-b-0">
        {{-- Toolbar --}}
        <div class="px-4 md:px-6 py-5 border-b border-gray-100 bg-white">
            <x-ui.action-bar :total="$riwayat->total()" totalLabel="Log" class="!gap-4">
                <x-slot:search>
                    <form method="GET" action="{{ route('my-riwayat.index') }}">
                        <input 
                            type="text" 
                            name="search"
                            value="{{ request('search') }}"
                            class="w-full md:w-80 rounded-xl border-0 bg-gray-100/80 text-sm text-gray-800 py-2.5 pl-10 pr-4 hover:bg-gray-100 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:shadow-lg focus:shadow-indigo-500/5 transition-all duration-200 placeholder-gray-400"
                            placeholder="Cari siswa atau pelanggaran..."
                            onkeydown="if(event.key === 'Enter') this.form.submit()"
                        >
                    </form>
                </x-slot:search>
                @if(request('search'))
                <x-slot:reset>
                    <a href="{{ route('my-riwayat.index') }}" class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">Reset</a>
                </x-slot:reset>
                @endif
            </x-ui.action-bar>
        </div>

        {{-- Table --}}
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Siswa</th>
                        <th>Kelas</th>
                        <th>Pelanggaran</th>
                        <th class="text-center">Poin</th>
                        <th class="w-32 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($riwayat as $r)
                        <tr>
                            <td class="text-gray-500 whitespace-nowrap">{{ \Carbon\Carbon::parse($r->tanggal_kejadian)->format('d M Y') }}</td>
                            <td>
                                <a href="{{ route('siswa.show', $r->siswa->id ?? 0) }}" class="font-medium text-gray-800 hover:text-blue-600">
                                    {{ $r->siswa->nama_siswa ?? '-' }}
                                </a>
                            </td>
                            <td>
                                <span class="badge badge-primary">{{ $r->siswa->kelas->nama_kelas ?? '-' }}</span>
                            </td>
                            <td class="max-w-xs">
                                <p class="font-medium text-gray-800">{{ $r->jenisPelanggaran->nama_pelanggaran ?? '-' }}</p>
                                @if($r->keterangan)
                                    <p class="text-sm text-gray-500 truncate">{{ $r->keterangan }}</p>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge badge-danger">{{ $r->poin ?? 0 }}</span>
                            </td>
                            <td>
                                <div class="flex items-center justify-center gap-1">
                                    <a href="{{ route('my-riwayat.edit', $r->id) }}" class="btn btn-icon btn-outline" title="Edit">
                                        <x-ui.icon name="edit" size="16" />
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <x-ui.empty-state 
                                    icon="clipboard" 
                                    title="Belum Ada Catatan" 
                                    description="Anda belum mencatat pelanggaran apapun." 
                                >
                                    <x-slot:action>
                                        <a href="{{ route('riwayat.create') }}" class="btn btn-primary">Catat Pelanggaran</a>
                                    </x-slot:action>
                                </x-ui.empty-state>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        {{-- Pagination Footer --}}
        @if($riwayat->hasPages())
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50 flex flex-col items-center justify-between gap-4 md:flex-row">
            <p class="text-sm text-gray-500 text-center md:text-left">
                Menampilkan <span class="font-semibold text-gray-900">{{ $riwayat->firstItem() }}</span> 
                sampai <span class="font-semibold text-gray-900">{{ $riwayat->lastItem() }}</span> 
                dari <span class="font-semibold text-gray-900">{{ $riwayat->total() }}</span> data
            </p>
            <div class="flex items-center gap-2">
                @if($riwayat->onFirstPage())
                    <button type="button" class="btn btn-sm btn-secondary text-gray-400 cursor-not-allowed bg-white/50" disabled>
                        <x-ui.icon name="chevron-left" size="16" />
                        <span>Sebelumnya</span>
                    </button>
                @else
                    <a href="{{ $riwayat->previousPageUrl() }}" class="btn btn-sm btn-secondary hover:text-indigo-600 hover:border-indigo-200 bg-white">
                        <x-ui.icon name="chevron-left" size="16" />
                        <span>Sebelumnya</span>
                    </a>
                @endif
                @if($riwayat->hasMorePages())
                    <a href="{{ $riwayat->nextPageUrl() }}" class="btn btn-sm btn-secondary hover:text-indigo-600 hover:border-indigo-200 bg-white">
                        <span>Selanjutnya</span>
                        <x-ui.icon name="chevron-right" size="16" />
                    </a>
                @else
                    <button type="button" class="btn btn-sm btn-secondary text-gray-400 cursor-not-allowed bg-white/50" disabled>
                        <span>Selanjutnya</span>
                        <x-ui.icon name="chevron-right" size="16" />
                    </button>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
