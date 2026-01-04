{{-- Partial: Konsentrasi Table for AJAX reloading --}}
<div x-init="$dispatch('update-total-data', { total: {{ $konsentrasiList->total() }} })">
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th class="w-12">No</th>
                    <th>Kode</th>
                    <th>Nama Konsentrasi</th>
                    <th>Jurusan</th>
                    <th class="text-center">Kelas</th>
                    <th class="text-center">Status</th>
                    <th class="w-32 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($konsentrasiList ?? [] as $index => $k)
                    <tr>
                        <td class="text-gray-500">{{ $konsentrasiList->firstItem() + $index }}</td>
                        <td><span class="font-mono text-sm bg-gray-100 px-2 py-1 rounded-md">{{ $k->kode_konsentrasi ?? '-' }}</span></td>
                        <td class="font-medium text-gray-800">{{ $k->nama_konsentrasi }}</td>
                        <td class="text-gray-500">{{ $k->jurusan->nama_jurusan ?? '-' }}</td>
                        <td class="text-center"><span class="badge badge-primary">{{ $k->kelas_count ?? 0 }}</span></td>
                        <td class="text-center">
                            @if($k->is_active)
                                <span class="badge badge-success">Aktif</span>
                            @else
                                <span class="badge badge-secondary">Nonaktif</span>
                            @endif
                        </td>
                        <td>
                            {{-- Desktop: Icon buttons --}}
                            <div class="action-buttons-desktop">
                                <a href="{{ route('konsentrasi.show', $k->id) }}" class="btn btn-icon btn-outline" title="Detail">
                                    <x-ui.icon name="eye" size="16" />
                                </a>
                                <a href="{{ route('konsentrasi.edit', $k->id) }}" class="btn btn-icon btn-outline" title="Edit">
                                    <x-ui.icon name="edit" size="16" />
                                </a>
                                <form action="{{ route('konsentrasi.destroy', $k->id) }}" method="POST" class="inline" onsubmit="return confirm('Hapus konsentrasi ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-icon btn-outline text-red-500 hover:bg-red-50" title="Hapus">
                                        <x-ui.icon name="trash" size="16" />
                                    </button>
                                </form>
                            </div>
                            
                            {{-- Mobile: Dropdown --}}
                            <div class="action-dropdown-mobile" x-data="{ open: false }">
                                <button @click="open = !open" @click.away="open = false" class="action-dropdown-trigger">
                                    <x-ui.icon name="more-horizontal" size="18" />
                                </button>
                                <div x-show="open" x-transition class="action-dropdown-menu">
                                    <a href="{{ route('konsentrasi.show', $k->id) }}" class="action-dropdown-item">
                                        <x-ui.icon name="eye" size="16" />
                                        Detail
                                    </a>
                                    <a href="{{ route('konsentrasi.edit', $k->id) }}" class="action-dropdown-item action-dropdown-item--edit">
                                        <x-ui.icon name="edit" size="16" />
                                        Edit
                                    </a>
                                    <div class="action-dropdown-divider"></div>
                                    <form action="{{ route('konsentrasi.destroy', $k->id) }}" method="POST" onsubmit="return confirm('Hapus konsentrasi ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="action-dropdown-item action-dropdown-item--delete">
                                            <x-ui.icon name="trash" size="16" />
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">
                            <x-ui.empty-state 
                                icon="layers" 
                                title="Tidak Ada Data" 
                                description="Belum ada konsentrasi yang terdaftar." 
                            >
                                <x-slot:action>
                                    <a href="{{ route('konsentrasi.create') }}" class="btn btn-primary">
                                        <x-ui.icon name="plus" size="18" />
                                        <span>Tambah Konsentrasi</span>
                                    </a>
                                </x-slot:action>
                            </x-ui.empty-state>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($konsentrasiList->hasPages())
        <div class="mt-4">
            {{ $konsentrasiList->links() }}
        </div>
    @endif
</div>
