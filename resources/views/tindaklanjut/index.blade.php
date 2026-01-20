@extends('layouts.app')

@section('title', 'Daftar Kasus')

@section('page-header')
    <x-page-header 
        title="Daftar Kasus" 
        subtitle="Kelola tindak lanjut pelanggaran siswa."
    />
@endsection

@section('content')
<div class="space-y-6">
    
    <div class="bg-white md:border md:border-gray-200 md:rounded-xl md:shadow-sm overflow-hidden mb-8 border-b border-gray-200 md:border-b-0">
        {{-- Toolbar with action-bar --}}
        <div class="px-4 md:px-6 py-5 border-b border-gray-100 bg-white">
            <x-ui.action-bar :total="$tindakLanjut->total()" totalLabel="Kasus" class="!gap-4">
                <x-slot:filters>
                    <div class="space-y-3">
                        <label class="text-xs font-semibold text-gray-500 uppercase">Status</label>
                        <select name="status" class="form-select w-full" onchange="window.location.href='{{ route('tindak-lanjut.index') }}?status=' + this.value">
                            <option value="">Semua Status</option>
                            <option value="Baru" {{ request('status') == 'Baru' ? 'selected' : '' }}>Baru</option>
                            <option value="Menunggu Persetujuan" {{ request('status') == 'Menunggu Persetujuan' ? 'selected' : '' }}>Menunggu Persetujuan</option>
                            <option value="Disetujui" {{ request('status') == 'Disetujui' ? 'selected' : '' }}>Disetujui</option>
                            <option value="Ditangani" {{ request('status') == 'Ditangani' ? 'selected' : '' }}>Ditangani</option>
                            <option value="Selesai" {{ request('status') == 'Selesai' ? 'selected' : '' }}>Selesai</option>
                            <option value="Ditolak" {{ request('status') == 'Ditolak' ? 'selected' : '' }}>Ditolak</option>
                        </select>
                    </div>
                </x-slot:filters>
                @if(request('status'))
                <x-slot:reset>
                    <a href="{{ route('tindak-lanjut.index') }}" class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">Reset</a>
                </x-slot:reset>
                @endif
            </x-ui.action-bar>
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
        
        {{-- Pagination Footer --}}
        @if($tindakLanjut->hasPages())
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50 flex flex-col items-center justify-between gap-4 md:flex-row">
            <p class="text-sm text-gray-500 text-center md:text-left">
                Menampilkan <span class="font-semibold text-gray-900">{{ $tindakLanjut->firstItem() }}</span> 
                sampai <span class="font-semibold text-gray-900">{{ $tindakLanjut->lastItem() }}</span> 
                dari <span class="font-semibold text-gray-900">{{ $tindakLanjut->total() }}</span> data
            </p>
            <div class="flex items-center gap-2">
                @if($tindakLanjut->onFirstPage())
                    <button type="button" class="btn btn-sm btn-secondary text-gray-400 cursor-not-allowed bg-white/50" disabled>
                        <x-ui.icon name="chevron-left" size="16" />
                        <span>Sebelumnya</span>
                    </button>
                @else
                    <a href="{{ $tindakLanjut->previousPageUrl() }}" class="btn btn-sm btn-secondary hover:text-indigo-600 hover:border-indigo-200 bg-white">
                        <x-ui.icon name="chevron-left" size="16" />
                        <span>Sebelumnya</span>
                    </a>
                @endif
                @if($tindakLanjut->hasMorePages())
                    <a href="{{ $tindakLanjut->nextPageUrl() }}" class="btn btn-sm btn-secondary hover:text-indigo-600 hover:border-indigo-200 bg-white">
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
