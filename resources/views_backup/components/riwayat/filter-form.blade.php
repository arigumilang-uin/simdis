{{-- Filter Form Partial for Riwayat Pelanggaran --}}
{{-- Usage: @include('components.riwayat.filter-form') --}}

<script>
    function toggleFilter() {
        const content = document.getElementById('filterContentRiwayat');
        content.classList.toggle('hidden');
    }

    // Auto-close di HP jika tidak ada filter aktif
    document.addEventListener('DOMContentLoaded', function() {
        if (window.innerWidth < 768) {
            const urlParams = new URLSearchParams(window.location.search);
            const isFiltered = urlParams.has('cari_siswa') || urlParams.has('start_date') || urlParams.has('end_date') || urlParams.has('jenis_pelanggaran_id') || urlParams.has('kelas_id');
            
            if (!isFiltered) {
                const content = document.getElementById('filterContentRiwayat');
                if (content) content.classList.add('hidden');
            }
        }
    });
</script>

<div id="riwayatFilterCard" class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden mb-6 sticky top-4 z-10">
    
    {{-- Header Kartu (Toggle-able) --}}
    <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center cursor-pointer transition-colors hover:bg-slate-50 group" onclick="toggleFilter()">
        <h3 class="text-sm font-bold text-slate-700 uppercase tracking-wider m-0 flex items-center gap-2 group-hover:text-indigo-600 transition-colors">
            <span class="p-1.5 bg-indigo-50 border border-indigo-100 rounded-lg text-indigo-600 shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
            </span>
            Filter & Pencarian Log Riwayat
        </h3>
        <span class="text-slate-400 group-hover:text-indigo-500 transition-colors">
            <i class="fas fa-chevron-down text-xs"></i>
        </span>
    </div>

    {{-- Konten Filter --}}
    <div id="filterContentRiwayat" class="transition-all duration-300 ease-in-out p-6">
        <form id="filterForm" action="{{ route('riwayat.index') }}" method="GET" class="w-full">
            
            <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                {{-- 1. Rentang Waktu (Col: 3 atau 4 untuk ruang lebih luas) --}}
<div class="md:col-span-4 lg:col-span-3">
    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">
        <i class="fas fa-calendar-alt mr-1 text-indigo-500"></i> Rentang Waktu
    </label>
    <div class="flex items-center gap-1 bg-slate-50 border border-slate-200 rounded-xl px-2 shadow-sm focus-within:ring-2 focus-within:ring-indigo-500 focus-within:border-indigo-500 transition-all">
        <input 
            type="date" 
            name="start_date" 
            value="{{ request('start_date') }}" 
            class="w-full bg-transparent border-none text-slate-700 text-xs py-2 px-1 focus:ring-0 cursor-pointer"
            onchange="this.form.submit()"
        >
        <span class="text-slate-400 shrink-0">
            <i class="fas fa-arrow-right text-[10px]"></i>
        </span>
        <input 
            type="date" 
            name="end_date" 
            value="{{ request('end_date') }}" 
            class="w-full bg-transparent border-none text-slate-700 text-xs py-2 px-1 focus:ring-0 cursor-pointer"
            onchange="this.form.submit()"
        >
    </div>
</div>
                {{-- 2. Filter Kelas (Admin/Operator Only) (Col: 2) --}}
                @if(!Auth::user()->hasRole('Wali Kelas'))
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">
                        <i class="fas fa-layer-group mr-1"></i> Kelas
                    </label>
                    <div class="relative">
                        <select name="kelas_id" class="w-full appearance-none bg-slate-50 border border-slate-200 text-slate-700 text-sm rounded-xl focus:ring-indigo-500 focus:border-indigo-500 block p-2.5 pr-8 shadow-sm transition-all hover:bg-white cursor-pointer" onchange="this.form.submit()">
                            <option value="">- Semua -</option>
                            @foreach($allKelas as $k)
                                <option value="{{ $k->id }}" {{ request('kelas_id') == $k->id ? 'selected' : '' }}>
                                    {{ $k->nama_kelas }}
                                </option>
                            @endforeach
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-slate-400">
                            <i class="fas fa-chevron-down text-[10px]"></i>
                        </div>
                    </div>
                </div>
                @endif

                {{-- 3. Filter Jenis Pelanggaran (Col: 3) --}}
                <div class="md:col-span-3">
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">
                        <i class="fas fa-list-check mr-1"></i> Jenis Pelanggaran
                    </label>
                    <div class="relative">
                        <select name="jenis_pelanggaran_id" class="w-full appearance-none bg-slate-50 border border-slate-200 text-slate-700 text-sm rounded-xl focus:ring-indigo-500 focus:border-indigo-500 block p-2.5 pr-8 shadow-sm transition-all hover:bg-white cursor-pointer" onchange="this.form.submit()">
                            <option value="">- Semua Jenis -</option>
                            @foreach($allPelanggaran as $jp)
                                <option value="{{ $jp->id }}" {{ request('jenis_pelanggaran_id') == $jp->id ? 'selected' : '' }}>
                                    [{{ $jp->kategoriPelanggaran->nama_kategori }}] {{ Str::limit($jp->nama_pelanggaran, 30) }}
                                </option>
                            @endforeach
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-slate-400">
                            <i class="fas fa-chevron-down text-[10px]"></i>
                        </div>
                    </div>
                </div>

                {{-- 4. Cari Siswa (Col: 3) --}}
                <div class="md:col-span-3">
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">
                        <i class="fas fa-search mr-1"></i> Cari Siswa
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
                            <i class="fas fa-search text-xs"></i>
                        </div>
                        <input type="text" name="cari_siswa" value="{{ request('cari_siswa') }}" 
                            class="block w-full p-2.5 pl-10 text-sm text-slate-900 border border-slate-200 rounded-xl bg-slate-50 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm transition-all hover:bg-white placeholder-slate-400" 
                            placeholder="Nama / NISN..." 
                            oninput="clearTimeout(window._searchDebounce); window._searchDebounce=setTimeout(() => { this.form.submit(); }, 800)">
                    </div>
                </div>

                {{-- 5. Reset Button (Col: 1) --}}
                <div class="md:col-span-1">
                    @if(request()->anyFilled(['cari_siswa', 'start_date', 'end_date', 'jenis_pelanggaran_id', 'kelas_id']))
                        <a href="{{ route('riwayat.index') }}" 
                            class="w-full inline-flex justify-center items-center py-2.5 text-sm font-semibold border border-rose-100 bg-rose-50 text-rose-600 rounded-xl shadow-sm hover:bg-rose-100 transition-colors h-[42px]" 
                            title="Reset Filter">
                            <i class="fas fa-times"></i>
                        </a>
                    @endif
                </div>

            </div>
        </form>
    </div>
</div>