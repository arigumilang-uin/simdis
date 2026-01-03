@extends('layouts.app')

@section('title', 'Catat Pelanggaran')
@section('page-header', false)

@section('content')
<div x-data="violationWizard()" class="min-h-screen py-4 px-2 lg:px-4" x-cloak>
    
    {{-- Progress Stepper - More Compact --}}
    <div class="max-w-7xl mx-auto mb-4">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
            <div class="relative flex items-center justify-between">
                {{-- Progress Line --}}
                <div class="absolute left-0 right-0 top-1/2 h-0.5 bg-gray-200 -translate-y-1/2"></div>
                <div class="absolute left-0 top-1/2 h-0.5 bg-gradient-to-r from-blue-500 to-blue-600 -translate-y-1/2 transition-all duration-500" 
                     :style="'width: ' + ((step - 1) / 2 * 100) + '%'"></div>

                {{-- Step 1 --}}
                <div class="relative z-10 flex items-center gap-3 bg-white pr-4 cursor-pointer group" @click="step > 1 ? step = 1 : null">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold border-2 transition-all duration-300"
                         :class="step >= 1 ? 'bg-blue-600 border-blue-600 text-white shadow-lg shadow-blue-500/30 scale-110' : 'bg-white border-gray-300 text-gray-400'">
                        <span x-show="step > 1">✓</span>
                        <span x-show="step === 1">1</span>
                    </div>
                    <div class="hidden sm:block">
                        <p class="text-sm font-bold" :class="step >= 1 ? 'text-blue-600' : 'text-gray-400'">Pilih Data</p>
                        <p class="text-xs text-gray-400">Siswa & Pelanggaran</p>
                    </div>
                </div>
                
                {{-- Step 2 --}}
                <div class="relative z-10 flex items-center gap-3 bg-white px-4">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold border-2 transition-all duration-300"
                         :class="step >= 2 ? 'bg-blue-600 border-blue-600 text-white shadow-lg shadow-blue-500/30 scale-110' : 'bg-white border-gray-300 text-gray-400'">
                        <span x-show="step > 2">✓</span>
                        <span x-show="step <= 2">2</span>
                    </div>
                    <div class="hidden sm:block">
                        <p class="text-sm font-bold" :class="step >= 2 ? 'text-blue-600' : 'text-gray-400'">Detail</p>
                        <p class="text-xs text-gray-400">Waktu & Bukti</p>
                    </div>
                </div>
                
                {{-- Step 3 --}}
                <div class="relative z-10 flex items-center gap-3 bg-white pl-4">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold border-2 transition-all duration-300"
                         :class="step >= 3 ? 'bg-green-600 border-green-600 text-white shadow-lg shadow-green-500/30 scale-110' : 'bg-white border-gray-300 text-gray-400'">
                        <span x-show="step === 3">✓</span>
                        <span x-show="step < 3">3</span>
                    </div>
                    <div class="hidden sm:block">
                        <p class="text-sm font-bold" :class="step >= 3 ? 'text-green-600' : 'text-gray-400'">Selesai</p>
                        <p class="text-xs text-gray-400">Tersimpan</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Content Card --}}
    <div class="max-w-7xl mx-auto bg-white rounded-2xl shadow-xl overflow-hidden flex flex-col relative border border-gray-100">
        
        {{-- Loading Overlay --}}
        <div class="absolute inset-0 bg-white/90 backdrop-blur-sm z-50 flex items-center justify-center" x-show="isLoading" x-transition>
            <div class="flex flex-col items-center gap-4">
                <div class="relative">
                    <div class="w-16 h-16 border-4 border-blue-200 rounded-full"></div>
                    <div class="w-16 h-16 border-4 border-blue-600 rounded-full border-t-transparent animate-spin absolute inset-0"></div>
                </div>
                <p class="text-sm font-semibold text-blue-600">Menyimpan Data...</p>
            </div>
        </div>
        
        <form x-ref="form" action="{{ route('riwayat.store') }}" method="POST" enctype="multipart/form-data" class="flex-1 flex flex-col">
            @csrf
            <input type="hidden" name="guru_pencatat_user_id" value="{{ auth()->id() }}">

            {{-- Hidden Inputs --}}
            <template x-for="s in selectedSiswa" :key="'hi-s-'+s.id">
                <input type="hidden" name="siswa_id[]" :value="s.id">
            </template>
            <template x-for="p in selectedPelanggaran" :key="'hi-p-'+p.id">
                <input type="hidden" name="jenis_pelanggaran_id[]" :value="p.id">
            </template>

            {{-- ====== STEP 1: PILIH DATA ====== --}}
            <div x-show="step === 1" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="flex-1 flex flex-col">
                {{-- Summary Bar - Solid Background, Better Mobile Visibility --}}
                <div class="bg-slate-800 text-white px-3 sm:px-6 py-3">
                    <div class="flex flex-wrap items-center justify-between gap-2 sm:gap-3">
                        {{-- Stats --}}
                        <div class="flex items-center gap-2 sm:gap-4">
                            {{-- Siswa Counter --}}
                            <div class="flex items-center gap-2 bg-blue-600 rounded-lg px-2.5 sm:px-3 py-1.5 sm:py-2">
                                <x-ui.icon name="users" class="w-4 h-4 sm:w-5 sm:h-5 flex-shrink-0" />
                                <div class="flex items-center gap-1 sm:flex-col sm:items-start sm:gap-0">
                                    <span class="text-xs sm:text-[10px] text-blue-100 font-medium sm:uppercase sm:tracking-wide">Siswa</span>
                                    <span class="font-bold text-lg sm:text-xl leading-none text-white" x-text="selectedSiswa.length">0</span>
                                </div>
                            </div>
                            
                            {{-- Pelanggaran Counter --}}
                            <div class="flex items-center gap-2 bg-red-600 rounded-lg px-2.5 sm:px-3 py-1.5 sm:py-2">
                                <x-ui.icon name="alert-triangle" class="w-4 h-4 sm:w-5 sm:h-5 flex-shrink-0" />
                                <div class="flex items-center gap-1 sm:flex-col sm:items-start sm:gap-0">
                                    <span class="text-xs sm:text-[10px] text-red-100 font-medium sm:uppercase sm:tracking-wide">Pelanggaran</span>
                                    <span class="font-bold text-lg sm:text-xl leading-none text-white" x-text="selectedPelanggaran.length">0</span>
                                </div>
                            </div>
                            
                            {{-- Total Poin Counter --}}
                            <div class="flex items-center gap-2 bg-amber-500 rounded-lg px-2.5 sm:px-3 py-1.5 sm:py-2">
                                <x-ui.icon name="star" class="w-4 h-4 sm:w-5 sm:h-5 flex-shrink-0" />
                                <div class="flex items-center gap-1 sm:flex-col sm:items-start sm:gap-0">
                                    <span class="text-xs sm:text-[10px] text-amber-100 font-medium sm:uppercase sm:tracking-wide">Poin</span>
                                    <span class="font-bold text-lg sm:text-xl leading-none text-white" x-text="getTotalPoin()">0</span>
                                </div>
                            </div>
                        </div>
                        
                        {{-- Clear All Button --}}
                        <button type="button" 
                                @click="selectedSiswa = []; selectedPelanggaran = [];" 
                                x-show="selectedSiswa.length > 0 || selectedPelanggaran.length > 0"
                                x-transition
                                class="flex items-center gap-1.5 px-3 py-1.5 sm:py-2 bg-red-600 hover:bg-red-700 rounded-lg text-white transition-all text-xs sm:text-sm font-medium">
                            <x-ui.icon name="trash" class="w-4 h-4" />
                            <span class="hidden sm:inline">Hapus Semua</span>
                            <span class="sm:hidden">Reset</span>
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 flex-1 divide-y lg:divide-y-0 lg:divide-x divide-gray-100">
                    
                    {{-- ===== PANEL KIRI: SISWA ===== --}}
                    <div class="flex flex-col h-[500px] lg:h-[600px]">
                        {{-- Sticky Header with Search --}}
                        <div class="sticky top-0 z-20 bg-white border-b border-gray-100 p-4">
                            <div class="flex items-center justify-between mb-3">
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center">
                                        <x-ui.icon name="users" size="16" />
                                    </div>
                                    <h3 class="font-bold text-gray-800">Pilih Siswa</h3>
                                </div>
                                <span class="text-xs px-2 py-1 rounded-full font-medium"
                                      :class="selectedSiswa.length > 0 ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-500'"
                                      x-text="selectedSiswa.length + ' dipilih'"></span>
                            </div>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400 pointer-events-none">
                                    <x-ui.icon name="search" size="16" x-show="!loadingSiswa" />
                                    <x-ui.icon name="loader" size="16" class="animate-spin" x-show="loadingSiswa" />
                                </span>
                                <input type="text" x-model="searchSiswa" 
                                       class="w-full pl-9 pr-4 py-2.5 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all" 
                                       placeholder="Cari nama, NISN, atau kelas...">
                                <button type="button" x-show="searchSiswa" @click="searchSiswa = ''" 
                                        class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600">
                                    <x-ui.icon name="x" size="16" />
                                </button>
                            </div>
                        </div>
                        
                        {{-- Scrollable List --}}
                        <div class="flex-1 overflow-y-auto p-3 space-y-1.5 bg-gray-50/50">
                            {{-- Selected Items (Pinned) --}}
                            <template x-if="selectedSiswa.length > 0">
                                <div class="mb-3">
                                    <p class="text-[10px] font-bold text-blue-600 uppercase tracking-wider mb-1.5 px-1">Terpilih</p>
                                    <template x-for="s in selectedSiswa" :key="'sel-s-'+s.id">
                                        <div class="flex items-center gap-3 p-2.5 rounded-lg bg-blue-50 border border-blue-200 mb-1.5 group cursor-pointer hover:bg-blue-100 transition-colors"
                                             @click="toggleSiswa(s)">
                                            <div class="w-5 h-5 rounded bg-blue-600 flex items-center justify-center text-white flex-shrink-0">
                                                <x-ui.icon name="check" size="12" stroke-width="3" />
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="font-semibold text-sm text-gray-800 truncate" x-text="s.nama_siswa"></p>
                                                <p class="text-xs text-gray-500" x-text="s.nama_kelas + ' • ' + s.nisn"></p>
                                            </div>
                                            <span class="text-xs text-blue-600 opacity-0 group-hover:opacity-100 transition-opacity">Hapus</span>
                                        </div>
                                    </template>
                                </div>
                            </template>

                            {{-- Search Results --}}
                            <template x-if="!loadingSiswa && daftarSiswa.length > 0">
                                <div>
                                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5 px-1" x-text="searchSiswa ? 'Hasil Pencarian' : 'Semua Siswa'"></p>
                                    <template x-for="s in daftarSiswa.filter(item => !isSelectedSiswa(item.id))" :key="s.id">
                                        <div class="flex items-center gap-3 p-2.5 rounded-lg bg-white border border-gray-100 mb-1.5 cursor-pointer hover:border-blue-300 hover:bg-blue-50/50 transition-all group"
                                             @click="toggleSiswa(s)">
                                            <div class="w-5 h-5 rounded border-2 border-gray-300 flex items-center justify-center flex-shrink-0 group-hover:border-blue-400">
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="font-semibold text-sm text-gray-800 truncate" x-text="s.nama_siswa"></p>
                                                <p class="text-xs text-gray-500" x-text="s.nama_kelas + ' • ' + s.nisn"></p>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </template>

                            {{-- Loading State --}}
                            <template x-if="loadingSiswa">
                                <div class="space-y-2">
                                    <template x-for="i in 5" :key="'skel-s-'+i">
                                        <div class="flex items-center gap-3 p-2.5 rounded-lg bg-white border border-gray-100 animate-pulse">
                                            <div class="w-5 h-5 rounded bg-gray-200"></div>
                                            <div class="flex-1">
                                                <div class="h-4 bg-gray-200 rounded w-3/4 mb-1"></div>
                                                <div class="h-3 bg-gray-100 rounded w-1/2"></div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </template>

                            {{-- Empty State --}}
                            <div class="text-center py-8" x-show="!loadingSiswa && daftarSiswa.length === 0">
                                <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-3">
                                    <x-ui.icon name="search" size="24" class="text-gray-400" />
                                </div>
                                <p class="text-sm text-gray-500">Tidak ditemukan siswa</p>
                                <p class="text-xs text-gray-400">Coba kata kunci lain</p>
                            </div>
                        </div>
                    </div>
                    
                    {{-- ===== PANEL KANAN: PELANGGARAN ===== --}}
                    <div class="flex flex-col h-[500px] lg:h-[600px]">
                        {{-- Sticky Header with Search --}}
                        <div class="sticky top-0 z-20 bg-white border-b border-gray-100 p-4">
                            <div class="flex items-center justify-between mb-3">
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 rounded-lg bg-red-100 text-red-600 flex items-center justify-center">
                                        <x-ui.icon name="alert-triangle" size="16" />
                                    </div>
                                    <h3 class="font-bold text-gray-800">Pilih Pelanggaran</h3>
                                </div>
                                <span class="text-xs px-2 py-1 rounded-full font-medium"
                                      :class="selectedPelanggaran.length > 0 ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-500'"
                                      x-text="selectedPelanggaran.length + ' dipilih'"></span>
                            </div>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400 pointer-events-none">
                                    <x-ui.icon name="search" size="16" x-show="!loadingPelanggaran" />
                                    <x-ui.icon name="loader" size="16" class="animate-spin" x-show="loadingPelanggaran" />
                                </span>
                                <input type="text" x-model="searchPelanggaran" 
                                       class="w-full pl-9 pr-4 py-2.5 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-red-500/20 focus:border-red-500 transition-all" 
                                       placeholder="Cari jenis pelanggaran...">
                                <button type="button" x-show="searchPelanggaran" @click="searchPelanggaran = ''" 
                                        class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600">
                                    <x-ui.icon name="x" size="16" />
                                </button>
                            </div>
                        </div>
                        
                        {{-- Scrollable List --}}
                        <div class="flex-1 overflow-y-auto p-3 space-y-1.5 bg-gray-50/50">
                            {{-- Selected Items (Pinned) --}}
                            <template x-if="selectedPelanggaran.length > 0">
                                <div class="mb-3">
                                    <p class="text-[10px] font-bold text-red-600 uppercase tracking-wider mb-1.5 px-1">Terpilih</p>
                                    <template x-for="p in selectedPelanggaran" :key="'sel-p-'+p.id">
                                        <div class="flex items-center gap-3 p-2.5 rounded-lg bg-red-50 border border-red-200 mb-1.5 group cursor-pointer hover:bg-red-100 transition-colors"
                                             @click="togglePelanggaran(p)">
                                            <div class="w-5 h-5 rounded bg-red-600 flex items-center justify-center text-white flex-shrink-0">
                                                <x-ui.icon name="check" size="12" stroke-width="3" />
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="font-semibold text-sm text-gray-800 truncate" x-text="p.nama_pelanggaran"></p>
                                                <span class="text-[10px] px-1.5 py-0.5 rounded font-medium"
                                                      :class="getKategoriClass(p.kategori)"
                                                      x-text="p.kategori || 'Umum'"></span>
                                            </div>
                                            <span class="text-xs text-red-600 opacity-0 group-hover:opacity-100 transition-opacity">Hapus</span>
                                        </div>
                                    </template>
                                </div>
                            </template>

                            {{-- Search Results --}}
                            <template x-if="!loadingPelanggaran && daftarPelanggaran.length > 0">
                                <div>
                                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5 px-1" x-text="searchPelanggaran ? 'Hasil Pencarian' : 'Semua Pelanggaran'"></p>
                                    <template x-for="p in daftarPelanggaran.filter(item => !isSelectedPelanggaran(item.id))" :key="p.id">
                                        <div class="flex items-center gap-3 p-2.5 rounded-lg bg-white border border-gray-100 mb-1.5 cursor-pointer hover:border-red-300 hover:bg-red-50/50 transition-all group"
                                             @click="togglePelanggaran(p)">
                                            <div class="w-5 h-5 rounded border-2 border-gray-300 flex items-center justify-center flex-shrink-0 group-hover:border-red-400">
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="font-semibold text-sm text-gray-800 truncate" x-text="p.nama_pelanggaran"></p>
                                                <span class="text-[10px] px-1.5 py-0.5 rounded font-medium"
                                                      :class="getKategoriClass(p.kategori)"
                                                      x-text="p.kategori || 'Umum'"></span>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </template>

                            {{-- Loading State --}}
                            <template x-if="loadingPelanggaran">
                                <div class="space-y-2">
                                    <template x-for="i in 5" :key="'skel-p-'+i">
                                        <div class="flex items-center gap-3 p-2.5 rounded-lg bg-white border border-gray-100 animate-pulse">
                                            <div class="w-5 h-5 rounded bg-gray-200"></div>
                                            <div class="flex-1">
                                                <div class="h-4 bg-gray-200 rounded w-3/4 mb-1"></div>
                                                <div class="h-3 bg-gray-100 rounded w-1/3"></div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </template>

                            {{-- Empty State --}}
                            <div class="text-center py-8" x-show="!loadingPelanggaran && daftarPelanggaran.length === 0">
                                <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-3">
                                    <x-ui.icon name="search" size="24" class="text-gray-400" />
                                </div>
                                <p class="text-sm text-gray-500">Tidak ditemukan pelanggaran</p>
                                <p class="text-xs text-gray-400">Coba kata kunci lain</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                {{-- Footer Action --}}
                <div class="border-t border-gray-100 bg-white p-4 flex items-center justify-between">
                    <div class="text-sm text-gray-500">
                        <span x-show="selectedSiswa.length === 0 || selectedPelanggaran.length === 0">
                            Pilih minimal 1 siswa dan 1 pelanggaran untuk melanjutkan
                        </span>
                        <span x-show="selectedSiswa.length > 0 && selectedPelanggaran.length > 0" class="text-green-600 font-medium">
                            ✓ Siap melanjutkan
                        </span>
                    </div>
                    <button type="button" @click="nextStep()" 
                            class="px-6 py-2.5 bg-blue-600 text-white font-semibold rounded-xl shadow-lg shadow-blue-500/30 hover:bg-blue-700 transition-all disabled:opacity-50 disabled:cursor-not-allowed disabled:shadow-none flex items-center gap-2" 
                            :disabled="selectedSiswa.length === 0 || selectedPelanggaran.length === 0">
                        Lanjut ke Detail
                        <x-ui.icon name="arrow-right" size="18" />
                    </button>
                </div>
            </div>

            {{-- ====== STEP 2: DETAIL ====== --}}
            <div x-show="step === 2" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0" class="flex-1 flex flex-col p-4 sm:p-6 lg:p-8">
                <div class="max-w-2xl mx-auto w-full space-y-4 sm:space-y-6">
                    {{-- Header --}}
                    <div class="text-center mb-2">
                        <h2 class="text-xl font-bold text-gray-900">Detail Kejadian</h2>
                        <p class="text-sm text-gray-500">Lengkapi informasi waktu dan bukti pendukung</p>
                    </div>

                    {{-- Summary Card --}}
                    <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl p-4 border border-blue-100">
                        <div class="flex items-start gap-4">
                            <div class="w-10 h-10 rounded-lg bg-blue-600 text-white flex items-center justify-center flex-shrink-0">
                                <x-ui.icon name="file-text" size="20" />
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-bold text-blue-900 mb-2">Ringkasan Catatan</p>
                                <div class="grid grid-cols-3 gap-4 text-center">
                                    <div>
                                        <p class="text-2xl font-bold text-blue-700" x-text="selectedSiswa.length">0</p>
                                        <p class="text-xs text-blue-600">Siswa</p>
                                    </div>
                                    <div>
                                        <p class="text-2xl font-bold text-blue-700" x-text="selectedPelanggaran.length">0</p>
                                        <p class="text-xs text-blue-600">Pelanggaran</p>
                                    </div>
                                    <div>
                                        <p class="text-2xl font-bold text-amber-600" x-text="getTotalPoin()">0</p>
                                        <p class="text-xs text-amber-700">Total Poin</p>
                                    </div>
                                </div>
                                <p class="text-xs text-blue-600 mt-3 text-center border-t border-blue-200 pt-2">
                                    <span x-text="selectedSiswa.length * selectedPelanggaran.length"></span> catatan akan dibuat
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    {{-- Form Fields --}}
                    {{-- Form Fields --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <x-forms.date 
                            name="tanggal_kejadian" 
                            label="Tanggal Kejadian" 
                            x-model="formData.tanggal" 
                            required 
                        />
                        <x-forms.input 
                            type="time" 
                            name="waktu_kejadian" 
                            label="Waktu" 
                            x-model="formData.waktu" 
                        />
                    </div>
                    
                    <div>
                        <x-forms.textarea 
                            name="keterangan" 
                            label="Keterangan Tambahan" 
                            x-model="formData.keterangan" 
                            placeholder="Jelaskan detail kejadian (opsional)..." 
                        />
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Bukti Foto (Opsional)</label>
                        <div class="border-2 border-dashed rounded-xl p-6 text-center cursor-pointer transition-all"
                             :class="fileName ? 'border-blue-400 bg-blue-50' : 'border-gray-200 hover:border-gray-300 hover:bg-gray-50'">
                            <input type="file" name="bukti_foto" accept="image/*" class="hidden" id="bukti_foto" @change="handleFileUpload">
                            <label for="bukti_foto" class="cursor-pointer">
                                <div x-show="!fileName" class="flex flex-col items-center gap-2 text-gray-400">
                                    <x-ui.icon name="image" size="32" />
                                    <span class="text-sm">Klik untuk upload foto</span>
                                </div>
                                <div x-show="fileName" class="flex items-center justify-center gap-2 text-blue-600">
                                    <x-ui.icon name="file" size="20" />
                                    <span class="font-medium text-sm" x-text="fileName"></span>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>
                
                {{-- Footer Actions --}}
                <div class="mt-auto pt-6 border-t border-gray-100 flex items-center justify-between">
                    <button type="button" @click="step = 1" class="px-5 py-2.5 text-gray-600 font-medium rounded-xl hover:bg-gray-100 transition-colors flex items-center gap-2">
                        <x-ui.icon name="arrow-left" size="18" />
                        Kembali
                    </button>
                    <button type="button" @click="submitData()" 
                            class="px-8 py-2.5 bg-green-600 text-white font-semibold rounded-xl shadow-lg shadow-green-500/30 hover:bg-green-700 transition-all flex items-center gap-2">
                        <x-ui.icon name="save" size="18" />
                        Simpan Data
                    </button>
                </div>
            </div>

            {{-- ====== STEP 3: SUCCESS ====== --}}
            <div x-show="step === 3" x-transition:enter="transition ease-out duration-500" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" class="flex-1 flex flex-col items-center justify-center p-4 sm:p-6 lg:p-8 text-center">
                <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center text-green-600 mb-6 animate-bounce-slow">
                    <x-ui.icon name="check" size="40" stroke-width="3" />
                </div>
                <h2 class="text-2xl font-bold text-gray-900 mb-2">Berhasil Tersimpan!</h2>
                <p class="text-gray-500 mb-6" x-text="successMessage || 'Data pelanggaran telah dicatat.'"></p>

                {{-- Result Table --}}
                <div class="w-full max-w-lg bg-gray-50 rounded-xl border border-gray-200 overflow-hidden mb-6 text-left">
                    <div class="p-3 border-b border-gray-200 bg-gray-100 flex justify-between items-center">
                        <span class="font-semibold text-gray-700 text-sm">Data Tersimpan</span>
                        <span class="text-xs px-2 py-1 rounded-full bg-green-100 text-green-700 font-medium" x-text="savedData.length + ' item'"></span>
                    </div>
                    <div class="max-h-48 overflow-y-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-white text-gray-500 border-b sticky top-0">
                                <tr>
                                    <th class="px-4 py-2 text-left font-medium">Siswa</th>
                                    <th class="px-4 py-2 text-left font-medium">Pelanggaran</th>
                                    <th class="px-4 py-2 text-right font-medium">Poin</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <template x-for="(item, index) in savedData" :key="index">
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-2 text-gray-800 font-medium" x-text="item.siswa_nama"></td>
                                        <td class="px-4 py-2 text-gray-600" x-text="item.pelanggaran_nama"></td>
                                        <td class="px-4 py-2 text-right text-red-600 font-bold" x-text="'+' + item.poin"></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="flex gap-3">
                    <a href="{{ route('dashboard') }}" class="px-5 py-2.5 border border-gray-200 text-gray-600 font-medium rounded-xl hover:bg-gray-50 transition-colors">
                        Ke Dashboard
                    </a>
                    <button type="button" @click="resetWizard()" class="px-6 py-2.5 bg-blue-600 text-white font-semibold rounded-xl shadow-lg shadow-blue-500/30 hover:bg-blue-700 transition-all">
                        Catat Lagi
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function violationWizard() {
    return {
        step: 1,
        isLoading: false,
        
        searchSiswa: '',
        searchPelanggaran: '',
        daftarSiswa: [],
        daftarPelanggaran: [],
        loadingSiswa: false,
        loadingPelanggaran: false,
        
        selectedSiswa: [],
        selectedPelanggaran: [],

        savedData: [],
        successMessage: '',
        
        fileName: '',
        formData: {
            tanggal: '{{ date("Y-m-d") }}',
            waktu: '{{ date("H:i") }}',
            keterangan: ''
        },
        
        tSiswa: null,
        tPelanggaran: null,

        init() {
            this.fetchSiswa();
            this.fetchPelanggaran();

            this.$watch('searchSiswa', (val) => {
                clearTimeout(this.tSiswa);
                this.tSiswa = setTimeout(() => this.fetchSiswa(val), 400);
            });
            
            this.$watch('searchPelanggaran', (val) => {
                clearTimeout(this.tPelanggaran);
                this.tPelanggaran = setTimeout(() => this.fetchPelanggaran(val), 400);
            });
        },

        async fetchSiswa(q = '') {
            this.loadingSiswa = true;
            try {
                const res = await fetch(`{{ route('riwayat.ajax.siswa') }}?q=${encodeURIComponent(q)}`);
                if(res.ok) this.daftarSiswa = await res.json();
            } catch(e) { console.error('Error fetching siswa', e); }
            this.loadingSiswa = false;
        },
        
        async fetchPelanggaran(q = '') {
            this.loadingPelanggaran = true;
            try {
                const res = await fetch(`{{ route('riwayat.ajax.pelanggaran') }}?q=${encodeURIComponent(q)}`);
                if(res.ok) this.daftarPelanggaran = await res.json();
            } catch(e) { console.error('Error fetching pelanggaran', e); }
            this.loadingPelanggaran = false;
        },

        // Helpers
        isSelectedSiswa(id) { return this.selectedSiswa.some(s => s.id === id); },
        isSelectedPelanggaran(id) { return this.selectedPelanggaran.some(p => p.id === id); },
        
        toggleSiswa(s) {
            const idx = this.selectedSiswa.findIndex(item => item.id === s.id);
            if (idx > -1) { this.selectedSiswa.splice(idx, 1); } 
            else { this.selectedSiswa.push(s); }
        },
        
        togglePelanggaran(p) {
            const idx = this.selectedPelanggaran.findIndex(item => item.id === p.id);
            if (idx > -1) { this.selectedPelanggaran.splice(idx, 1); } 
            else { this.selectedPelanggaran.push(p); }
        },

        getTotalPoin() {
            return this.selectedPelanggaran.reduce((sum, p) => sum + (parseInt(p.poin) || 0), 0) * this.selectedSiswa.length;
        },

        getKategoriClass(kategori) {
            if (!kategori) return 'bg-gray-100 text-gray-600';
            const k = kategori.toLowerCase();
            if (k.includes('ringan')) return 'bg-green-100 text-green-700';
            if (k.includes('sedang')) return 'bg-yellow-100 text-yellow-700';
            if (k.includes('berat')) return 'bg-red-100 text-red-700';
            return 'bg-gray-100 text-gray-600';
        },

        clearAll() {
            if(confirm('Hapus semua pilihan?')) {
                this.selectedSiswa = [];
                this.selectedPelanggaran = [];
            }
        },

        handleFileUpload(event) {
            const file = event.target.files[0];
            if (file) this.fileName = file.name;
        },

        nextStep() {
            if (this.step === 1 && this.selectedSiswa.length > 0 && this.selectedPelanggaran.length > 0) {
                this.step = 2;
            }
        },

        async submitData() {
            this.isLoading = true;
            const form = this.$refs.form;
            const data = new FormData(form);
            
            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    body: data,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });

                const result = await response.json();

                if (response.ok) {
                    this.successMessage = result.message;
                    this.savedData = result.data;
                    this.step = 3;
                } else {
                    alert(result.message || 'Gagal menyimpan data.');
                }
            } catch (error) {
                console.error(error);
                alert('Terjadi kesalahan server.');
            } finally {
                this.isLoading = false;
            }
        },

        resetWizard() {
            this.step = 1;
            this.selectedSiswa = [];
            this.selectedPelanggaran = [];
            this.fileName = '';
            this.successMessage = '';
            this.savedData = [];
            this.formData.keterangan = '';
            this.searchSiswa = '';
            this.searchPelanggaran = '';
            this.fetchSiswa(); 
            this.fetchPelanggaran();
        }
    }
}
</script>

<style>
[x-cloak] { display: none !important; }
.animate-bounce-slow { animation: bounce 2s infinite; }
</style>
@endsection
