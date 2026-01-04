@extends('layouts.app')

@section('title', 'Manajemen Kelas')

@section('page-header')
    <x-page-header 
        title="Manajemen Kelas" 
        subtitle="Kelola data rombongan belajar sekolah."
        :total="$kelasList->count()"
    />
@endsection

@section('content')
<div class="space-y-6" x-data="{ selectionMode: false, selected: [], selectAll: false }">
    {{-- Action Button --}}
    <div class="flex justify-end">
        <a href="{{ route('kelas.create') }}" class="btn btn-primary">
            <x-ui.icon name="plus" size="18" />
            <span>Tambah Kelas</span>
        </a>
    </div>

    {{-- Wali Created Info --}}
    @if(session('wali_created'))
        @php $w = session('wali_created'); @endphp
        <div class="p-4 bg-indigo-50 rounded-xl border border-indigo-100">
            <h4 class="text-indigo-700 font-bold flex items-center gap-2 mb-3">
                <x-ui.icon name="user-check" size="18" />
                Akun Wali Kelas Baru Dibuat
            </h4>
            <div class="flex flex-wrap gap-4">
                <div class="bg-white p-3 rounded-lg border border-indigo-100 flex-1 min-w-[150px]">
                    <span class="text-xs text-indigo-400 uppercase font-bold">Username</span>
                    <div class="font-mono text-indigo-900 font-bold text-lg">{{ $w['username'] }}</div>
                </div>
                <div class="bg-white p-3 rounded-lg border border-indigo-100 flex-1 min-w-[150px]">
                    <span class="text-xs text-indigo-400 uppercase font-bold">Password</span>
                    <div class="font-mono text-rose-600 font-bold text-lg">{{ $w['password'] }}</div>
                </div>
            </div>
            <p class="text-xs text-indigo-500 mt-3 italic">* Harap simpan kredensial ini.</p>
        </div>
    @endif

    {{-- Bulk Action Toolbar --}}
    <div x-show="selected.length > 0" x-transition x-cloak class="bg-indigo-50 p-3 flex flex-col sm:flex-row justify-between items-center gap-3 rounded-xl border border-indigo-100 shadow-sm">
        <div class="flex items-center gap-2">
            <span class="flex items-center justify-center w-6 h-6 rounded-full bg-indigo-600 text-white text-xs font-bold" x-text="selected.length"></span>
            <span class="text-sm font-medium text-indigo-900">Kelas Terpilih</span>
        </div>
        <div class="flex flex-wrap gap-2">
            <button type="button" @click="if(confirm('Hapus ' + selected.length + ' kelas terpilih?')) { alert('Fitur bulk delete sedang dalam pengembangan.'); }" class="btn btn-sm btn-white text-red-600 border-red-200 hover:bg-red-50">
                <x-ui.icon name="trash" size="14" />
                Hapus Massal
            </button>
            <button type="button" @click="selected = []; selectionMode = false;" class="btn btn-sm btn-white">
                Batal
            </button>
        </div>
    </div>

    {{-- Stats --}}
    <div class="flex justify-between items-center">
        <span class="text-sm text-gray-500">
            Total: <b class="text-blue-600">{{ method_exists($kelasList, 'total') ? $kelasList->total() : $kelasList->count() }}</b> kelas
        </span>
    </div>
    
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th class="w-12">No</th>
                    <th>Nama Kelas</th>
                    <th>Jurusan</th>
                    <th>Konsentrasi</th>
                    <th>Wali Kelas</th>
                    <th class="text-center">Siswa</th>
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
                                        @change="selectAll ? selected = {{ Js::from($kelasList->pluck('id')->map(fn($id) => (string)$id)->values()) }} : selected = []"
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
                @forelse($kelasList ?? [] as $index => $k)
                    <tr :class="{ 'bg-indigo-50/40': selected.includes('{{ $k->id }}') }">
                        <td class="text-gray-500">{{ $loop->iteration }}</td>
                        <td class="font-medium text-gray-800">{{ $k->nama_kelas }}</td>
                        <td>
                            <span class="font-mono text-sm bg-blue-50 text-blue-700 px-2 py-1 rounded-md">
                                {{ $k->jurusan->kode_jurusan ?? strtoupper(substr($k->jurusan->nama_jurusan ?? '-', 0, 3)) }}
                            </span>
                        </td>
                        <td>
                            @if($k->konsentrasi)
                                <span class="font-mono text-sm bg-purple-50 text-purple-700 px-2 py-1 rounded-md">
                                    {{ $k->konsentrasi->kode_konsentrasi ?? strtoupper(substr($k->konsentrasi->nama_konsentrasi, 0, 3)) }}
                                </span>
                            @else
                                <span class="text-gray-300 text-sm">-</span>
                            @endif
                        </td>
                        <td class="text-gray-600">
                            {{ $k->waliKelas->username ?? '-' }}
                        </td>
                        <td class="text-center">
                            <span class="badge badge-info">{{ $k->siswa_count ?? $k->siswa()->count() }}</span>
                        </td>
                        <td class="text-center relative">
                            {{-- Normal Mode: Kebab Dropdown --}}
                            <template x-if="!selectionMode">
                                <div x-data="{
                                         open: false,
                                         pressTimer: null,
                                         isLongPress: false,
                                         
                                         startPress() {
                                             this.isLongPress = false;
                                             this.pressTimer = setTimeout(() => {
                                                 this.isLongPress = true;
                                                 this.selectionMode = true;
                                                 if (!this.selected.includes('{{ $k->id }}')) {
                                                     this.selected.push('{{ $k->id }}');
                                                 }
                                                 if (navigator.vibrate) navigator.vibrate(50);
                                             }, 500);
                                         },
                                         endPress() {
                                             clearTimeout(this.pressTimer);
                                         },
                                         handleClick() {
                                             if (!this.isLongPress) {
                                                 this.open = !this.open;
                                             }
                                             this.isLongPress = false;
                                         }
                                     }"
                                     class="relative inline-block text-left"
                                >
                                    <button 
                                        x-ref="trigger" 
                                        @click="handleClick()" 
                                        @mousedown="startPress()"
                                        @touchstart.passive="startPress()"
                                        @mouseup="endPress()"
                                        @mouseleave="endPress()"
                                        @touchend="endPress()"
                                        type="button" 
                                        class="p-1.5 text-gray-400 rounded-lg hover:bg-gray-100 hover:text-gray-600 transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 select-none"
                                    >
                                        <x-ui.icon name="more-horizontal" size="18" />
                                    </button>
                                    
                                    <template x-teleport="body">
                                        <div x-show="open" 
                                             @click.outside="open = false"
                                             x-transition:enter="transition ease-out duration-100"
                                             x-transition:enter-start="transform opacity-0 scale-95"
                                             x-transition:enter-end="transform opacity-100 scale-100"
                                             x-transition:leave="transition ease-in duration-75"
                                             x-transition:leave-start="transform opacity-100 scale-100"
                                             x-transition:leave-end="transform opacity-0 scale-95"
                                             class="w-36 origin-top-right rounded-xl bg-white shadow-xl ring-1 ring-black ring-opacity-5 focus:outline-none border border-gray-100"
                                             :style="open ? (() => {
                                                 const rect = $refs.trigger.getBoundingClientRect();
                                                 return `position: fixed; z-index: 9999; top: ${rect.bottom + 4}px; left: ${rect.right - 144}px;`;
                                             })() : 'display: none;'"
                                        >
                                            <div class="py-1">
                                                <a href="{{ route('kelas.show', $k->id) }}" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-indigo-600 transition-colors">
                                                    <x-ui.icon name="info" size="14" />
                                                    Detail
                                                </a>
                                                @can('update', $k)
                                                <a href="{{ route('kelas.edit', $k->id) }}" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-indigo-600 transition-colors">
                                                    <x-ui.icon name="edit" size="14" />
                                                    Edit
                                                </a>
                                                @endcan
                                                @can('delete', $k)
                                                <div class="border-t border-gray-100 my-1"></div>
                                                <form action="{{ route('kelas.destroy', $k->id) }}" method="POST" onsubmit="return confirm('Hapus kelas ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="flex w-full items-center gap-2 px-4 py-2 text-sm text-red-600 hover:bg-red-50 hover:text-red-700 transition-colors">
                                                        <x-ui.icon name="trash" size="14" />
                                                        Hapus
                                                    </button>
                                                </form>
                                                @endcan
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </template>

                            {{-- Selection Mode: Checkbox --}}
                            <template x-if="selectionMode">
                                <div class="flex justify-center">
                                    <input type="checkbox" value="{{ $k->id }}" x-model="selected" class="w-5 h-5 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500 cursor-pointer">
                                </div>
                            </template>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">
                            <x-ui.empty-state 
                                title="Tidak Ada Data Kelas" 
                                description="Belum ada kelas yang terdaftar." 
                                icon="layout"
                            >
                                <x-slot:action>
                                    @can('create', App\Models\Kelas::class)
                                    <a href="{{ route('kelas.create') }}" class="btn btn-primary">
                                        <x-ui.icon name="plus" size="18" />
                                        <span>Tambah Kelas</span>
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
    @if(method_exists($kelasList, 'links'))
        <div class="flex justify-center">
            {{ $kelasList->links() }}
        </div>
    @endif
</div>
@endsection

