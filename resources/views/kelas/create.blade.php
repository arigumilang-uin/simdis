@extends('layouts.app')

@section('title', 'Tambah Kelas')

@section('page-header')
    <x-page-header 
        title="Tambah Kelas" 
        subtitle="Buat rombongan belajar baru dalam sistem."
        :backUrl="route('kelas.index')"
    />
@endsection

@section('content')
@php
    $konsentrasiApiUrl = route('api.konsentrasi.by-jurusan');
    
    // Build Jurusan Map for Alpine Preview
    $jurusanMap = [];
    foreach($jurusanList ?? [] as $j) {
        $jurusanMap[$j->id] = $j->kode_jurusan ?? strtoupper(substr($j->nama_jurusan, 0, 3));
    }
@endphp

<div class="form-page-container" 
    x-data="{
        tingkat: '{{ old('tingkat', '') }}',
        jurusanId: '{{ old('jurusan_id', '') }}',
        konsentrasiId: '{{ old('konsentrasi_id', '') }}',
        rombel: '{{ old('rombel', 'none') }}',
        jurusanMap: {{ json_encode($jurusanMap) }},
        createWali: false,
        
        // Dynamic konsentrasi
        konsentrasiList: [],
        loadingKonsentrasi: false,
        
        async loadKonsentrasi() {
            this.konsentrasiId = '';
            this.konsentrasiList = [];
            
            if (!this.jurusanId) return;
            
            this.loadingKonsentrasi = true;
            try {
                const response = await fetch('{{ $konsentrasiApiUrl }}?jurusan_id=' + this.jurusanId);
                this.konsentrasiList = await response.json();
            } catch (error) {
                console.error('Failed to load konsentrasi:', error);
            } finally {
                this.loadingKonsentrasi = false;
            }
        },
        
        generateNamaKelas() {
             if (!this.tingkat || !this.jurusanId) return '';
             
             // Get base code from map
             let kode = this.jurusanMap[this.jurusanId] || '';
             
             // Priority: Konsentrasi Code
             if (this.konsentrasiId && this.konsentrasiList.length > 0) {
                 const k = this.konsentrasiList.find(item => item.id == this.konsentrasiId);
                 if (k) {
                      kode = k.kode_konsentrasi || k.nama_konsentrasi.substring(0,3).toUpperCase();
                 }
             }
             
             const rombelSuffix = (this.rombel && this.rombel !== 'none') ? (' ' + this.rombel) : '';
             return this.tingkat + ' ' + kode + rombelSuffix;
        },
        
        init() {
            if (this.jurusanId) this.loadKonsentrasi();
        }
    }"
