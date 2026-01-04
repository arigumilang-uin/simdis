@extends('layouts.app')

@section('title', 'Tambah Kelas')

@section('page-header')
    <x-page-header 
        title="Tambah Kelas" 
        subtitle="Buat rombongan belajar baru."
    />
@endsection

@section('content')
<div class="max-w-2xl">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Form Tambah Kelas</h3>
        </div>
        <div class="card-body">
            {{-- Jurusan mapping for auto-generate class name --}}
            @php
                $jurusanMap = [];
                foreach($jurusanList ?? [] as $j) {
                    $jurusanMap[$j->id] = $j->kode_jurusan ?? strtoupper(substr($j->nama_jurusan, 0, 3));
                }
            @endphp
            
            <form action="{{ route('kelas.store') }}" method="POST" class="space-y-6"
                  x-data="{ 
                      tingkat: '{{ old('tingkat', '') }}',
                      jurusanId: '{{ old('jurusan_id', '') }}',
                      konsentrasiId: '{{ old('konsentrasi_id', '') }}',
                      rombel: '{{ old('rombel', '1') }}',
                      createWali: false,
                      jurusanMap: {{ json_encode($jurusanMap) }},
                      konsentrasiList: [],
                      konsentrasiMap: {},
                      loadingKonsentrasi: false,
                      
                      getKodeJurusan() {
                          return this.jurusanMap[this.jurusanId] || '';
                      },
                      getKodeKonsentrasi() {
                          return this.konsentrasiMap[this.konsentrasiId] || '';
                      },
                      generateNamaKelas() {
                          if (!this.tingkat || !this.jurusanId) return '';
                          // Prioritize konsentrasi code if selected, otherwise use jurusan code
                          let kode = this.getKodeJurusan();
                          if (this.konsentrasiId && this.getKodeKonsentrasi()) {
                              kode = this.getKodeKonsentrasi();
                          }
                          const rombelNum = this.rombel || '1';
                          return this.tingkat + ' ' + kode + ' ' + rombelNum;
                      },
                      generateWaliUsername() {
                          if (!this.tingkat || !this.jurusanId) return '...';
                          const kode = this.getKodeJurusan().toLowerCase();
                          const tingkat = this.tingkat.toLowerCase();
                          const rombel = this.rombel || '1';
                          return kode + '_' + tingkat + '_' + rombel + '_wali';
                      },
                      async loadKonsentrasi() {
                          this.konsentrasiId = '';
                          this.konsentrasiList = [];
                          this.konsentrasiMap = {};
                          
                          if (!this.jurusanId) return;
                          
                          this.loadingKonsentrasi = true;
                          try {
                              const response = await fetch('{{ route('api.konsentrasi.by-jurusan') }}?jurusan_id=' + this.jurusanId);
                              const data = await response.json();
                              this.konsentrasiList = data;
                              // Build map for quick code lookup
                              data.forEach(k => {
                                  this.konsentrasiMap[k.id] = k.kode_konsentrasi || k.nama_konsentrasi.substring(0, 3).toUpperCase();
                              });
                          } catch (error) {
                              console.error('Failed to load konsentrasi:', error);
                          } finally {
                              this.loadingKonsentrasi = false;
                          }
                      }
                  }"
                  x-init="if(jurusanId) loadKonsentrasi()"
            >
                @csrf
                
                {{-- Tingkat Selection --}}
                <div class="form-group">
                    <label class="form-label form-label-required">Tingkat</label>
                    <div class="grid grid-cols-3 gap-3">
                        <label class="relative cursor-pointer">
                            <input type="radio" name="tingkat" value="X" x-model="tingkat" class="peer sr-only" {{ old('tingkat') == 'X' ? 'checked' : '' }}>
                            <div class="p-4 text-center border-2 rounded-xl transition-all peer-checked:border-blue-500 peer-checked:bg-blue-50 peer-checked:text-blue-700 hover:bg-gray-50">
                                <span class="text-2xl font-bold">X</span>
                                <p class="text-sm mt-1">Kelas 10</p>
                            </div>
                        </label>
                        <label class="relative cursor-pointer">
                            <input type="radio" name="tingkat" value="XI" x-model="tingkat" class="peer sr-only" {{ old('tingkat') == 'XI' ? 'checked' : '' }}>
                            <div class="p-4 text-center border-2 rounded-xl transition-all peer-checked:border-blue-500 peer-checked:bg-blue-50 peer-checked:text-blue-700 hover:bg-gray-50">
                                <span class="text-2xl font-bold">XI</span>
                                <p class="text-sm mt-1">Kelas 11</p>
                            </div>
                        </label>
                        <label class="relative cursor-pointer">
                            <input type="radio" name="tingkat" value="XII" x-model="tingkat" class="peer sr-only" {{ old('tingkat') == 'XII' ? 'checked' : '' }}>
                            <div class="p-4 text-center border-2 rounded-xl transition-all peer-checked:border-blue-500 peer-checked:bg-blue-50 peer-checked:text-blue-700 hover:bg-gray-50">
                                <span class="text-2xl font-bold">XII</span>
                                <p class="text-sm mt-1">Kelas 12</p>
                            </div>
                        </label>
                    </div>
                    <p class="form-help">Nama kelas akan digenerate otomatis.</p>
                    @error('tingkat')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>
                
                {{-- Jurusan Selection --}}
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
                
                {{-- Konsentrasi Selection --}}
                <div x-show="jurusanId" x-transition>
                    <x-forms.select 
                        name="konsentrasi_id" 
                        label="Konsentrasi Keahlian" 
                        x-model="konsentrasiId"
                        ::disabled="!jurusanId || konsentrasiList.length === 0"
                        placeholder="-- Pilih Konsentrasi (Opsional) --"
                    >
                        <template x-for="k in konsentrasiList" :key="k.id">
                            <option :value="k.id" x-text="k.nama_konsentrasi + (k.kode_konsentrasi ? ' (' + k.kode_konsentrasi + ')' : '')"></option>
                        </template>
                    </x-forms.select>
                    <p class="form-help" x-show="!jurusanId">Pilih jurusan terlebih dahulu untuk melihat konsentrasi.</p>
                    <p class="form-help" x-show="jurusanId && konsentrasiList.length === 0 && !loadingKonsentrasi">Tidak ada konsentrasi untuk jurusan ini. <a href="{{ route('konsentrasi.create') }}" class="text-blue-600 hover:underline">Tambah konsentrasi</a></p>
                    <p class="form-help text-blue-600" x-show="loadingKonsentrasi">Memuat konsentrasi...</p>
                </div>
                
                {{-- Rombel Number --}}
                <x-forms.select 
                    name="rombel" 
                    label="Nomor Rombel" 
                    required 
                    x-model="rombel"
                    help="Nomor urut rombongan belajar (1, 2, 3...)"
                >
                    @for($i = 1; $i <= 10; $i++)
                        <option value="{{ $i }}" {{ old('rombel', '1') == $i ? 'selected' : '' }}>{{ $i }}</option>
                    @endfor
                </x-forms.select>
                
                {{-- Preview Nama Kelas --}}
                <div class="p-4 bg-blue-50 rounded-xl border border-blue-100"
                     x-show="tingkat && jurusanId"
                     x-transition>
                    <label class="form-label text-blue-700">Nama Kelas (Otomatis)</label>
                    <div class="flex items-center gap-3">
                        <div class="flex-1 p-3 bg-white rounded-lg border border-blue-200">
                            <span class="text-xl font-bold text-blue-800" x-text="generateNamaKelas()"></span>
                        </div>
                        <x-ui.icon name="check-circle" size="24" class="text-blue-400" />
                    </div>
                    <input type="hidden" name="nama_kelas" :value="generateNamaKelas()">
                </div>

                <hr class="border-gray-100">
                
                {{-- Wali Kelas Selection --}}
                <x-forms.select 
                    name="wali_kelas_user_id" 
                    label="Wali Kelas (Opsional)" 
                    :options="$waliList"
                    optionValue="id"
                    optionLabel="username"
                    :selected="old('wali_kelas_user_id')"
                    placeholder="-- Pilih dari Guru yang Ada --"
                />
                
                {{-- Create Wali Option --}}
                <div class="p-4 bg-gray-50 rounded-xl border border-gray-200">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="create_wali" value="1" x-model="createWali"
                               class="w-5 h-5 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <div>
                            <span class="text-sm font-bold text-gray-700">Buat akun Wali Kelas baru secara otomatis</span>
                            <p class="text-xs text-gray-500">Centang ini jika guru belum terdaftar di sistem. Akun akan dibuatkan oleh sistem.</p>
                        </div>
                    </label>
                    
                    {{-- Preview Wali Account --}}
                    <div x-show="createWali && tingkat && jurusanId" x-transition class="mt-4">
                        <div class="p-4 bg-white rounded-lg border border-indigo-100">
                            <h5 class="text-xs font-bold text-indigo-500 uppercase tracking-wide mb-3">Preview Akun Baru</h5>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <span class="text-xs text-gray-400">Username</span>
                                    <div class="flex items-center gap-2 bg-gray-50 p-2 rounded border mt-1">
                                        <x-ui.icon name="user" size="14" class="text-gray-400" />
                                        <span class="font-mono font-bold text-gray-700 text-sm" x-text="generateWaliUsername()"></span>
                                    </div>
                                </div>
                                <div>
                                    <span class="text-xs text-gray-400">Password Awal</span>
                                    <div class="flex items-center gap-2 bg-gray-50 p-2 rounded border mt-1">
                                        <x-ui.icon name="lock" size="14" class="text-gray-400" />
                                        <span class="font-mono font-bold text-rose-500 text-sm">(Auto-generated)</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                {{-- Actions --}}
                <div class="flex items-center gap-3 pt-4 border-t border-gray-100">
                    <button type="submit" class="btn btn-primary" :disabled="!tingkat || !jurusanId">
                        <x-ui.icon name="save" size="18" />
                        <span>Simpan Data</span>
                    </button>
                    <a href="{{ route('kelas.index') }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
