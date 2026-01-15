@extends('layouts.app')

@section('title', 'Edit Mata Pelajaran')

@section('content')
<div class="space-y-6">
    {{-- Page Header --}}
    <x-page-header 
        title="Edit Mata Pelajaran" 
        subtitle="Perbarui data mata pelajaran: {{ $mataPelajaran->nama_mapel }}"
    />

    {{-- Info Card --}}
    <div class="bg-slate-50 border border-slate-200 rounded-lg p-4">
        <div class="flex items-center gap-3">
            <div class="flex-shrink-0">
                <x-ui.icon name="info" size="20" class="text-slate-600" />
            </div>
            <div>
                <p class="font-medium text-slate-800">
                    Kurikulum: <span class="font-bold">{{ $mataPelajaran->kurikulum?->kode ?? '-' }} - {{ $mataPelajaran->kurikulum?->nama ?? '-' }}</span>
                </p>
                <p class="text-sm text-slate-600">
                    Kelompok: <span class="font-bold">{{ $kelompokOptions[$mataPelajaran->kelompok] ?? $mataPelajaran->kelompok }}</span>
                </p>
            </div>
        </div>
    </div>

    {{-- Form Card --}}
    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.mata-pelajaran.update', $mataPelajaran->id) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Kode --}}
                    <div class="form-group">
                        <label for="kode_mapel" class="form-label">Kode Mapel</label>
                        <input type="text" 
                               name="kode_mapel" 
                               id="kode_mapel" 
                               value="{{ old('kode_mapel', $mataPelajaran->kode_mapel) }}"
                               class="form-input @error('kode_mapel') border-red-500 @enderror" 
                               placeholder="Contoh: MTK">
                        @error('kode_mapel')
                            <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Nama --}}
                    <div class="form-group">
                        <label for="nama_mapel" class="form-label">Nama Mata Pelajaran <span class="text-red-500">*</span></label>
                        <input type="text" 
                               name="nama_mapel" 
                               id="nama_mapel" 
                               value="{{ old('nama_mapel', $mataPelajaran->nama_mapel) }}"
                               class="form-input @error('nama_mapel') border-red-500 @enderror" 
                               placeholder="Contoh: Matematika"
                               required>
                        @error('nama_mapel')
                            <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Kelompok --}}
                <div class="form-group">
                    <label for="kelompok" class="form-label">Kelompok Mapel</label>
                    <select name="kelompok" id="kelompok" class="form-input @error('kelompok') border-red-500 @enderror">
                        @foreach($kelompokOptions as $kode => $label)
                            <option value="{{ $kode }}" {{ old('kelompok', $mataPelajaran->kelompok) == $kode ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @error('kelompok')
                        <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Guru Pengampu --}}
                @php
                    $oldGuruIds = old('guru_ids', $selectedGuruIds ?? []);
                    $oldGuruUtama = old('guru_utama_id', $guruUtamaId ?? '');
                @endphp
                <div class="form-group" x-data="{ 
                    selectedGuru: {{ json_encode(array_map('strval', $oldGuruIds)) }}, 
                    guruUtama: '{{ $oldGuruUtama }}' 
                }">
                    <label class="form-label">
                        Guru Pengampu
                        <span class="text-sm text-slate-500 font-normal">(pilih guru yang bisa mengajar mapel ini)</span>
                    </label>
                    
                    <div class="border border-slate-200 rounded-lg p-4 bg-slate-50 space-y-3 max-h-72 overflow-y-auto">
                        @forelse($guruList as $guru)
                            <label class="flex items-center gap-3 p-2 bg-white rounded-lg border border-slate-100 hover:border-emerald-200 transition cursor-pointer">
                                <input type="checkbox" 
                                       name="guru_ids[]" 
                                       value="{{ $guru->id }}"
                                       x-model="selectedGuru"
                                       class="form-checkbox text-emerald-600 rounded">
                                <div class="flex-1">
                                    <span class="font-medium text-slate-800">{{ $guru->username }}</span>
                                </div>
                                <template x-if="selectedGuru.includes('{{ $guru->id }}')">
                                    <label class="flex items-center gap-1.5 text-xs">
                                        <input type="radio" 
                                               name="guru_utama_id" 
                                               value="{{ $guru->id }}"
                                               x-model="guruUtama"
                                               class="form-radio text-amber-500">
                                        <span class="text-amber-600 font-medium">Utama</span>
                                    </label>
                                </template>
                            </label>
                        @empty
                            <p class="text-slate-500 text-sm text-center py-4">Belum ada data guru</p>
                        @endforelse
                    </div>
                    <p class="text-xs text-slate-500 mt-1">
                        💡 Centang guru yang bisa mengajar mapel ini. Pilih "Utama" untuk guru utama.
                    </p>
                    @error('guru_ids')
                        <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Deskripsi --}}
                <div class="form-group">
                    <label for="deskripsi" class="form-label">Deskripsi</label>
                    <textarea name="deskripsi" 
                              id="deskripsi" 
                              rows="3"
                              class="form-input @error('deskripsi') border-red-500 @enderror" 
                              placeholder="Deskripsi singkat tentang mata pelajaran ini (opsional)">{{ old('deskripsi', $mataPelajaran->deskripsi) }}</textarea>
                    @error('deskripsi')
                        <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Status --}}
                <div class="form-group">
                    <label class="flex items-center gap-2">
                        <input type="checkbox" 
                               name="is_active" 
                               value="1"
                               {{ old('is_active', $mataPelajaran->is_active) ? 'checked' : '' }}
                               class="form-checkbox">
                        <span class="text-sm text-slate-700">Mata pelajaran aktif</span>
                    </label>
                </div>

                {{-- Actions --}}
                <div class="flex items-center justify-end gap-3 pt-4 border-t">
                    <a href="{{ route('admin.mata-pelajaran.index', ['kurikulum_id' => $mataPelajaran->kurikulum_id, 'kelompok' => $mataPelajaran->kelompok]) }}" class="btn btn-secondary">
                        <x-ui.icon name="x" size="16" />
                        <span>Batal</span>
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <x-ui.icon name="save" size="16" />
                        <span>Simpan Perubahan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
