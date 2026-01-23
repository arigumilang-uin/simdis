{{-- Dispatch total count and page IDs to parent --}}
@php
    // Priority: 
    // 1. Variable passed from Controller/Parent View ($userRole)
    // 2. Request parameter (AJAX refresh)
    // 3. Auth user check (Fallback)
    // 4. Default 'Operator Sekolah'
    
    if (!isset($userRole)) {
        $userRole = request('user_role');
        if (!$userRole && auth()->check()) {
            $userRole = auth()->user()->effectiveRoleName();
        }
    }
    $userRole = $userRole ?? 'Operator Sekolah';
@endphp
<div x-data x-init="$dispatch('update-total-data', { total: {{ $siswa->total() }} }); $dispatch('update-page-ids', {{ json_encode($siswa->pluck('id')->map(fn($id) => (string) $id)) }})"></div>

    {{-- Data Table --}}
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th class="w-12">No</th>
                    <th>NISN</th>
                    <th>Nama Siswa</th>
                    <th>Kelas</th>
                    <th>Kontak Wali</th>
                    @if(in_array($userRole, ['Wali Kelas', 'Kaprodi']))
                        {{-- Simple "Aksi" header for Wali Kelas & Kaprodi (no selection mode) --}}
                        <th class="w-20 text-center">
                            <span class="text-xs font-semibold uppercase tracking-wider text-gray-500">Aksi</span>
                        </th>
                    @else
                        {{-- Selection mode header for Operator/Waka --}}
                        <x-table.action-header />
                    @endif
            </tr>
        </thead>
        <tbody>
            @forelse($siswa as $index => $s)
                <tr :class="{ 'bg-indigo-50/40': selected.includes('{{ $s->id }}') }">
                    <td>
                        <a href="{{ route('siswa.show', $s->id) }}" class="text-gray-500 hover:text-primary-600 transition-colors">
                            {{ $siswa->firstItem() + $index }}
                        </a>
                    </td>
                    <td>
                        <a href="{{ route('siswa.show', $s->id) }}" class="inline-block font-mono text-xs bg-gray-100 px-2 py-1 rounded-md hover:bg-primary-100 hover:text-primary-700 transition-colors">
                            {{ $s->nisn }}
                        </a>
                    </td>
                    <td>
                        <a href="{{ route('siswa.show', $s->id) }}" class="font-medium text-gray-800 hover:text-primary-600 transition-colors">
                            {{ $s->nama_siswa }}
                        </a>
                    </td>
                    <td>
                        @if($s->kelas)
                            <a href="{{ route('kelas.show', $s->kelas->id) }}" class="badge badge-primary hover:bg-primary-200 transition-colors">
                                {{ $s->kelas->nama_kelas }}
                            </a>
                        @else
                            <span class="badge badge-neutral">-</span>
                        @endif
                    </td>
                    <td>
                        @if($s->nomor_hp_wali_murid)
                            <a href="https://wa.me/62{{ ltrim($s->nomor_hp_wali_murid, '0') }}" target="_blank" class="text-emerald-600 hover:text-emerald-700 font-medium inline-flex items-center gap-1">
                                <x-ui.icon name="brand-whatsapp" size="14" class="fill-current" strokeWidth="0" />
                                {{ $s->nomor_hp_wali_murid }}
                            </a>
                        @else
                            <span class="text-gray-400 text-sm">-</span>
                        @endif
                    </td>
                    <x-table.action-column :id="$s->id" :allow-selection="!in_array($userRole, ['Wali Kelas', 'Kaprodi'])">
                        <x-table.action-item icon="eye" href="{{ route('siswa.show', $s->id) }}">
                            Detail
                        </x-table.action-item>
                        
                        {{-- Edit: Available for Wali Kelas (HP only) and Operator/Waka --}}
                        @if($userRole === 'Wali Kelas')
                            <x-table.action-item icon="edit" href="{{ route('siswa.edit', $s->id) }}">
                                Edit No HP
                            </x-table.action-item>
                        @elseif(!in_array($userRole, ['Kaprodi']))
                            @can('update', $s)
                                <x-table.action-item icon="edit" href="{{ route('siswa.edit', $s->id) }}">
                                    Edit
                                </x-table.action-item>
                            @endcan
                        @endif
                        
                        {{-- Delete: Only for Operator/Waka (not for Wali Kelas/Kaprodi) --}}
                        @if(!in_array($userRole, ['Wali Kelas', 'Kaprodi']))
                            @can('delete', $s)
                                <x-table.action-separator />
                                <x-table.action-item 
                                    icon="trash" 
                                    class="text-red-600 hover:bg-red-50 hover:text-red-700"
                                    @click="open = false; $dispatch('open-delete-modal', { id: {{ $s->id }}, nama: '{{ $s->nama_siswa }}', nisn: '{{ $s->nisn }}' })"
                                >
                                    Hapus
                                </x-table.action-item>
                            @endcan
                        @endif
                    </x-table.action-column>
                </tr>
                @empty
                    <tr>
                        <td colspan="6">
                            <x-ui.empty-state 
                                icon="search" 
                                title="Data Tidak Ditemukan" 
                                description="Tidak ada data siswa yang sesuai dengan filter Anda." 
                            >
                                <x-slot:action>
                                    @can('create', App\Models\Siswa::class)
                                        <a href="{{ route('siswa.create') }}" class="btn btn-primary">
                                            <x-ui.icon name="plus" size="18" />
                                            <span>Tambah Siswa Baru</span>
                                        </a>
                                    @endcan
                                </x-slot:action>
                            </x-ui.empty-state>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    {{-- Pagination --}}
    {{-- Pagination Footer --}}
    @if($siswa->hasPages())
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50 flex flex-col items-center justify-between gap-4 md:flex-row">
            <p class="text-sm text-gray-500 text-center md:text-left">
                Menampilkan <span class="font-semibold text-gray-900">{{ $siswa->firstItem() }}</span> 
                sampai <span class="font-semibold text-gray-900">{{ $siswa->lastItem() }}</span> 
                dari <span class="font-semibold text-gray-900">{{ $siswa->total() }}</span> data
            </p>
            
            <div class="flex items-center gap-2">
                {{-- Previous --}}
                @if($siswa->onFirstPage())
                    <button type="button" class="btn btn-sm btn-secondary text-gray-400 cursor-not-allowed bg-white/50" disabled>
                        <x-ui.icon name="chevron-left" size="16" />
                        <span>Sebelumnya</span>
                    </button>
                @else
                    {{-- Use data-href for AJAX handling --}}
                    <a href="{{ $siswa->previousPageUrl() }}" class="btn btn-sm btn-secondary hover:text-indigo-600 hover:border-indigo-200 bg-white" data-page="prev">
                        <x-ui.icon name="chevron-left" size="16" />
                        <span>Sebelumnya</span>
                    </a>
                @endif
                
                {{-- Next --}}
                @if($siswa->hasMorePages())
                    {{-- Use data-href for AJAX handling --}}
                    <a href="{{ $siswa->nextPageUrl() }}" class="btn btn-sm btn-secondary hover:text-indigo-600 hover:border-indigo-200 bg-white" data-page="next">
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
