{{-- Dispatch total count and page IDs to parent for Select All functionality --}}
<div x-data x-init="$dispatch('update-total-data', { total: {{ $deletedSiswa->total() }} }); $dispatch('update-page-ids', {{ json_encode($deletedSiswa->pluck('id')->map(fn($id) => (string) $id)) }})"></div>

{{-- Data Table --}}
<div class="table-container">
    <table class="table">
        <thead>
            <tr>
                <th class="w-12">No</th>
                <th>NISN</th>
                <th>Nama Siswa</th>
                <th>Kelas Terakhir</th>
                <th>Alasan Keluar</th>
                <th>Tanggal Dihapus</th>
                <x-table.action-header />
            </tr>
        </thead>
        <tbody>
            @forelse($deletedSiswa ?? [] as $index => $s)
                <tr :class="{ 'bg-indigo-50/40': selected.includes('{{ $s->id }}') }">
                    <td class="text-gray-500">{{ ($deletedSiswa->currentPage() - 1) * $deletedSiswa->perPage() + $index + 1 }}</td>
                    <td>
                        <span class="font-mono text-xs bg-gray-100 px-2 py-1 rounded-md">{{ $s->nisn }}</span>
                    </td>
                    <td class="font-medium text-gray-800">{{ $s->nama_siswa }}</td>
                    <td>
                        <span class="badge badge-neutral">{{ $s->kelas->nama_kelas ?? '-' }}</span>
                    </td>
                    <td>
                        @php
                            $alasanColors = [
                                'Alumni' => 'badge-success',
                                'Dikeluarkan' => 'badge-danger',
                                'Pindah Sekolah' => 'badge-warning',
                                'Lainnya' => 'badge-neutral',
                            ];
                        @endphp
                        <span class="badge {{ $alasanColors[$s->alasan_keluar] ?? 'badge-neutral' }}">{{ $s->alasan_keluar ?? '-' }}</span>
                        @if($s->keterangan_keluar)
                            <p class="text-xs text-gray-500 mt-1">{{ Str::limit($s->keterangan_keluar, 30) }}</p>
                        @endif
                    </td>
                    <td class="text-gray-500 text-sm">{{ $s->deleted_at ? $s->deleted_at->format('d M Y H:i') : '-' }}</td>
                    
                    {{-- Action Column using Reusable Component --}}
                    <x-table.action-column :id="$s->id">
                        {{-- Restore Action --}}
                        <x-table.action-item 
                            icon="rotate-ccw" 
                            class="text-emerald-600 hover:bg-emerald-50 hover:text-emerald-700"
                            @click="open = false; if(confirm('Restore siswa ini ke daftar aktif?')) { document.getElementById('restore-form-{{ $s->id }}').submit(); }"
                        >
                            Restore
                        </x-table.action-item>
                        
                        <x-table.action-separator />
                        
                        {{-- Permanent Delete Action --}}
                        <x-table.action-item 
                            icon="trash" 
                            class="text-red-600 hover:bg-red-50 hover:text-red-700"
                            @click="open = false; $dispatch('open-permanent-delete-modal', { id: {{ $s->id }}, nama: '{{ addslashes($s->nama_siswa) }}', nisn: '{{ $s->nisn }}' })"
                        >
                            Hapus Permanen
                        </x-table.action-item>
                    </x-table.action-column>
                </tr>
                
                {{-- Hidden Restore Form --}}
                <form id="restore-form-{{ $s->id }}" action="{{ route('siswa.restore', $s->id) }}" method="POST" class="hidden">
                    @csrf
                </form>
            @empty
                <tr>
                    <td colspan="7">
                        <x-ui.empty-state 
                            icon="check-circle" 
                            title="Tidak Ada Data Arsip" 
                            description="Tidak ada siswa yang telah dihapus." 
                        />
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Pagination Footer --}}
@if(method_exists($deletedSiswa ?? [], 'hasPages') && $deletedSiswa->hasPages())
    <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50 flex flex-col items-center justify-between gap-4 md:flex-row">
        <p class="text-sm text-gray-500 text-center md:text-left">
            Menampilkan <span class="font-semibold text-gray-900">{{ $deletedSiswa->firstItem() }}</span> 
            sampai <span class="font-semibold text-gray-900">{{ $deletedSiswa->lastItem() }}</span> 
            dari <span class="font-semibold text-gray-900">{{ $deletedSiswa->total() }}</span> data
        </p>
        
        <div class="flex items-center gap-2">
            {{-- Previous --}}
            @if($deletedSiswa->onFirstPage())
                <button type="button" class="btn btn-sm btn-secondary text-gray-400 cursor-not-allowed bg-white/50" disabled>
                    <x-ui.icon name="chevron-left" size="16" />
                    <span>Sebelumnya</span>
                </button>
            @else
                <a href="{{ $deletedSiswa->previousPageUrl() }}" class="btn btn-sm btn-secondary hover:text-indigo-600 hover:border-indigo-200 bg-white">
                    <x-ui.icon name="chevron-left" size="16" />
                    <span>Sebelumnya</span>
                </a>
            @endif
            
            {{-- Next --}}
            @if($deletedSiswa->hasMorePages())
                <a href="{{ $deletedSiswa->nextPageUrl() }}" class="btn btn-sm btn-secondary hover:text-indigo-600 hover:border-indigo-200 bg-white">
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
