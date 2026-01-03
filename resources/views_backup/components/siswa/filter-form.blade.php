{{-- filter siswa --}}

{{-- File ini adalah SATU LAPISAN kartu filter yang bersih. --}}

<script>
    // Pastikan fungsi toggleFilter tersedia di scope ini
    function toggleFilter() {
        const content = document.getElementById('filterContentSiswa');
        // Hapus class hidden
        content.classList.toggle('hidden');
    }

    // (Opsional) Auto-close di HP biar gak menuhin layar
    document.addEventListener('DOMContentLoaded', function() {
        if (window.innerWidth < 768) {
            const urlParams = new URLSearchParams(window.location.search);
            const isFiltered = urlParams.has('cari') || urlParams.has('kelas_id') || urlParams.has('tingkat') || urlParams.has('jurusan_id');
            
            // Tutup default jika tidak ada filter aktif
            if (!isFiltered) {
                const content = document.getElementById('filterContentSiswa');
                if (content) {
                    content.classList.add('hidden');
                }
            }
        }
    });
</script>


{{-- INI ADALAH SATU-SATUNYA KARTU FILTER (Card Utama) --}}
<div id="siswaFilterCard" class="bg-white rounded-2xl shadow-soft border border-slate-200 overflow-hidden mb-6 sticky top-4 z-10">
    
    {{-- Header Kartu yang Bisa di-toggle (Hanya border bawah sebagai pemisah visual) --}}
    <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center cursor-pointer transition-colors hover:bg-slate-50 group" onclick="toggleFilter()">
        
        <h3 class="text-sm font-bold text-slate-700 uppercase tracking-wider m-0 flex items-center gap-2 group-hover:text-blue-600 transition-colors">
            <span class="p-1.5 bg-blue-50 border border-blue-100 rounded-lg text-blue-600 shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
            </span>
            Filter & Pencarian Siswa
        </h3>
    </div>

    {{-- Konten Filter --}}
    <div id="filterContentSiswa" class="transition-all duration-300 ease-in-out p-6">
        <form id="filterFormSiswa" action="{{ route('siswa.index') }}" method="GET" class="w-full">
            
            {{-- Grid akan direorganisasi menjadi 10 kolom (2+3+3+2) atau 12 kolom tergantung konfigurasi --}}
            <div class="grid grid-cols-1 md:grid-cols-4 lg:grid-cols-5 gap-4 items-end">
                
                {{-- 1. Tingkat, Jurusan, Kelas (Hanya untuk non-Wali Kelas) --}}
                @if(!$isWaliKelas)
                    
                    {{-- Tingkat: 1/5 kolom (atau 2/12) --}}
                    <div class="col-span-1">
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">
                            Tingkat
                        </label>
                        <div class="relative">
                            {{-- ONCHANGE AUTO-SUBMIT --}}
                            <select name="tingkat" class="w-full appearance-none bg-slate-50 border border-slate-200 text-slate-700 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block p-2.5 pr-8 shadow-sm transition-all hover:bg-white cursor-pointer" data-filter="tingkat" onchange="this.form.submit()">
                                <option value="">- Semua Tingkat -</option>
                                <option value="X" {{ request('tingkat') == 'X' ? 'selected' : '' }}>Kelas X</option>
                                <option value="XI" {{ request('tingkat') == 'XI' ? 'selected' : '' }}>Kelas XI</option>
                                <option value="XII" {{ request('tingkat') == 'XII' ? 'selected' : '' }}>Kelas XII</option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-slate-500">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </div>
                        </div>
                    </div>

                    {{-- Jurusan: 1/5 kolom (Hanya untuk non-Kaprodi) --}}
                    @if(!($isKaprodi ?? false))
                        <div class="col-span-1">
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">
                                Jurusan
                            </label>
                            <div class="relative">
                                {{-- ONCHANGE AUTO-SUBMIT --}}
                                <select name="jurusan_id" class="w-full appearance-none bg-slate-50 border border-slate-200 text-slate-700 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block p-2.5 pr-8 shadow-sm transition-all hover:bg-white cursor-pointer" data-filter="jurusan" onchange="this.form.submit()">
                                    <option value="">- Semua Jurusan -</option>
                                    @foreach($allJurusan as $j)
                                        <option value="{{ $j->id }}" {{ request('jurusan_id') == $j->id ? 'selected' : '' }}>{{ $j->nama_jurusan }}</option>
                                    @endforeach
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-slate-500">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Kelas: 1/5 kolom --}}
                    <div class="col-span-1">
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">
                            Kelas
                        </label>
                        <div class="relative">
                            {{-- ONCHANGE AUTO-SUBMIT --}}
                            <select name="kelas_id" class="w-full appearance-none bg-slate-50 border border-slate-200 text-slate-700 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block p-2.5 pr-8 shadow-sm transition-all hover:bg-white cursor-pointer" data-filter="kelas" onchange="this.form.submit()">
                                <option value="">- Semua Kelas -</option>
                                @foreach($allKelas as $k)
                                    <option value="{{ $k->id }}" {{ request('kelas_id') == $k->id ? 'selected' : '' }}>{{ $k->nama_kelas }}</option>
                                @endforeach
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-slate-500">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </div>
                        </div>
                    </div>
                @endif
                
                {{-- Cari Siswa (Mengambil sisa ruang grid, atau 3/5 jika Wali Kelas) --}}
                <div class="
                    @if($isWaliKelas) col-span-3
                    @else col-span-1
                    @endif">
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">
                        Cari Nama / NISN
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </div>
                        {{-- DEBOUNCED AUTO-SUBMIT --}}
                        <input type="text" name="cari" value="{{ request('cari') }}" class="block w-full p-2.5 pl-10 text-sm text-slate-900 border border-slate-200 rounded-xl bg-slate-50 focus:ring-blue-500 focus:border-blue-500 shadow-sm transition-all hover:bg-white placeholder-slate-400" placeholder="Ketik Nama atau NISN..." oninput="clearTimeout(window._siswaSearchDebounce); window._siswaSearchDebounce=setTimeout(function(){document.getElementById('filterFormSiswa').submit();}, 800)">
                    </div>
                </div>

                {{-- Reset Button (1/5 kolom) --}}
                <div class="col-span-1">
                    @if(request()->has('cari') || request()->has('kelas_id') || request()->has('tingkat') || request()->has('jurusan_id'))
                        <a href="{{ route('siswa.index') }}" class="w-full inline-flex justify-center items-center py-2 text-sm font-semibold border border-rose-100 bg-rose-50 text-rose-600 rounded-xl shadow-sm hover:bg-rose-100 transition-colors h-[40px]" title="Hapus Semua Filter">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                            Reset
                        </a>
                    @endif
                </div>
            
            </div>
            
            {{-- HAPUS DIV RESET BAWAH KARENA SUDAH ADA DI GRID --}}
            
        </form>
    </div>
</div>