@extends('layouts.app')

{{-- 1. SCRIPT LOGIC (PALING ATAS) --}}
@push('scripts')
<script>
    (function(){
        // --- LOGIC TOGGLE SECTION ---
        const roleSelect = document.getElementById('roleSelect');
        const kaprodiSection = document.getElementById('kaprodiSection');
        const jurusanSelect = document.getElementById('jurusanSelect');
        const waliSection = document.getElementById('waliSection');
        const kelasSelect = document.getElementById('kelasSelect');
        const nipSection = document.getElementById('nipSection');
        const siswaSection = document.getElementById('siswaSection');
        const currentUserId = '{{ $user->id }}';

        function toggleSections() {
            const opt = roleSelect.options[roleSelect.selectedIndex];
            const roleName = opt ? opt.dataset.roleName : '';
            
            // Reset Display
            kaprodiSection.style.display = 'none';
            waliSection.style.display = 'none';
            siswaSection.style.display = 'none';
            if (nipSection) nipSection.style.display = '';

            // Logic Display
            if (roleName === 'Kaprodi' || roleName === 'Developer') {
                kaprodiSection.style.display = '';
            } 
            
            if (roleName === 'Wali Kelas' || roleName === 'Developer') {
                waliSection.style.display = '';
            } 
            
            if (roleName === 'Wali Murid' || roleName === 'Developer') {
                siswaSection.style.display = '';
                if (nipSection && roleName === 'Wali Murid') nipSection.style.display = 'none';
            }
        }

        // Logic Disable Option (Kecuali Punya Sendiri)
        function disableAssigned() {
            if (jurusanSelect) {
                Array.from(jurusanSelect.options).forEach(opt => {
                    const kaprodiId = opt.dataset.kaprodiId;
                    if (kaprodiId && kaprodiId !== '' && kaprodiId !== currentUserId) {
                        opt.disabled = true;
                    }
                });
            }
            if (kelasSelect) {
                Array.from(kelasSelect.options).forEach(opt => {
                    const waliId = opt.dataset.waliId;
                    if (waliId && waliId !== '' && waliId !== currentUserId) {
                        opt.disabled = true;
                    }
                });
            }
        }

        roleSelect.addEventListener('change', toggleSections);
        document.addEventListener('DOMContentLoaded', function(){ 
            toggleSections(); 
            disableAssigned(); 
        });
    })();
    
    // --- LOGIC FILTER SISWA ---
    function filterStudents() {
        const tingkat = document.getElementById('filterTingkat').value.toLowerCase();
        const jurusan = document.getElementById('filterJurusan').value;
        const kelas = document.getElementById('filterKelas').value;
        const search = document.getElementById('searchSiswa').value.toLowerCase();
        
        const items = document.querySelectorAll('.student-card-wrapper');
        let visibleCount = 0;
        
        items.forEach(item => {
            const iTingkat = item.dataset.tingkat.toLowerCase();
            const iJurusan = item.dataset.jurusan;
            const iKelas = item.dataset.kelas;
            const iSearch = item.dataset.search;
            
            let show = true;
            if (tingkat && !iTingkat.includes(tingkat)) show = false;
            if (jurusan && iJurusan !== jurusan) show = false;
            if (kelas && iKelas !== kelas) show = false;
            if (search && !iSearch.includes(search)) show = false;
            
            item.style.display = show ? '' : 'none';
            if (show) visibleCount++;
        });
        
        const noMsg = document.getElementById('noResultMsg');
        if(noMsg) noMsg.style.display = visibleCount === 0 ? 'block' : 'none';
    }

    function resetFilters() {
        ['filterTingkat', 'filterJurusan', 'filterKelas', 'searchSiswa'].forEach(id => {
            document.getElementById(id).value = '';
        });
        filterStudents();
    }

    document.addEventListener('DOMContentLoaded', function() {
        ['filterTingkat', 'filterJurusan', 'filterKelas', 'searchSiswa'].forEach(id => {
            const el = document.getElementById(id);
            if(el) el.addEventListener(id === 'searchSiswa' ? 'input' : 'change', filterStudents);
        });
    });
