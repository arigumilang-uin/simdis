@extends('layouts.app')

@section('title', 'Riwayat Saya')
@section('subtitle', 'Pelanggaran yang dicatat oleh Anda.')
@section('page-header', true)

@section('actions')
    <a href="{{ route('riwayat.create') }}" class="btn btn-primary">
        <x-ui.icon name="plus" size="18" />
        <span>Catat Pelanggaran</span>
    </a>
@endsection

@section('content')
<div class="space-y-6">
    {{-- Data Table --}}
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
    
    {{-- Pagination --}}
    @if($riwayat->hasPages())
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
            <p class="text-sm text-gray-500">
                Menampilkan {{ $riwayat->firstItem() }} - {{ $riwayat->lastItem() }} dari {{ $riwayat->total() }}
            </p>
            {{ $riwayat->links() }}
        </div>
    @endif
</div>
@endsection
