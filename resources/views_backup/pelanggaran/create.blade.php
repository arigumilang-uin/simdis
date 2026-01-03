@extends('layouts.app')

@section('content')

<!-- Tailwind Setup -->
<script src="https://cdn.tailwindcss.com"></script>
<script>
    tailwind.config = {
        theme: {
            extend: {
                colors: { primary: '#4f46e5', slate: { 800: '#1e293b', 900: '#0f172a' } },
                screens: { 'xs': '375px' }
            }
        },
        corePlugins: { preflight: false } // Avoid conflict with Bootstrap
    }
</script>
<style>
    /* Custom Scrollbar for lists */
    .custom-scrollbar::-webkit-scrollbar { width: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: #f1f5f9; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    
    /* Helpers for JS selectors */
    .selected { background-color: #eff6ff !important; border-color: #3b82f6 !important; }
    /* Checkbox click area fix */
    .checkbox-wrapper { pointer-events: auto; }
</style>

<div class="page-wrap bg-slate-50 min-h-screen p-6 font-sans">
    
    <!-- Toast/Alert Area (Maintains old logic keys) -->
    @if(session('success'))
        <div class="mb-6 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl flex items-center gap-3 shadow-sm">
            <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <div class="flex-1 text-sm font-medium">{{ session('success') }}</div>
            <button type="button" class="text-emerald-400 hover:text-emerald-600" onclick="this.parentElement.remove()">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
    @endif
    @if($errors->any())
        <div class="mb-6 bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 rounded-xl shadow-sm">
            <div class="flex items-center gap-2 font-bold mb-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                Terjadi Kesalahan
            </div>
            <ul class="list-disc list-inside text-sm space-y-1 ml-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Header -->
    <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4 mb-8">
        <div class="relative group">
    <div class="flex items-center gap-5">
        <div class="relative flex-shrink-0">
            <div class="absolute -inset-1 bg-indigo-500 rounded-2xl blur opacity-20 group-hover:opacity-40 transition duration-300"></div>
            <div class="relative bg-white border border-slate-200 p-3.5 rounded-2xl shadow-sm text-indigo-600">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
            </div>
        </div>

        <div>
            <div class="flex items-center gap-2">
                <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight m-0">
                    Input Pelanggaran
                </h1>
                <span class="bg-emerald-100 text-emerald-700 text-[10px] font-bold px-2 py-0.5 rounded-full border border-emerald-200 uppercase tracking-wider">
                    Sistem Poin
                </span>
            </div>
            <p class="text-slate-500 text-sm font-medium mt-0.5">
                Manajemen kedisiplinan siswa melalui pencatatan poin pelanggaran secara real-time.
            </p>
        </div>
    </div>
</div>
        
        @php
            $role = auth()->user()->effectiveRoleName() ?? auth()->user()->role?->nama_role;
            $backRoute = match($role) {
                'Wali Kelas' => route('dashboard.walikelas'),
                'Kaprodi' => route('dashboard.kaprodi'),
                'Kepala Sekolah' => route('dashboard.kepsek'),
                default => route('dashboard.admin'),
            };
        @endphp
        <a href="{{ $backRoute }}" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-slate-200 text-slate-600 text-sm font-bold rounded-xl hover:bg-slate-50 hover:text-slate-800 transition-all shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Kembali ke Dashboard
        </a>
    </div>

    <!-- Main Form -->
    <form action="{{ route('riwayat.store') }}" method="POST" enctype="multipart/form-data" id="formPelanggaran" class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        @csrf
        
        <!-- Kolom Kiri: Siswa -->
        <div class="lg:col-span-5 flex flex-col gap-0">
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden flex flex-col h-[600px]">
        <div class="px-6 py-3 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
            <h3 class="text-sm font-bold text-slate-700 uppercase tracking-wider flex items-center gap-2 m-0">
                <span class="w-6 h-6 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-xs">1</span>
                Pilih Siswa
            </h3>
            <span class="bg-blue-50 text-blue-700 text-[10px] font-black px-3 py-1 rounded-full border border-blue-100 uppercase" id="countSiswa">{{ count($daftarSiswa) }} Siswa</span>
        </div>
        
        <div class="px-4 pt-2 pb-4 flex-1 flex flex-col gap-2 min-h-0">
            <div class="grid grid-cols-3 gap-2">
                <select id="filterTingkat" class="w-full bg-slate-50 border border-slate-200 text-[11px] font-bold uppercase tracking-tighter rounded-lg p-2 focus:ring-2 focus:ring-blue-500 outline-none cursor-pointer">
                    <option value="">Tingkat</option>
                    <option value="X">Kelas X</option>
                    <option value="XI">Kelas XI</option>
                    <option value="XII">Kelas XII</option>
                </select>
                <select id="filterJurusan" class="w-full bg-slate-50 border border-slate-200 text-[11px] font-bold uppercase tracking-tighter rounded-lg p-2 focus:ring-2 focus:ring-blue-500 outline-none cursor-pointer">
                    <option value="">Jurusan</option>
                     @foreach($jurusan as $j) <option value="{{ $j->id }}">{{ $j->nama_jurusan }}</option> @endforeach
                </select>
                <select id="filterKelas" class="w-full bg-slate-50 border border-slate-200 text-[11px] font-bold uppercase tracking-tighter rounded-lg p-2 focus:ring-2 focus:ring-blue-500 outline-none cursor-pointer">
                    <option value="">Kelas</option>
                     @foreach($kelas as $k) <option value="{{ $k->id }}" data-jurusan="{{ $k->jurusan_id }}">{{ $k->nama_kelas }}</option> @endforeach
                </select>
            </div>
            
            <div class="relative">
                <input type="text" id="searchSiswa" class="w-full pl-9 pr-10 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 outline-none transition-all placeholder:text-slate-400 font-medium" placeholder="Cari nama atau NISN...">
                <div class="absolute left-3 top-2.5 text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <button type="button" onclick="resetFilters()" class="absolute right-2 top-1.5 p-1.5 bg-slate-100 text-slate-500 rounded-lg hover:bg-rose-100 hover:text-rose-600 transition-all border-none cursor-pointer shadow-sm active:scale-90" title="Reset Filter">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <div class="custom-scrollbar overflow-y-auto flex-1 pr-1 space-y-1.5" id="studentListContainer">
                 @foreach($daftarSiswa as $siswa)
                    @php
                        $tingkat = $siswa->tingkat ?? explode(' ', $siswa->nama_kelas ?? '')[0] ?? '';
                        $jurusanId = $siswa->jurusan_id ?? '';
                        $searchText = strtolower($siswa->nama_siswa . ' ' . $siswa->nisn);
                        $initial = strtoupper(substr($siswa->nama_siswa, 0, 1));
                    @endphp
                    
                    <div class="student-item group flex items-center gap-3 p-2.5 rounded-xl border border-transparent hover:bg-white hover:border-slate-200 hover:shadow-sm cursor-pointer transition-all active:scale-[0.98]"
                         data-tingkat="{{ $tingkat }}"
                         data-jurusan="{{ $jurusanId }}"
                         data-kelas="{{ $siswa->kelas_id }}"
                         data-search="{{ $searchText }}"
                         onclick="selectStudent(this)">
                        
                        <div class="w-9 h-9 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 text-white flex items-center justify-center font-bold text-xs shadow-sm flex-shrink-0">
                            {{ $initial }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="font-bold font-weight-bold text-slate-800 text-sm truncate">{{ $siswa->nama_siswa }}</div>
                            <div class="flex items-center gap-2 mt-0.5">
                                <span class="text-[9px] font-black uppercase tracking-tighter bg-slate-100 text-slate-500 px-1.5 py-0.5 rounded border border-slate-200">{{ $siswa->nama_kelas ?? '-' }}</span>
                                <span class="text-[10px] font-mono text-slate-400 font-medium">#{{ $siswa->nisn }}</span>
                            </div>
                        </div>
                        <input type="checkbox" name="siswa_id[]" value="{{ $siswa->id }}" class="siswa-checkbox w-5 h-5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer">
                    </div>
                @endforeach
                
                <div id="noResultMsg" class="hidden flex flex-col items-center justify-center py-10 text-slate-400">
                    <svg class="w-8 h-8 opacity-30 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    <p class="text-xs font-bold uppercase tracking-widest">Siswa tidak ditemukan</p>
                </div>
            </div>

             @error('siswa_id') 
             <div class="bg-rose-50 border border-rose-100 text-rose-600 text-[10px] p-2.5 rounded-lg font-bold flex items-center gap-2">
                <i class="fas fa-exclamation-triangle"></i> {{ $message }}
             </div> 
             @enderror
        </div>
    </div>
</div>

        <div class="lg:col-span-7 flex flex-col gap-0 animate-fade-in">
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden flex flex-col h-full">
        
        <div class="px-6 py-3 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
            <h3 class="text-sm font-bold text-slate-700 uppercase tracking-wider flex items-center gap-2 m-0">
                <span class="w-6 h-6 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center text-xs font-bold">2</span>
                Data Pelanggaran
            </h3>
            <span class="bg-rose-50 text-rose-700 text-[10px] font-black px-3 py-1 rounded-full border border-rose-100 uppercase italic">Input Detail Kejadian</span>
        </div>

        <div class="px-6 pt-2 pb-6 flex-1 flex flex-col gap-3">
            
            <div class="flex flex-col gap-2.5">
    
    <div class="filter-pills flex flex-wrap gap-2" data-toggle="buttons">
        {{-- Button Semua --}}
        <label class="btn active group cursor-pointer px-4 py-2 rounded-xl border border-slate-200 bg-white transition-all hover:bg-slate-50 [&.active]:bg-slate-900 [&.active]:border-slate-900 shadow-sm flex-1 min-w-[70px] text-center" onclick="setFilterTopic('all', this)">
            <input type="radio" class="hidden" checked>
            <span class="text-[10px] font-black uppercase tracking-widest text-slate-500 group-[.active]:text-white transition-colors">Semua</span>
        </label>

        @php $topics = [
            ['id' => 'atribut', 'label' => 'Atribut'], 
            ['id' => 'kehadiran', 'label' => 'Absensi'], 
            ['id' => 'kerapian', 'label' => 'Kerapian'], 
            ['id' => 'ibadah', 'label' => 'Ibadah']
        ]; @endphp

        @foreach($topics as $topic)
        <label class="btn group cursor-pointer px-4 py-2 rounded-xl border border-slate-200 bg-white transition-all hover:border-blue-400 hover:bg-blue-50/50 [&.active]:bg-blue-600 [&.active]:border-blue-600 shadow-sm flex-1 min-w-[80px] text-center" onclick="setFilterTopic('{{ $topic['id'] }}', this)">
            <input type="radio" class="hidden">
            <span class="text-[10px] font-black uppercase tracking-widest text-slate-400 group-[.active]:text-white transition-colors">{{ $topic['label'] }}</span>
        </label>
        @endforeach

        {{-- Button Berat --}}
        <label class="btn group cursor-pointer px-4 py-2 rounded-xl border border-rose-200 bg-rose-50/30 transition-all hover:bg-rose-100/50 [&.active]:bg-rose-600 [&.active]:border-rose-600 shadow-sm flex-1 min-w-[80px] text-center" onclick="setFilterTopic('berat', this)">
            <input type="radio" class="hidden">
            <span class="text-[10px] font-black uppercase tracking-widest text-rose-500 group-[.active]:text-white transition-colors">BERAT</span>
        </label>
    </div>

    <div class="relative group">
        <div class="absolute left-3.5 top-1/2 -translate-y-1/2 text-rose-400 pointer-events-none group-focus-within:text-rose-600 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
        </div>
        <input type="text" id="searchPelanggaran" 
            class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:ring-4 focus:ring-rose-500/10 focus:border-rose-500 outline-none transition-all placeholder:text-slate-400" 
            placeholder="Cari masalah (contoh: Bolos, Rokok, Telat)...">
    </div>

</div>

<style>
    /* Menyamakan tinggi Select di kiri dengan Pills di kanan */
    .filter-pills .btn {
        display: flex;
        align-items: center;
        justify-content: center;
        height: 38px; /* Menyamakan dengan tinggi input search */
        border-width: 1px;
    }
    
    /* Animasi Klik */
    .filter-pills .btn:active {
        transform: scale(0.95);
    }
</style>

            <div class="custom-scrollbar overflow-y-auto h-64 border border-slate-100 rounded-2xl p-1.5 bg-slate-50/50 shadow-inner">
    @foreach($daftarPelanggaran as $jp)
        @php
            $kategori = $jp->kategoriPelanggaran->nama_kategori;
            $isBerat = str_contains(strtolower($kategori), 'berat');
        @endphp
        
        <div class="violation-item group flex items-center gap-4 p-3 mb-1.5 rounded-xl bg-white border border-transparent hover:border-rose-200 hover:shadow-md hover:shadow-rose-100/50 cursor-pointer transition-all active:scale-[0.98]" 
             data-nama="{{ strtolower($jp->nama_pelanggaran) }}"
             data-kategori="{{ strtolower($kategori) }}"
             data-keywords="{{ strtolower($jp->keywords ?? '') }}"
             onclick="selectViolation(this)">
            
            <div class="w-1 h-8 rounded-full {{ $isBerat ? 'bg-rose-500 shadow-[0_0_8px_rgba(244,63,94,0.4)]' : 'bg-blue-400' }} opacity-40 group-hover:opacity-100 transition-opacity"></div>
            
            <div class="flex-1 min-w-0">
                <div class="font-bold font-weight-bold text-slate-700 text-sm truncate group-hover:text-rose-600 transition-colors">
                    {{ $jp->nama_pelanggaran }}
                </div>
                <div class="flex items-center gap-2 mt-0.5">
                    <span class="text-[8px] font-black uppercase tracking-wider px-1.5 py-0.5 rounded {{ $isBerat ? 'bg-rose-50 text-rose-600 border border-rose-100' : 'bg-slate-100 text-slate-400 border border-slate-200' }}">
                        {{ $kategori }}
                    </span>
                </div>
            </div>
            
            <div class="flex items-center gap-3">
                <div class="text-right flex flex-col items-end">
                    <span class="text-xs font-black {{ $isBerat ? 'text-rose-600' : 'text-blue-600' }} leading-none">
                        {{ $jp->getDisplayPoin() }}
                    </span>
                    <span class="text-[8px] font-bold text-slate-400 uppercase tracking-tighter">Poin</span>
                </div>

                <div class="relative flex items-center justify-center">
                    <input type="checkbox" name="jenis_pelanggaran_id[]" value="{{ $jp->id }}" 
                           class="pelanggaran-checkbox w-5 h-5 rounded-lg border-slate-300 text-rose-600 focus:ring-4 focus:ring-rose-500/10 cursor-pointer transition-all group-hover:border-rose-400">
                </div>
            </div>
        </div>
    @endforeach

    <div id="noViolationMsg" class="hidden flex flex-col items-center justify-center py-14 text-center">
        <div class="w-12 h-12 flex items-center justify-center text-slate-300 mb-3">
        </div>
        <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 m-0">Pelanggaran tidak ditemukan</p>
        <p class="text-[9px] text-slate-400 mt-1 italic">Coba kata kunci lain atau cek filter</p>
    </div>
</div>

<style>
    /* Styling Scrollbar agar Tipis & Elegan */
    .custom-scrollbar::-webkit-scrollbar {
        width: 4px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: transparent;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #e2e8f0;
        border-radius: 10px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: #cbd5e1;
    }

    /* Logic hover shadow spesifik rose untuk pelanggaran */
    .violation-item:hover {
        background-color: #fff;
    }
</style>

            @error('jenis_pelanggaran_id') 
                <div class="text-rose-500 text-[10px] font-bold flex items-center gap-1"><i class="fas fa-exclamation-triangle"></i> {{ $message }}</div> 
            @enderror

            <div class="bg-slate-50 rounded-2xl p-4 border border-slate-100 shadow-inner">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Waktu Kejadian</label>
                        <div class="flex gap-2">
                            <input type="date" name="tanggal_kejadian" class="flex-1 bg-white border border-slate-200 text-slate-700 text-xs font-bold rounded-xl p-2 focus:ring-2 focus:ring-blue-500 outline-none" value="{{ date('Y-m-d') }}" required>
                            <input type="time" name="jam_kejadian" class="w-24 bg-white border border-slate-200 text-slate-700 text-xs font-bold rounded-xl p-2 focus:ring-2 focus:ring-blue-500 outline-none" value="{{ old('jam_kejadian', date('H:i')) }}">
                        </div>
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Bukti Foto</label>
                        <input type="file" name="bukti_foto" id="customFile" accept="image/*" required
                               class="block w-full text-[10px] text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-[10px] file:font-black file:uppercase file:bg-blue-600 file:text-white hover:file:bg-blue-700 cursor-pointer transition-all">
                    </div>
                </div>
                
                <div class="mb-4">
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Keterangan / Kronologi</label>
                    <textarea name="keterangan" class="w-full bg-white border border-slate-200 text-slate-700 text-xs font-medium rounded-xl p-3 focus:ring-2 focus:ring-blue-500 outline-none resize-none" rows="2" placeholder="Tulis rincian kejadian..."></textarea>
                </div>
                
                <div class="grid grid-cols-2 gap-3">
                    <button type="button" id="btnPreview" class="flex justify-center items-center gap-2 py-2.5 bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 text-[10px] font-black uppercase tracking-widest rounded-xl transition-all active:scale-95 shadow-sm">
                        <i class="fas fa-eye opacity-50"></i> Preview
                    </button>
                    <button type="submit" id="btnSubmitPreview" class="flex justify-center items-center gap-2 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-[10px] font-black uppercase tracking-widest rounded-xl transition-all active:scale-95 shadow-lg shadow-indigo-100">
                        <i class="fas fa-save opacity-50"></i> Simpan Data
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="previewModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content rounded-3xl shadow-2xl border-0 overflow-hidden">
            <div class="modal-header bg-slate-900 text-white border-0 p-5">
                <h5 class="modal-title text-sm font-black uppercase tracking-[0.2em] flex items-center gap-3">
                    <i class="fas fa-chart-line text-blue-400"></i> Preview Kalkulasi
                </h5>
                <button type="button" class="close text-white opacity-50 hover:opacity-100 transition-all border-none bg-transparent cursor-pointer text-2xl" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body bg-slate-50 p-0" id="previewModalContent"></div>
        </div>
    </div>
</div>

<div class="modal fade" id="confirmModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content rounded-3xl shadow-2xl border-0 overflow-hidden">
            <div class="modal-header bg-white border-b border-slate-100 p-6">
                <h5 class="modal-title text-sm font-black uppercase tracking-widest text-slate-800">Konfirmasi Laporan</h5>
                <button type="button" class="close text-slate-300 hover:text-rose-500 border-none bg-transparent cursor-pointer text-2xl" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body p-6 bg-white">
                <div class="space-y-4">
                    <div class="flex flex-col gap-1">
                        <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Subjek Siswa</span>
                        <div id="confirmStudents" class="text-xs font-bold text-slate-800 bg-slate-50 p-2.5 rounded-xl border border-slate-100"></div>
                    </div>
                    <div class="flex flex-col gap-1">
                        <span class="text-[9px] font-black text-rose-400 uppercase tracking-widest">Jenis Pelanggaran</span>
                        <div id="confirmViolations" class="text-xs font-bold text-rose-600 bg-rose-50 p-2.5 rounded-xl border border-rose-100"></div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="flex flex-col gap-1">
                            <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Waktu</span>
                            <div id="confirmTime" class="text-xs font-bold text-slate-600"></div>
                        </div>
                        <div class="flex flex-col gap-1">
                            <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Keterangan</span>
                            <div id="confirmKeterangan" class="text-xs font-medium text-slate-500 italic truncate"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 p-5 bg-slate-50 flex gap-3">
                <button type="button" class="flex-1 py-3 text-[10px] font-black uppercase tracking-widest text-slate-400 hover:text-slate-600 transition-colors" data-dismiss="modal">Batal</button>
                <button type="button" id="btnConfirmSubmit" class="flex-[2] py-3 bg-indigo-600 text-white text-[10px] font-black uppercase tracking-[0.2em] rounded-xl shadow-lg shadow-indigo-100 transition-all active:scale-95">Konfirmasi & Simpan</button>
            </div>
        </div>
    </div>
</div>

</div>
@endsection

@push('scripts')
    <!-- jQuery and BS Custom File Input (kept as requested) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bs-custom-file-input/dist/bs-custom-file-input.min.js"></script>
    <script src="{{ asset('js/pages/pelanggaran/create.js') }}"></script>
@endpush