</script>
@endpush

{{-- 2. CONTENT FORM --}}
@section('content')

<script src="https://cdn.tailwindcss.com"></script>
<script>
    tailwind.config = {
        theme: {
            extend: { colors: { primary: '#4f46e5', slate: { 800: '#1e293b', 900: '#0f172a' } } }
        },
        corePlugins: { preflight: false }
    }
</script>

<div class="page-container p-4">

    <form action="{{ route('users.update', $user->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row">
            <div class="col-lg-8">
                
                {{-- Alert Error --}}
                @if ($errors->any())
                    <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-4 rounded-r">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-exclamation-circle text-red-500"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-red-700 font-bold">Gagal memperbarui data:</p>
                                <ul class="list-disc list-inside text-xs text-red-600 mt-1">
                                    @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 mb-6 overflow-hidden">
                    <div class="bg-slate-50 px-6 py-4 border-b border-slate-200">
                        <h3 class="text-sm font-bold text-slate-700 m-0 uppercase tracking-wide flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-primary"></span>
                            1. Informasi Akun
                        </h3>
                    </div>
                    <div class="p-6">
                        
                        <div class="bg-blue-50 border border-blue-100 rounded-lg p-3 mb-5 text-xs text-blue-700">
                            <i class="fas fa-info-circle mr-1"></i>
                            <strong>Catatan:</strong> Nama akan di-generate otomatis sesuai role. Username & Password hanya berubah otomatis jika user belum pernah menggantinya.
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            
                            <div class="form-group mb-0">
                                <label class="form-label-modern">Nama Lengkap <span class="text-red-500">*</span></label>
                                <input type="text" name="nama" class="form-input-modern w-full" value="{{ old('nama', $user->nama) }}" required>
                            </div>

                            <div class="form-group mb-0">
                                <label class="form-label-modern">Jabatan (Role) <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <select name="role_id" id="roleSelect" class="form-input-modern w-full appearance-none pr-8" required>
                                        @foreach($roles as $role)
                                            @php
                                                $disabled = ($role->nama_role === 'Kepala Sekolah' && isset($kepsekExists) && $kepsekExists && (!isset($kepsekId) || $kepsekId != $user->id)) ? 'disabled' : '';
                                            @endphp
                                            <option value="{{ $role->id }}" data-role-name="{{ $role->nama_role }}" {{ (old('role_id', $user->role_id) == $role->id) ? 'selected' : '' }} {{ $disabled }}>
                                                {{ $role->nama_role }} @if($disabled) (Sudah Terisi) @endif
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none text-slate-500">
                                        <i class="fas fa-chevron-down text-xs"></i>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group mb-0">
                                <label class="form-label-modern">Username <span class="text-red-500">*</span></label>
                                <input type="text" name="username" class="form-input-modern w-full" value="{{ old('username', $user->username) }}" required>
                                <div class="mt-1">
                                    @if($user->hasChangedUsername())
                                        <span class="text-[10px] text-emerald-600 font-bold bg-emerald-50 px-2 py-0.5 rounded"><i class="fas fa-check"></i> Custom Username</span>
                                    @else
                                        <span class="text-[10px] text-slate-400 bg-slate-100 px-2 py-0.5 rounded">Default (Auto-Generate)</span>
                                    @endif
                                </div>
                            </div>

                            <div class="form-group mb-0">
                                <label class="form-label-modern">Password Baru <span class="text-slate-400 text-xs font-normal">(Opsional)</span></label>
                                <input type="password" name="password" class="form-input-modern w-full" placeholder="Biarkan kosong jika tetap">
                                <div class="mt-1">
                                    @if($user->hasChangedPassword())
                                        <span class="text-[10px] text-emerald-600 font-bold bg-emerald-50 px-2 py-0.5 rounded"><i class="fas fa-check"></i> Password Diubah User</span>
                                    @else
                                        <span class="text-[10px] text-slate-400 bg-slate-100 px-2 py-0.5 rounded">Password Masih Default</span>
                                    @endif
                                </div>
                            </div>

                            <div class="form-group mb-0">
                                <label class="form-label-modern">Email Address <span class="text-red-500">*</span></label>
                                <input type="email" name="email" class="form-input-modern w-full" value="{{ old('email', $user->email) }}" required>
                            </div>

                            <div class="form-group mb-0">
                                <label class="form-label-modern">No. Handphone</label>
                                <input type="text" name="phone" class="form-input-modern w-full" value="{{ old('phone', $user->phone) }}" {{ $user->isWaliMurid() ? 'readonly title="Diambil dari data siswa"' : '' }}>
                                @if($user->isWaliMurid())
                                    <small class="text-slate-400 text-[10px]">Sinkron otomatis dari data siswa.</small>
                                @endif
                            </div>

                        </div>
                    </div>
                </div>

                <div id="nipSection" class="bg-white rounded-2xl shadow-sm border border-slate-200 mb-6 overflow-hidden">
                    <div class="bg-slate-50 px-6 py-4 border-b border-slate-200">
                        <h3 class="text-sm font-bold text-slate-700 m-0 uppercase tracking-wide flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            2. Data Kepegawaian
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="form-label-modern">NIP / NI PPPK</label>
                                <input type="text" name="nip" class="form-input-modern w-full" value="{{ old('nip', $user->nip) }}" maxlength="18">
                            </div>
                            <div>
                                <label class="form-label-modern">NUPTK</label>
                                <input type="text" name="nuptk" class="form-input-modern w-full" value="{{ old('nuptk', $user->nuptk) }}" maxlength="18">
                            </div>
                        </div>
                    </div>
                </div>

                <div id="mappingContainer">
                    
                    <div id="waliSection" style="display:none;" class="bg-white rounded-2xl shadow-sm border border-blue-200 mb-6">
                        <div class="bg-blue-50 px-6 py-4 border-b border-blue-100">
                            <h3 class="text-sm font-bold text-blue-800 m-0 uppercase tracking-wide">Mapping Wali Kelas</h3>
                        </div>
                        <div class="p-6">
                            <label class="form-label-modern text-blue-700">Kelas Binaan</label>
                            <div class="relative">
                                <select name="kelas_id" id="kelasSelect" class="form-input-modern w-full border-blue-300 focus:ring-blue-200 appearance-none pr-8">
                                    <option value="">-- Pilih Kelas --</option>
                                    @foreach($kelas as $k)
                                        @php 
                                            $hasWali = $k->wali_kelas_user_id; 
                                            $isMine = $hasWali == $user->id;
                                        @endphp
                                        <option value="{{ $k->id }}" data-wali-id="{{ $hasWali ?? '' }}" {{ (old('kelas_id', $user->kelasDiampu->id ?? '') == $k->id) ? 'selected' : '' }} {{ ($hasWali && !$isMine) ? 'disabled' : '' }}>
                                            {{ $k->nama_kelas }} @if($hasWali && !$isMine) (Terisi) @endif
                                        </option>
                                    @endforeach
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none text-blue-500">
                                    <i class="fas fa-chevron-down text-xs"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="kaprodiSection" style="display:none;" class="bg-white rounded-2xl shadow-sm border border-blue-200 mb-6">
                        <div class="bg-blue-50 px-6 py-4 border-b border-blue-100">
                            <h3 class="text-sm font-bold text-blue-800 m-0 uppercase tracking-wide">Mapping Kaprodi</h3>
                        </div>
                        <div class="p-6">
                            <label class="form-label-modern text-blue-700">Program Studi</label>
                            <div class="relative">
                                <select name="jurusan_id" id="jurusanSelect" class="form-input-modern w-full border-blue-300 focus:ring-blue-200 appearance-none pr-8">
                                    <option value="">-- Pilih Jurusan --</option>
                                    @foreach($jurusan as $j)
                                        @php 
                                            $hasKaprodi = $j->kaprodi_user_id; 
                                            $isMine = $hasKaprodi == $user->id;
                                        @endphp
                                        <option value="{{ $j->id }}" data-kaprodi-id="{{ $hasKaprodi ?? '' }}" {{ (old('jurusan_id', $user->jurusanDiampu->id ?? '') == $j->id) ? 'selected' : '' }} {{ ($hasKaprodi && !$isMine) ? 'disabled' : '' }}>
                                            {{ $j->nama_jurusan }} @if($hasKaprodi && !$isMine) (Terisi) @endif
                                        </option>
                                    @endforeach
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none text-blue-500">
                                    <i class="fas fa-chevron-down text-xs"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="siswaSection" style="display:none;" class="bg-white rounded-2xl shadow-sm border border-slate-200 mb-6 overflow-hidden">
                        <div class="bg-slate-50 px-6 py-4 border-b border-slate-200 flex justify-between items-center">
                            <h3 class="text-sm font-bold text-slate-700 m-0 uppercase tracking-wide flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                                3. Hubungkan Siswa (Anak)
                            </h3>
                           <button type="button" class="px-3 py-2 bg-white border border-slate-300 rounded-lg text-xs font-bold text-slate-600 hover:bg-slate-50 hover:text-slate-800 transition shadow-sm" onclick="resetFilters()">
                                <i class="fas fa-undo mr-1"></i> Reset
                        </button>
                        </div>
                        
                        <div class="p-6">
                            <div class="flex flex-wrap gap-3 mb-5 p-1">
                                <select id="filterTingkat" class="form-select-sm-modern">
                                    <option value="">Semua Tingkat</option>
                                    <option value="X">Kelas X</option>
                                    <option value="XI">Kelas XI</option>
                                    <option value="XII">Kelas XII</option>
                                </select>
                                <select id="filterJurusan" class="form-select-sm-modern">
                                    <option value="">Semua Jurusan</option>
                                    @foreach($jurusan as $j) <option value="{{ $j->id }}">{{ $j->nama_jurusan }}</option> @endforeach
                                </select>
                                <select id="filterKelas" class="form-select-sm-modern">
                                    <option value="">Semua Kelas</option>
                                    @foreach($kelas as $k) <option value="{{ $k->id }}" data-jurusan="{{ $k->jurusan_id }}">{{ $k->nama_kelas }}</option> @endforeach
                                </select>
                                <input type="text" id="searchSiswa" class="form-input-sm-modern flex-grow" placeholder="Cari Nama / NISN...">
                            </div>

                            <div class="student-scroll-area">
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3" id="studentGrid">
                                    
                                    @php
                                        // Sort: Connected students first
                                        $connectedIds = $connectedSiswaIds ?? [];
                                        $sortedSiswa = $siswa->sortByDesc(function($s) use ($connectedIds) {
                                            return in_array($s->id, $connectedIds);
                                        });
                                    @endphp

                                    @foreach($sortedSiswa as $s)
                                        @php
                                            $isChecked = in_array($s->id, $connectedIds);
                                        @endphp
                                        <div class="student-card-wrapper" 
                                             data-tingkat="{{ explode(' ', $s->kelas->nama_kelas ?? '')[0] }}" 
                                             data-jurusan="{{ $s->kelas->jurusan_id ?? '' }}" 
                                             data-kelas="{{ $s->kelas_id }}" 
                                             data-search="{{ strtolower($s->nama_siswa . ' ' . $s->nisn) }}">
                                            
                                            <label class="flex items-center p-3 border rounded-xl cursor-pointer transition-all group h-full relative
                                                          {{ $isChecked ? 'bg-emerald-50 border-emerald-500' : 'bg-white border-slate-200 hover:border-blue-500 hover:shadow-md' }}">
                                                
                                                <input type="checkbox" name="siswa_ids[]" value="{{ $s->id }}" class="peer sr-only student-check-input" {{ $isChecked ? 'checked' : '' }}>
                                                
                                                <div class="absolute top-0 right-0 p-2 text-emerald-600 {{ $isChecked ? '' : 'opacity-0 peer-checked:opacity-100 text-blue-600' }}">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
                                                </div>

                                                <div class="w-10 h-10 rounded-lg flex items-center justify-center font-bold text-sm shrink-0 transition-colors
                                                            {{ $isChecked ? 'bg-emerald-200 text-emerald-800' : 'bg-slate-100 text-slate-500 group-hover:bg-blue-50 group-hover:text-blue-600 peer-checked:bg-blue-600 peer-checked:text-white' }}">
                                                    {{ substr($s->nama_siswa, 0, 1) }}
                                                </div>
                                                <div class="ml-3 overflow-hidden">
                                                    <h6 class="text-sm font-bold truncate transition-colors {{ $isChecked ? 'text-emerald-800' : 'text-slate-700 peer-checked:text-blue-700' }}">
                                                        {{ $s->nama_siswa }}
                                                    </h6>
                                                    <p class="text-[10px] truncate {{ $isChecked ? 'text-emerald-600' : 'text-slate-400' }}">
                                                        {{ $s->nisn }} • {{ $s->kelas->nama_kelas ?? '-' }}
                                                    </p>
                                                </div>
                                                
                                                <div class="absolute inset-0 border-2 border-transparent rounded-xl pointer-events-none {{ $isChecked ? 'border-emerald-500' : 'peer-checked:border-blue-500' }}"></div>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                                <div id="noResultMsg" class="text-center py-8 text-slate-400 text-sm hidden">
                                    <i class="fas fa-search mb-2 text-2xl opacity-50"></i><br>
                                    Siswa tidak ditemukan.
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

            </div>

            <div class="col-lg-4">
                
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 mb-4 sticky top-6 z-10">
                    <div class="p-6">
                        <h4 class="text-sm font-bold text-slate-700 uppercase tracking-wide mb-3 flex items-center gap-2">
                            <i class="fas fa-check-circle text-emerald-500"></i> Simpan Perubahan
                        </h4>
                        <p class="text-xs text-slate-500 mb-6 leading-relaxed">
                            Pastikan data yang diubah sudah benar sebelum menyimpan.
                        </p>
                        
                        <button type="submit" class="w-full bg-amber-500 hover:bg-amber-600 text-white font-bold py-3 px-4 rounded-xl shadow-lg shadow-amber-200 transition-all transform active:scale-95 mb-3 flex items-center justify-center gap-2">
                            <i class="fas fa-save"></i> Update User
                        </button>
                        
                        <a href="{{ route('users.index') }}" class="w-full block text-center bg-white border border-slate-200 hover:bg-slate-50 text-slate-600 font-bold py-3 px-4 rounded-xl transition-colors text-sm">
                            Batal
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </form>
</div>
@endsection

