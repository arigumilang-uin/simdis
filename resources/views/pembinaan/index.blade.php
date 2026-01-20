@extends('layouts.app')

@section('title', 'Pembinaan Internal')

@section('page-header')
    <x-page-header 
        title="Pembinaan Internal" 
        subtitle="Monitoring dan tracking status pembinaan internal siswa."
        :total="$stats['total'] ?? 0"
        totalLabel="siswa"
    />
@endsection

@section('content')
<div class="space-y-6" x-data="pembinaanPage()">
    {{-- Statistics Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="card p-5">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wide">Total</span>
                <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                    <x-ui.icon name="users" size="20" />
                </div>
            </div>
            <h3 class="text-2xl font-bold text-gray-800">{{ $stats['total'] ?? 0 }}</h3>
            <p class="text-[10px] text-gray-400 uppercase font-bold">Siswa</p>
        </div>

        <div class="card p-5 border-amber-200">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[10px] font-bold text-amber-500 uppercase tracking-wide">Perlu Pembinaan</span>
                <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                    <x-ui.icon name="alert-triangle" size="20" />
                </div>
            </div>
            <h3 class="text-2xl font-bold text-amber-600">{{ $stats['perlu_pembinaan'] ?? 0 }}</h3>
            <p class="text-[10px] text-gray-400 uppercase font-bold">Menunggu</p>
        </div>

        <div class="card p-5 border-blue-200">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[10px] font-bold text-blue-500 uppercase tracking-wide">Sedang Dibina</span>
                <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                    <x-ui.icon name="shield" size="20" />
                </div>
            </div>
            <h3 class="text-2xl font-bold text-blue-600">{{ $stats['sedang_dibina'] ?? 0 }}</h3>
            <p class="text-[10px] text-gray-400 uppercase font-bold">Proses</p>
        </div>

        <div class="card p-5 border-emerald-200">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[10px] font-bold text-emerald-500 uppercase tracking-wide">Selesai</span>
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <x-ui.icon name="check-circle" size="20" />
                </div>
            </div>
            <h3 class="text-2xl font-bold text-emerald-600">{{ $stats['selesai'] ?? 0 }}</h3>
            <p class="text-[10px] text-gray-400 uppercase font-bold">Tuntas</p>
        </div>
    </div>

    {{-- Table with Unified Layout --}}
    <div class="bg-white md:border md:border-gray-200 md:rounded-xl md:shadow-sm overflow-hidden mb-8 border-b border-gray-200 md:border-b-0">
        {{-- Toolbar --}}
        <div class="px-4 md:px-6 py-5 border-b border-gray-100 bg-white">
            <x-ui.action-bar :total="count($pembinaanList ?? [])" totalLabel="Siswa" class="!gap-4">
                <x-slot:filters>
                    <div class="space-y-4">
                        {{-- Status --}}
                        <div class="space-y-1">
                            <label class="text-xs font-semibold text-gray-500 uppercase">Status</label>
                            <select name="status" class="form-select w-full text-sm rounded-lg" onchange="this.form.submit()" form="filter-form">
                                <option value="">Semua Status</option>
                                <option value="Perlu Pembinaan" {{ ($statusFilter ?? '') == 'Perlu Pembinaan' ? 'selected' : '' }}>🟡 Perlu Pembinaan</option>
                                <option value="Sedang Dibina" {{ ($statusFilter ?? '') == 'Sedang Dibina' ? 'selected' : '' }}>🔵 Sedang Dibina</option>
                                <option value="Selesai" {{ ($statusFilter ?? '') == 'Selesai' ? 'selected' : '' }}>🟢 Selesai</option>
                            </select>
                        </div>
                        
                        {{-- Range Poin --}}
                        <div class="space-y-1">
                            <label class="text-xs font-semibold text-gray-500 uppercase">Range Poin</label>
                            <select name="rule_id" class="form-select w-full text-sm rounded-lg" onchange="this.form.submit()" form="filter-form">
                                <option value="">Semua Range</option>
                                @foreach($rules ?? [] as $rule)
                                    <option value="{{ $rule->id }}" {{ ($ruleId ?? '') == $rule->id ? 'selected' : '' }}>{{ $rule->getRangeText() }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        {{-- Kelas --}}
                        <div class="space-y-1">
                            <label class="text-xs font-semibold text-gray-500 uppercase">Kelas</label>
                            <select name="kelas_id" class="form-select w-full text-sm rounded-lg" onchange="this.form.submit()" form="filter-form">
                                <option value="">Semua Kelas</option>
                                @foreach($kelasList ?? [] as $kelas)
                                    <option value="{{ $kelas->id }}" {{ ($kelasId ?? '') == $kelas->id ? 'selected' : '' }}>{{ $kelas->nama_kelas }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        {{-- Jurusan --}}
                        <div class="space-y-1">
                            <label class="text-xs font-semibold text-gray-500 uppercase">Jurusan</label>
                            <select name="jurusan_id" class="form-select w-full text-sm rounded-lg" onchange="this.form.submit()" form="filter-form">
                                <option value="">Semua Jurusan</option>
                                @foreach($jurusanList ?? [] as $jurusan)
                                    <option value="{{ $jurusan->id }}" {{ ($jurusanId ?? '') == $jurusan->id ? 'selected' : '' }}>{{ $jurusan->nama_jurusan }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </x-slot:filters>
                @if(request()->hasAny(['status', 'rule_id', 'kelas_id', 'jurusan_id']))
                <x-slot:reset>
                    <a href="{{ route('pembinaan.index') }}" class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">Reset</a>
                </x-slot:reset>
                @endif
            </x-ui.action-bar>
            
            {{-- Hidden form for filter submission --}}
            <form id="filter-form" method="GET" action="{{ route('pembinaan.index') }}" class="hidden"></form>
        </div>

        {{-- Table --}}
        <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Siswa</th>
                    <th class="">Kelas</th>
                    <th class="text-center">Poin</th>
                    <th class="">Keterangan</th>
                    <th class="text-center">Status</th>
                    <th class="">Dibina Oleh</th>
                    <th class="w-32 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pembinaanList ?? [] as $item)
                    <tr>
                        {{-- Siswa --}}
                        <td>
                            <a href="{{ route('siswa.show', $item->siswa->id ?? 0) }}" class="font-medium text-gray-800 hover:text-blue-600">
                                {{ $item->siswa->nama_siswa ?? '-' }}
                            </a>
                            <span class="block text-[10px] text-gray-400 font-mono">{{ $item->siswa->nisn ?? '-' }}</span>
                        </td>
                        
                        {{-- Kelas --}}
                        <td class="">
                            <span class="font-medium text-gray-700">{{ $item->siswa->kelas->nama_kelas ?? '-' }}</span>
                            <span class="block text-[10px] text-gray-400">{{ $item->siswa->kelas->jurusan->nama_jurusan ?? '-' }}</span>
                        </td>
                        
                        {{-- Poin --}}
                        <td class="text-center">
                            @php
                                $p = $item->total_poin_saat_trigger ?? 0;
                                $badgeClass = $p > 300 ? 'badge-danger' : ($p > 100 ? 'badge-warning' : 'badge-info');
                            @endphp
                            <span class="badge {{ $badgeClass }}">{{ $p }} Poin</span>
                        </td>
                        
                        {{-- Keterangan --}}
                        <td class="max-w-xs">
                            <p class="text-xs text-gray-600 italic truncate">"{{ $item->keterangan_pembinaan }}"</p>
                            <span class="text-[9px] font-bold text-gray-400 uppercase">{{ $item->range_text }}</span>
                        </td>
                        
                        {{-- Status --}}
                        <td class="text-center">
                            @php
                                $status = $item->status->value ?? $item->status ?? 'Unknown';
                                $statusClass = match($status) {
                                    'Perlu Pembinaan' => 'badge-warning',
                                    'Sedang Dibina' => 'badge-info',
                                    'Selesai' => 'badge-success',
                                    default => 'badge-neutral',
                                };
                            @endphp
                            <span class="badge {{ $statusClass }}">{{ $status }}</span>
                            @if($item->dibina_at)
                                <span class="block text-[9px] text-gray-400 mt-1">{{ $item->dibina_at->format('d M Y H:i') }}</span>
                            @endif
                        </td>
                        
                        {{-- Dibina Oleh --}}
                        <td class="">
                            @if($item->dibinaOleh)
                                <span class="font-medium text-gray-700">{{ $item->dibinaOleh->username }}</span>
                            @else
                                <span class="text-gray-400 italic text-xs">-</span>
                            @endif
                        </td>
                        
                        {{-- Aksi --}}
                        <td class="text-center">
                            @php $statusValue = $item->status->value ?? $item->status; @endphp
                            @if($statusValue === 'Perlu Pembinaan')
                                <form action="{{ route('pembinaan.mulai', $item->id) }}" method="POST" 
                                      onsubmit="return confirm('Mulai pembinaan untuk ' + {{ json_encode($item->siswa->nama_siswa ?? 'siswa ini') }} + '?')">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit" class="btn btn-primary text-xs">
                                        <x-ui.icon name="play" size="14" />
                                        Mulai
                                    </button>
                                </form>
                            @elseif($statusValue === 'Sedang Dibina')
                                <button type="button" 
                                        @click="openModal({{ $item->id }}, {{ json_encode($item->siswa->nama_siswa ?? '') }})" 
                                        class="btn btn-success text-xs">
                                    <x-ui.icon name="check-circle" size="14" />
                                    Selesai
                                </button>
                            @else
                                <span class="text-emerald-600 font-bold text-xs flex items-center justify-center gap-1">
                                    <x-ui.icon name="check-circle" size="14" />
                                    Tuntas
                                </span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">
                            <x-ui.empty-state 
                                icon="shield" 
                                title="Tidak Ada Data" 
                                description="Belum ada siswa yang perlu pembinaan atau semua sudah selesai." 
                            />
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>

    {{-- Info Section --}}
    <div class="p-6 bg-blue-50 rounded-xl border border-blue-100">
        <h6 class="text-sm font-bold text-blue-800 mb-3 flex items-center gap-2">
            <x-ui.icon name="info" size="16" />
            Informasi Penting
        </h6>
        <ul class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-2 text-xs text-blue-700/80 ml-4 list-disc">
            <li><strong>Perlu Pembinaan</strong> = Siswa yang mencapai threshold poin dan belum ditangani.</li>
            <li><strong>Sedang Dibina</strong> = Proses pembinaan sedang berlangsung oleh pembina.</li>
            <li><strong>Selesai</strong> = Pembinaan telah selesai dengan hasil yang tercatat.</li>
            <li>Klik nama siswa untuk melihat <strong>riwayat lengkap</strong> pelanggaran.</li>
        </ul>
    </div>

    {{-- Modal Selesaikan --}}
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" @keydown.escape.window="showModal = false">
        <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm" @click="showModal = false"></div>
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative w-full max-w-md bg-white rounded-2xl shadow-2xl" @click.stop x-transition>
                <form :action="'/pembinaan/' + selectedId + '/selesaikan'" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="p-6 border-b border-gray-100 bg-emerald-50">
                        <h3 class="text-lg font-bold text-emerald-800 flex items-center gap-2">
                            <x-ui.icon name="check-circle" size="20" />
                            Selesaikan Pembinaan
                        </h3>
                    </div>
                    
                    <div class="p-6 space-y-4">
                        <p class="text-sm text-gray-600">
                            Selesaikan pembinaan untuk: <strong x-text="selectedName" class="text-gray-800"></strong>
                        </p>
                        
                        <x-forms.textarea 
                            name="hasil_pembinaan" 
                            label="Hasil Pembinaan" 
                            rows="4" 
                            placeholder="Tuliskan hasil/catatan pembinaan..." 
                        />
                    </div>
                    
                    <div class="p-6 border-t border-gray-100 flex justify-end gap-3">
                        <button type="button" @click="showModal = false" class="btn btn-secondary">Batal</button>
                        <button type="submit" class="btn btn-success">
                            <x-ui.icon name="check-circle" size="16" />
                            Selesaikan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function pembinaanPage() {
    return {
        showModal: false,
        selectedId: null,
        selectedName: '',
        
        openModal(id, name) {
            this.selectedId = id;
            this.selectedName = name;
            this.showModal = true;
        }
    }
}
</script>
@endpush
@endsection
