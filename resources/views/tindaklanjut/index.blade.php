@extends('layouts.app')

@section('title', 'Daftar Kasus')

@section('page-header')
    <x-page-header 
        title="Daftar Kasus" 
        subtitle="Kelola tindak lanjut pelanggaran siswa."
        :total="$tindakLanjut->total()"
    />
@endsection

@section('content')
<div class="space-y-6">
    {{-- Filter Card --}}
    <div class="card" x-data="{ expanded: false }">
        <div class="card-header cursor-pointer" @click="expanded = !expanded">
            <div class="flex items-center gap-2">
                <x-ui.icon name="filter" class="text-gray-400" size="18" />
                <span class="card-title">Filter Data</span>
            </div>
            <x-ui.icon name="chevron-down" size="20" class="text-gray-400 transition-transform" ::class="{ 'rotate-180': expanded }" />
        </div>
        
        <div x-show="expanded" x-collapse x-cloak>
            <div class="card-body border-t border-gray-100">
                <form method="GET" action="{{ route('tindak-lanjut.index') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="form-group">
                        <x-forms.select name="status" label="Status">
                            <option value="">Semua Status</option>
                            <option value="Baru" {{ request('status') == 'Baru' ? 'selected' : '' }}>Baru</option>
                            <option value="Menunggu Persetujuan" {{ request('status') == 'Menunggu Persetujuan' ? 'selected' : '' }}>Menunggu Persetujuan</option>
                            <option value="Disetujui" {{ request('status') == 'Disetujui' ? 'selected' : '' }}>Disetujui</option>
                            <option value="Ditangani" {{ request('status') == 'Ditangani' ? 'selected' : '' }}>Ditangani</option>
                            <option value="Selesai" {{ request('status') == 'Selesai' ? 'selected' : '' }}>Selesai</option>
                            <option value="Ditolak" {{ request('status') == 'Ditolak' ? 'selected' : '' }}>Ditolak</option>
                        </x-forms.select>
                    </div>
                    
                    <div class="form-group flex items-end gap-2">
                        <button type="submit" class="btn btn-primary">
                            <x-ui.icon name="search" size="16" />
                            <span>Filter</span>
                        </button>
                        <a href="{{ route('tindak-lanjut.index') }}" class="btn btn-secondary">Reset</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th class="w-12">No</th>
                    <th>Siswa</th>
                    <th>Kelas</th>
                    <th>Pemicu</th>
                    <th class="text-center">Status</th>
                    <th>Tanggal</th>
                    <th class="w-28 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tindakLanjut as $index => $kasus)
                    <tr>
                        <td class="text-gray-500">{{ $tindakLanjut->firstItem() + $index }}</td>
                        <td>
                            <div class="font-medium text-gray-800">{{ $kasus->siswa->nama_siswa ?? '-' }}</div>
                            <div class="text-xs text-gray-500">{{ $kasus->siswa->nisn ?? '-' }}</div>
                        </td>
                        <td class="text-gray-500">{{ $kasus->siswa->kelas->nama_kelas ?? '-' }}</td>
                        <td>
                            <div class="max-w-xs truncate text-gray-700" title="{{ $kasus->pemicu }}">
                                {{ Str::limit($kasus->pemicu, 50) }}
                            </div>
                        </td>
                        <td class="text-center">
                            @php
                                $statusColor = match($kasus->status->value ?? $kasus->status) {
                                    'Baru' => 'badge-secondary',
                                    'Menunggu Persetujuan' => 'badge-warning',
                                    'Disetujui' => 'badge-info',
                                    'Ditangani' => 'badge-primary',
                                    'Selesai' => 'badge-success',
                                    'Ditolak' => 'badge-danger',
                                    default => 'badge-secondary'
                                };
                            @endphp
                            <span class="badge {{ $statusColor }}">{{ $kasus->status->value ?? $kasus->status }}</span>
                        </td>
                        <td class="text-gray-500">{{ $kasus->tanggal_tindak_lanjut?->format('d M Y') ?? '-' }}</td>
                        <td>
                            {{-- Desktop: Icon buttons --}}
                            <div class="action-buttons-desktop">
                                <a href="{{ route('tindak-lanjut.show', $kasus->id) }}" class="btn btn-icon btn-outline" title="Detail">
                                    <x-ui.icon name="eye" size="16" />
                                </a>
                                @if($kasus->status->value != 'Selesai' && $kasus->status->value != 'Ditolak')
                                <a href="{{ route('tindak-lanjut.edit', $kasus->id) }}" class="btn btn-icon btn-outline" title="Edit">
                                    <x-ui.icon name="edit" size="16" />
                                </a>
                                @endif
                            </div>
                            
                            {{-- Mobile: Dropdown --}}
                            <div class="action-dropdown-mobile" x-data="{ open: false }">
                                <button @click="open = !open" @click.away="open = false" class="action-dropdown-trigger">
                                    <x-ui.icon name="more-horizontal" size="18" />
                                </button>
                                <div x-show="open" x-transition class="action-dropdown-menu">
                                    <a href="{{ route('tindak-lanjut.show', $kasus->id) }}" class="action-dropdown-item">
                                        <x-ui.icon name="eye" size="16" />
                                        Detail
                                    </a>
                                    @if($kasus->status->value != 'Selesai' && $kasus->status->value != 'Ditolak')
                                    <a href="{{ route('tindak-lanjut.edit', $kasus->id) }}" class="action-dropdown-item action-dropdown-item--edit">
                                        <x-ui.icon name="edit" size="16" />
                                        Edit
                                    </a>
                                    @endif
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">
                            <x-ui.empty-state 
                                icon="clipboard" 
                                title="Belum Ada Kasus" 
                                description="Belum ada tindak lanjut yang tercatat." 
                            />
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($tindakLanjut->hasPages())
        <div class="mt-4">
            {{ $tindakLanjut->links() }}
        </div>
    @endif
</div>
@endsection
