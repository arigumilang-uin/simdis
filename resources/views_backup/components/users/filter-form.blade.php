{{-- filter user --}}

{{-- Filter Form Partial for Users Management --}}

<script>
    function toggleFilter() {
        const content = document.getElementById('filterContent');
        // Gunakan toggle tanpa arrow
        content.classList.toggle('hidden');
    }

    // (Opsional) Auto-close di HP biar gak menuhin layar
    document.addEventListener('DOMContentLoaded', function() {
        if (window.innerWidth < 768) {
            const urlParams = new URLSearchParams(window.location.search);
            if (!urlParams.has('cari') && !urlParams.has('role_id')) {
                const content = document.getElementById('filterContent');
                if (content) {
                    content.classList.add('hidden'); // Tutup default di HP
                }
            }
        }
    });
</script>

<div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden mb-6">
    
    <div class="px-6 py-4 bg-slate-50 border-b border-slate-100 flex justify-between items-center cursor-pointer transition-colors hover:bg-blue-50/50 group" onclick="toggleFilter()">
        
        <h3 class="text-sm font-bold text-slate-700 uppercase tracking-wider m-0 flex items-center gap-2 group-hover:text-blue-600 transition-colors">
            <span class="p-1.5 bg-white border border-slate-200 rounded-lg text-blue-500 shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
            </span>
            Filter & Pencarian
        </h3>
    </div>

    <div id="filterContent" class="transition-all duration-300 ease-in-out">
        <div class="p-6">
            <form id="filterForm" action="{{ route('users.index') }}" method="GET" class="w-full">
                
                {{-- Grid direorganisasi menjadi 12 kolom untuk proporsi yang lebih baik (misal 3, 7, 2) --}}
                <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
            
                    {{-- Role (Jabatan): 4/12 kolom --}}
                    <div class="md:col-span-5 lg:col-span-4">
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">
                            Role (Jabatan)
                        </label>
                        <div class="relative">
                            {{-- ONCHANGE AUTO-SUBMIT --}}
                            <select name="role_id" class="w-full appearance-none bg-slate-50 border border-slate-200 text-slate-700 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block p-2.5 pr-8 shadow-sm transition-all hover:bg-white cursor-pointer" onchange="this.form.submit()">
                                <option value="">- Semua Role -</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->id }}" {{ request('role_id') == $role->id ? 'selected' : '' }}>
                                        {{ $role->nama_role }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-slate-500">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </div>
                        </div>
                    </div>
            
                    {{-- Cari Nama / Username / Email: 8/12 kolom --}}
                    <div class="md:col-span-7 lg:col-span-8">
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">
                            Cari Nama / Username / Email
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                            </div>
                            {{-- DEBOUNCED AUTO-SUBMIT --}}
                            <input type="text" name="cari" value="{{ request('cari') }}" class="block w-full p-2.5 pl-10 text-sm text-slate-900 border border-slate-200 rounded-xl bg-slate-50 focus:ring-blue-500 focus:border-blue-500 shadow-sm transition-all hover:bg-white placeholder-slate-400" placeholder="Ketik kata kunci pencarian..." oninput="clearTimeout(window._userSearchDebounce); window._userSearchDebounce=setTimeout(function(){document.getElementById('filterForm').submit();}, 800)">
                        </div>
                    </div>
                    
                    {{-- HAPUS TOMBOL SUBMIT DARI SINI --}}
                
                </div>
            
                @if(request()->has('cari') || request()->has('role_id'))
                    <div class="mt-4 pt-3 border-t border-slate-100 flex justify-end animate-fade-in">
                        <a href="{{ route('users.index') }}" class="group inline-flex items-center gap-2 text-xs font-bold text-rose-500 hover:text-rose-700 transition-colors">
                            <span class="p-1 bg-rose-50 rounded group-hover:bg-rose-100 transition">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                            </span>
                            Reset Filter Pencarian
                        </a>
                    </div>
                @endif
            </form>
        </div>
    </div>
</div>