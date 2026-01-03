
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
                <th class="w-20 text-center cursor-pointer select-none hover:bg-gray-100 transition-colors group" @click="selectionMode = !selectionMode" title="Klik untuk memilih data">
                    <div class="flex items-center justify-center">
                        <template x-if="!selectionMode">
                            <div class="flex items-center justify-center gap-2 text-gray-400 group-hover:text-indigo-600 transition-colors p-1">
                                <span class="text-[10px] font-bold uppercase tracking-wider">Pilih</span>
                                <x-ui.icon name="check-square" size="16" />
                            </div>
                        </template>
                        <template x-if="selectionMode">
                            <div class="flex items-center justify-center gap-1">
                                <input type="checkbox" x-model="selectAll"
                                    @change="selectAll ? selected = {{ Js::from(($deletedSiswa ?? collect())->pluck('id')->map(fn($id) => (string)$id)->values()) }} : selected = []"
                                    @click.stop class="w-5 h-5 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500 cursor-pointer" title="Pilih Semua">
                                <button type="button" @click.stop="selectionMode = false; selected = []; selectAll = false;" class="p-1 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded transition-colors" title="Batalkan Pilih">
                                    <x-ui.icon name="x" size="14" />
                                </button>
                            </div>
                        </template>
                    </div>
                </th>
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
                    <td class="text-center relative">
                        {{-- Normal Mode --}}
                        <template x-if="!selectionMode">
                            <div class="flex items-center justify-center gap-1">
                                {{-- Restore Button --}}
                                <form action="{{ route('siswa.restore', $s->id) }}" method="POST" class="inline" onsubmit="return confirm('Restore siswa ini ke daftar aktif?')">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline text-emerald-600 border-emerald-200 hover:bg-emerald-50">
                                        <x-ui.icon name="rotate-ccw" size="14" />
                                        <span class="hidden sm:inline">Restore</span>
                                    </button>
                                </form>
                                
                                {{-- Permanent Delete Button --}}
                                <button 
                                    type="button" 
                                    class="btn btn-sm btn-outline text-red-600 border-red-200 hover:bg-red-50" 
                                    @click="$dispatch('open-permanent-delete-modal', { id: {{ $s->id }}, nama: '{{ addslashes($s->nama_siswa) }}', nisn: '{{ $s->nisn }}' })"
                                >
                                    <x-ui.icon name="trash" size="14" />
                                    <span class="hidden sm:inline">Hapus</span>
                                </button>
                            </div>
                        </template>

                        {{-- Selection Mode: Checkbox --}}
                        <template x-if="selectionMode">
                            <div class="flex justify-center">
                                <input type="checkbox" value="{{ $s->id }}" x-model="selected" class="w-5 h-5 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500 cursor-pointer">
                            </div>
                        </template>
                    </td>
                </tr>
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

{{-- Pagination --}}
@if(method_exists($deletedSiswa ?? [], 'hasPages') && $deletedSiswa->hasPages())
    <div class="flex flex-col sm:flex-row items-center justify-between gap-4 mt-4">
        <p class="text-sm text-gray-500">
            Menampilkan {{ $deletedSiswa->firstItem() }} sampai {{ $deletedSiswa->lastItem() }} dari {{ $deletedSiswa->total() }} data
        </p>
        <div class="pagination">
            {{-- Previous --}}
            @if($deletedSiswa->onFirstPage())
                <span class="pagination-btn" disabled>
                    <x-ui.icon name="chevron-left" size="16" />
                </span>
            @else
                <a href="{{ $deletedSiswa->previousPageUrl() }}" class="pagination-btn">
                    <x-ui.icon name="chevron-left" size="16" />
                </a>
            @endif
            
            {{-- Next --}}
            @if($deletedSiswa->hasMorePages())
                <a href="{{ $deletedSiswa->nextPageUrl() }}" class="pagination-btn">
                    <x-ui.icon name="chevron-right" size="16" />
                </a>
            @else
                <span class="pagination-btn" disabled>
                    <x-ui.icon name="chevron-right" size="16" />
                </span>
            @endif
        </div>
    </div>
@endif