{{-- 3. STYLE CSS --}}
@section('styles')
<style>
    /* Styling Manual untuk komponen form yang Modern & Clean */
    
    .form-label-modern {
        display: block;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        color: #64748b;
        margin-bottom: 0.5rem;
        letter-spacing: 0.025em;
    }

    .form-input-modern {
        display: block;
        width: 100%;
        padding: 0.75rem 1rem; /* Lebih tinggi dan lega */
        font-size: 0.875rem;
        line-height: 1.25;
        color: #1e293b;
        background-color: #fff;
        background-clip: padding-box;
        border: 1px solid #e2e8f0; /* Border sangat halus */
        border-radius: 0.75rem; /* Rounded modern */
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }

    .form-input-modern:focus {
        border-color: #6366f1; /* Primary indigo/blue */
        outline: 0;
        box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
    }

    /* Small Input untuk Filter */
    .form-select-sm-modern, .form-input-sm-modern {
        padding: 0.5rem 0.75rem;
        font-size: 0.75rem;
        border: 1px solid #e2e8f0;
        border-radius: 0.5rem;
        color: #475569;
        background-color: #fff;
    }
    .form-select-sm-modern:focus, .form-input-sm-modern:focus {
        border-color: #6366f1;
        outline: none;
        box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.1);
    }

    /* Scroll Area untuk Siswa */
    .student-scroll-area {
        max-height: 400px;
        overflow-y: auto;
        padding-right: 5px;
    }
    .student-scroll-area::-webkit-scrollbar { width: 5px; }
    .student-scroll-area::-webkit-scrollbar-track { background: #f8fafc; }
    .student-scroll-area::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    .student-scroll-area::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
</style>
@endsection