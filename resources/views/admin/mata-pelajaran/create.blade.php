@extends('layouts.app')

@section('title', 'Tambah Mata Pelajaran')

@section('page-header')
    <x-page-header 
        title="Tambah Mata Pelajaran" 
        subtitle="Tambahkan mata pelajaran baru untuk {{ $kurikulum?->nama ?? 'kurikulum' }}"
        :backUrl="route('admin.mata-pelajaran.index', ['kurikulum_id' => $kurikulumId, 'kelompok' => $kelompok])"
    />
@endsection

@section('content')
<x-forms.card 
    action="{{ route('admin.mata-pelajaran.store') }}" 
    maxWidth="full" 
    layout="sidebar"
    x-data="createMapelForm()"
    @submit.prevent="submitForm"
>
    {{-- Hidden fields --}}
    <input type="hidden" name="kurikulum_id" value="{{ $kurikulumId }}">
    <input type="hidden" name="kelompok" value="{{ $kelompok }}">

    {{-- LEFT COLUMN (Main Content) --}}
    <div class="lg:col-span-8 space-y-6">
        
        {{-- Context Info --}}
        @if($kurikulum)
        <div class="p-4 bg-indigo-50 border border-indigo-100 rounded-xl flex items-start gap-3">
            <div class="w-10 h-10 rounded-lg bg-indigo-100 text-indigo-600 flex items-center justify-center shrink-0">
                <x-ui.icon name="layers" size="20" />
            </div>
            <div>
                <p class="font-semibold text-indigo-800">{{ $kurikulum->kode }} - {{ $kurikulum->nama }}</p>
                <p class="text-sm text-indigo-600">Kelompok: <strong>{{ $kelompokOptions[$kelompok] ?? $kelompok }}</strong></p>
            </div>
        </div>
        @endif

        {{-- SECTION 1: Identitas Mapel --}}
        <x-forms.section 
            title="Identitas Mata Pelajaran" 
            variant="card"
            icon="book"
        >
            <x-slot name="description">Informasi dasar mata pelajaran.</x-slot>
            <x-slot name="actions">
                <div x-show="isValid" x-transition 
                        class="flex items-center gap-1.5 text-emerald-600 bg-emerald-50 px-3 py-1 rounded-full border border-emerald-100">
                    <x-ui.icon name="check" size="14" stroke-width="3" />
                    <span class="text-[11px] font-bold uppercase tracking-wider">Lengkap</span>
                </div>
            </x-slot>

            <div class="space-y-5">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <x-forms.input 
                        name="kode_mapel" 
                        label="Kode Mapel" 
                        placeholder="Contoh: MTK" 
                        x-model="form.kodeMapel"
                        help="Opsional, kode singkat."
                    />
                    
                    <x-forms.input 
                        name="nama_mapel" 
                        label="Nama Mata Pelajaran" 
                        required 
                        placeholder="Contoh: Matematika" 
                        x-model="form.namaMapel"
                    />
                </div>
                
                <x-forms.textarea 
                    name="deskripsi" 
                    label="Deskripsi" 
                    placeholder="Deskripsi singkat tentang mata pelajaran ini (opsional)"
                    rows="2"
                />
            </div>
        </x-forms.section>

        {{-- SECTION 2: Guru Pengampu --}}
        <x-forms.section 
            title="Guru Pengampu" 
            variant="card"
            icon="users"
        >
            <x-slot name="description">Pilih guru yang dapat mengajar mata pelajaran ini.</x-slot>

            <div class="border border-slate-200 rounded-lg bg-slate-50 overflow-hidden">
                <div class="max-h-64 overflow-y-auto p-3 space-y-2">
                    @forelse($guruList as $guru)
                        <label class="flex items-center gap-3 p-3 bg-white rounded-lg border border-slate-100 hover:border-indigo-200 transition cursor-pointer group">
                            <input type="checkbox" 
                                   name="guru_ids[]" 
                                   value="{{ $guru->id }}"
                                   x-model="selectedGuru"
                                   class="form-checkbox text-indigo-600 rounded">
                            <div class="flex-1">
                                <span class="font-medium text-slate-800 group-hover:text-indigo-700 transition">{{ $guru->username }}</span>
                            </div>
                            <template x-if="selectedGuru.includes('{{ $guru->id }}') || selectedGuru.includes({{ $guru->id }})">
                                <label class="flex items-center gap-1.5 text-xs bg-amber-50 px-2 py-1 rounded-full border border-amber-200">
                                    <input type="radio" 
                                           name="guru_utama_id" 
                                           value="{{ $guru->id }}"
                                           x-model="guruUtama"
                                           class="form-radio text-amber-500 w-3 h-3">
                                    <span class="text-amber-700 font-medium">Utama</span>
                                </label>
                            </template>
                        </label>
                    @empty
                        <div class="text-center py-8 text-slate-500">
                            <x-ui.icon name="user-x" size="24" class="mx-auto mb-2 opacity-50" />
                            <p class="text-sm">Belum ada data guru</p>
                        </div>
                    @endforelse
                </div>
                <div class="px-3 py-2 bg-slate-100 border-t border-slate-200 text-xs text-slate-500">
                    💡 Centang guru yang bisa mengajar mapel ini. Pilih "Utama" untuk guru utama.
                </div>
            </div>
        </x-forms.section>

        {{-- ACTIONS --}}
        <div class="flex justify-end gap-3 pt-2">
            <a href="{{ route('admin.mata-pelajaran.index', ['kurikulum_id' => $kurikulumId, 'kelompok' => $kelompok]) }}" class="px-5 py-2.5 rounded-lg border border-slate-200 text-slate-600 text-sm font-medium hover:bg-slate-50 transition-all">
                Batal
            </a>
            <button type="submit" class="px-5 py-2.5 rounded-lg text-white text-sm font-semibold shadow-sm flex items-center gap-2 transition-all active:scale-95 bg-indigo-600 hover:bg-indigo-700"
                :disabled="!isValid"
                :class="{'opacity-50 cursor-not-allowed': !isValid}">
                <x-ui.icon name="check" size="16" />
                Simpan Mapel
            </button>
        </div>
    </div>

    {{-- RIGHT COLUMN (Sidebar) --}}
    <div class="hidden lg:block lg:col-span-4 pl-2">
        <div class="sticky top-8 space-y-4">
             <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
                <h4 class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-4">
                    Kelengkapan
                </h4>
                <div class="space-y-4 relative">
                     <div class="absolute left-[7px] top-2 bottom-2 w-[1px] bg-slate-100"></div>
                     
                    {{-- Step 1 --}}
                    <div class="relative pl-6">
                        <div class="absolute left-0 top-0.5 w-4 h-4 rounded-full border flex items-center justify-center bg-white z-10"
                                :class="form.namaMapel.length > 2 ? 'border-emerald-500' : 'border-slate-300'">
                                <div class="w-1.5 h-1.5 rounded-full" :class="form.namaMapel.length > 2 ? 'bg-emerald-500' : 'bg-slate-200'"></div>
                        </div>
                        <h5 class="text-sm font-semibold text-slate-800">Nama Mapel</h5>
                        <p class="text-[11px] text-slate-500 mt-0.5" x-text="form.namaMapel || 'Wajib diisi'"></p>
                    </div>

                    {{-- Step 2 --}}
                    <div class="relative pl-6">
                        <div class="absolute left-0 top-0.5 w-4 h-4 rounded-full border flex items-center justify-center bg-white z-10"
                                :class="selectedGuru.length > 0 ? 'border-emerald-500' : 'border-slate-300'">
                                <div class="w-1.5 h-1.5 rounded-full" :class="selectedGuru.length > 0 ? 'bg-emerald-500' : 'bg-slate-200'"></div>
                        </div>
                        <h5 class="text-sm font-semibold text-slate-800">Guru Pengampu</h5>
                        <p class="text-[11px] text-slate-500 mt-0.5" x-text="selectedGuru.length > 0 ? selectedGuru.length + ' guru dipilih' : 'Opsional'"></p>
                    </div>
                </div>
            </div>

            <div class="bg-indigo-50 rounded-xl p-5 border border-indigo-100">
                <h4 class="text-xs font-bold text-indigo-800 uppercase tracking-widest mb-3 flex items-center gap-2">
                    <x-ui.icon name="info" size="14" /> Info
                </h4>
                 <p class="text-xs text-indigo-700 leading-relaxed">
                    Mata pelajaran akan terhubung ke kurikulum <strong>{{ $kurikulum?->kode ?? '-' }}</strong> dan kelompok <strong>{{ $kelompokOptions[$kelompok] ?? $kelompok }}</strong>.
                </p>
            </div>
        </div>
    </div>
</x-forms.card>

<script>
    function createMapelForm() {
        return {
            form: {
                kodeMapel: '{{ old('kode_mapel') }}',
                namaMapel: '{{ old('nama_mapel') }}',
            },
            selectedGuru: {!! json_encode(old('guru_ids', [])) !!},
            guruUtama: '{{ old('guru_utama_id', '') }}',
            
            get isValid() {
                return this.form.namaMapel.length > 2;
            },
            
            submitForm(e) {
                if (this.isValid) {
                    e.target.submit();
                }
            }
        }
    }
</script>
@endsection