>
    <form action="{{ route('kelas.store') }}" method="POST" class="form-layout-wrapper">
        @csrf
        
        {{-- MAIN CONTENT (Left/Top) --}}
        <div class="form-main-card">
            <div class="form-header">
                <h3 class="form-title">Form Data Kelas</h3>
                <p class="form-subtitle">Isi detail rombongan belajar dengan lengkap.</p>
            </div>
            
            <div class="form-body">
                {{-- SECTION 1: Konfigurasi --}}
                <div class="form-section active">
                    <div class="form-section-title">
                        <x-ui.icon name="layout" size="18" class="text-primary-600" />
                        <span>Konfigurasi Kelas</span>
                    </div>
                
                    {{-- Tingkat --}}
                    <div class="form-group">
                        <label class="form-label form-label-required">Tingkat Kelas</label>
                        <div class="radio-card-group">
                            <label class="radio-card">
                                <input type="radio" name="tingkat" value="X" x-model="tingkat" {{ old('tingkat') == 'X' ? 'checked' : '' }}>
                                <div class="radio-card-content text-center cursor-pointer rounded-lg hover:border-primary-300 transition-colors">
                                    <span class="block text-2xl font-bold text-slate-700 mb-1">10</span>
                                    <span class="text-xs text-slate-500 font-medium uppercase tracking-wider">Kelas X</span>
                                </div>
                            </label>
                            <label class="radio-card">
                                <input type="radio" name="tingkat" value="XI" x-model="tingkat" {{ old('tingkat') == 'XI' ? 'checked' : '' }}>
                                <div class="radio-card-content text-center cursor-pointer rounded-lg hover:border-primary-300 transition-colors">
                                    <span class="block text-2xl font-bold text-slate-700 mb-1">11</span>
                                    <span class="text-xs text-slate-500 font-medium uppercase tracking-wider">Kelas XI</span>
                                </div>
                            </label>
                            <label class="radio-card">
                                <input type="radio" name="tingkat" value="XII" x-model="tingkat" {{ old('tingkat') == 'XII' ? 'checked' : '' }}>
                                <div class="radio-card-content text-center cursor-pointer rounded-lg hover:border-primary-300 transition-colors">
                                    <span class="block text-2xl font-bold text-slate-700 mb-1">12</span>
                                    <span class="text-xs text-slate-500 font-medium uppercase tracking-wider">Kelas XII</span>
                                </div>
                            </label>
                        </div>
                        @error('tingkat')
                            <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    {{-- Jurusan --}}
                    <div class="form-group">
                        <x-forms.select 
                            name="jurusan_id" 
                            label="Jurusan / Program Keahlian" 
                            required 
                            x-model="jurusanId"
                            @change="loadKonsentrasi()"
                            :options="$jurusanList"
                            optionValue="id"
                            optionLabel="nama_jurusan"
                            :selected="old('jurusan_id')"
                            placeholder="-- Pilih Jurusan --"
                        />
                    </div>
                    
                    {{-- Konsentrasi --}}
                    <div x-show="jurusanId" x-transition class="form-group">
                        <label for="konsentrasi_id" class="form-label">Konsentrasi Keahlian (Opsional)</label>
                        <div :class="{ 'opacity-50 pointer-events-none': loadingKonsentrasi }">
                            <select 
                                name="konsentrasi_id" 
                                id="konsentrasi_id"
                                class="form-input block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                                x-model="konsentrasiId"
                                :disabled="!jurusanId || konsentrasiList.length === 0"
                            >
                                <option value="">-- Tidak Spesifik --</option>
                                <template x-for="k in konsentrasiList" :key="k.id">
                                    <option :value="k.id" x-text="k.nama_konsentrasi + (k.kode_konsentrasi ? ' (' + k.kode_konsentrasi + ')' : '')"></option>
                                </template>
                            </select>
                        </div>
                        <p class="form-help-text" x-show="jurusanId && konsentrasiList.length === 0 && !loadingKonsentrasi">
                            Tidak ada data konsentrasi untuk jurusan ini.
                        </p>
                    </div>

                {{-- Rombel Number (Added Manual Input) --}}
                <div class="form-group">
                    <x-forms.select 
                        name="rombel" 
                        label="Nomor Rombel (Opsional)" 
                        x-model="rombel" 
                        :value="old('rombel')"
                        help="Pilih 'Tanpa Nomor' untuk format standar (misal: X TKJ)."
                    >
                        <option value="none">Tanpa Nomor</option>
                        @for($i = 1; $i <= 10; $i++)
                            <option value="{{ $i }}">{{ $i }}</option>
                        @endfor
                    </x-forms.select>
                </div>

                {{-- Preview Nama Kelas --}}
                <div class="p-4 bg-blue-50 rounded-xl border border-blue-100 mt-6 mb-6" 
                        x-show="tingkat && jurusanId" 
                        x-transition>
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-blue-100 rounded-lg text-blue-600">
                            <x-ui.icon name="layout" size="24" />
                        </div>
                        <div>
                            <h4 class="text-xs uppercase font-bold text-blue-500 mb-0.5">Preview Nama Kelas</h4>
                            <div class="text-2xl font-bold text-blue-800 tracking-tight" x-text="generateNamaKelas() || '...'"></div>
                        </div>
                    </div>
                </div>
                
                </div>

                {{-- SECTION 2: Wali Kelas --}}
                <div class="form-section">
                    <div class="form-section-title">
                        <x-ui.icon name="user" size="18" class="text-primary-600" />
                        <span>Wali Kelas</span>
                    </div>

                    <div class="form-group">
                        <x-forms.select 
                            name="wali_kelas_user_id" 
                            label="Pilih Guru Wali Kelas" 
                            :options="$waliList ?? []"
                            optionValue="id"
                            optionLabel="username"
                            placeholder="-- Pilih dari Daftar User --"
                            help="Pilih user yang akan menjadi wali kelas. Kosongkan jika ingin menambah nanti."
                        />
                    </div>
                </div>
            </div>

            {{-- FOOTER ACTIONS --}}
            <div class="form-actions-footer">
                <a href="{{ route('kelas.index') }}" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-primary px-6">
                    Simpan Kelas Baru
                </button>
            </div>
        </div>
        
        {{-- SIDEBAR LINK (Right) --}}
        <div class="form-sidebar">
            {{-- Info Card --}}
            <div class="sidebar-card">
                <div class="sidebar-header">
                    <x-ui.icon name="info" size="16" class="text-blue-500" />
                    <span class="sidebar-title">Informasi</span>
                </div>
                <div class="sidebar-body">
                    <ul class="info-list text-sm space-y-3">
                        <li>
                            <strong>Nama Kelas Otomatis</strong>
                            <p class="text-xs mt-1 text-slate-500">Sistem akan menamai kelas (misal: XI TKJ 1) secara otomatis.</p>
                        </li>
                        <li>
                            <strong>Nomor Urut</strong>
                            <p class="text-xs mt-1 text-slate-500">Jika sudah ada XI TKJ 1, sistem otomatis membuat XI TKJ 2.</p>
                        </li>
                    </ul>
                </div>
            </div>

            {{-- Help Card --}}
            <div class="sidebar-card">
                <div class="sidebar-header">
                    <x-ui.icon name="help-circle" size="16" class="text-emerald-500" />
                    <span class="sidebar-title">Bantuan</span>
                </div>
                <div class="sidebar-body">
                    <p class="mb-3 text-xs">Butuh menambahkan konsentrasi baru?</p>
                    <a href="{{ route('konsentrasi.index') }}" class="text-sm font-medium text-primary-600 hover:text-primary-700 inline-flex items-center gap-1">
                        Kelola Konsentrasi <x-ui.icon name="arrow-right" size="12" />
                    </a>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
