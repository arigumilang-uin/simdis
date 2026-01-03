<div class="animate-in fade-in duration-500">
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
            <h5 class="text-xs font-black uppercase tracking-widest text-slate-500 m-0 flex items-center gap-2">
                <i class="fas fa-filter text-indigo-500"></i> Filter Parameter
            </h5>
        </div>
        <div class="p-6">
            <form method="GET" action="{{ route('audit.activity.index') }}" class="grid grid-cols-1 md:grid-cols-12 gap-4">
                <input type="hidden" name="tab" value="activity">
                
                <div class="md:col-span-3">
                    <label class="text-[10px] font-bold text-slate-400 uppercase mb-1.5 block tracking-tight">Cari Deskripsi</label>
                    <input type="text" name="search" class="custom-input-clean w-full" 
                           placeholder="Kata kunci..." value="{{ request('search') }}">
                </div>

                <div class="md:col-span-2">
                    <label class="text-[10px] font-bold text-slate-400 uppercase mb-1.5 block tracking-tight">Jenis Log</label>
                    <select name="type" class="custom-select-clean w-full">
                        <option value="">Semua Jenis</option>
                        @foreach($activityTypes as $type)
                            <option value="{{ $type }}" {{ request('type') == $type ? 'selected' : '' }}>
                                {{ ucfirst($type) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="md:col-span-4 flex gap-3">
                    <div class="flex-1">
                        <label class="text-[10px] font-bold text-slate-400 uppercase mb-1.5 block tracking-tight">Dari</label>
                        <input type="date" name="dari_tanggal" class="custom-input-clean w-full" value="{{ request('dari_tanggal') }}">
                    </div>
                    <div class="flex-1">
                        <label class="text-[10px] font-bold text-slate-400 uppercase mb-1.5 block tracking-tight">Sampai</label>
                        <input type="date" name="sampai_tanggal" class="custom-input-clean w-full" value="{{ request('sampai_tanggal') }}">
                    </div>
                </div>

                <div class="md:col-span-3 flex items-end gap-2">
                    <button type="submit" class="btn-filter-primary flex-1">
                        <i class="fas fa-search mr-1"></i> Filter
                    </button>
                    @if(request()->hasAny(['search', 'type', 'dari_tanggal', 'sampai_tanggal']))
                        <a href="{{ route('audit.activity.index', ['tab' => 'activity']) }}" class="btn-filter-secondary px-4">
                            <i class="fas fa-redo"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="px-6 py-4 bg-slate-50/50 border-b border-slate-100 flex justify-between items-center">
            <span class="text-[11px] font-black text-slate-400 uppercase tracking-widest">Aktivitas Terbaru</span>
            <span class="text-[10px] font-bold text-indigo-600 bg-indigo-50 px-3 py-1 rounded-full border border-indigo-100">
                Total: {{ $logs->total() }} Record
            </span>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse custom-solid-table">
                <thead>
                    <tr class="text-[10px] font-bold text-slate-400 uppercase tracking-wider bg-slate-50 border-b border-slate-100">
                        <th class="px-6 py-4 w-44">Waktu & Tanggal</th>
                        <th class="px-6 py-4 w-32 text-center">Jenis</th>
                        <th class="px-6 py-4 w-64">Pelaku (User)</th>
                        <th class="px-6 py-4">Keterangan Aktivitas</th>
                        <th class="px-6 py-4 text-center w-24 pr-8">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($logs as $log)
                    <tr class="hover:bg-slate-50/50 transition-all duration-200">
                        <td class="px-6 py-4">
                            <div class="flex flex-col">
                                <span class="text-sm font-bold text-slate-700 leading-tight">{{ formatDate($log->created_at) }}</span>
                                <span class="text-[10px] font-mono text-slate-400 uppercase tracking-tighter mt-0.5">{{ formatTime($log->created_at) }} WIB</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="px-2.5 py-1 rounded-lg bg-blue-50 text-blue-600 text-[10px] font-black uppercase border border-blue-100 whitespace-nowrap">
                                {{ $log->log_name }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-xs border border-indigo-100 shadow-sm">
                                    {{ strtoupper(substr($log->causer->nama ?? 'S', 0, 1)) }}
                                </div>
                                <div class="flex flex-col min-w-0">
                                    <span class="text-sm font-bold text-slate-700 truncate leading-tight">{{ $log->causer->nama ?? 'System' }}</span>
                                    <span class="text-[10px] text-slate-400 uppercase font-medium tracking-wide">{{ $log->causer->role->nama_role ?? '-' }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-xs text-slate-600 leading-relaxed italic max-w-md">
                                "{{ $log->description }}"
                            </div>
                        </td>
                        <td class="px-6 py-4 text-center pr-8">
                            <a href="{{ route('audit.activity.show', $log->id) }}" class="btn-action hover:text-indigo-600 hover:border-indigo-100" title="Detail">
                                <i class="fas fa-eye text-sm"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-20 text-center text-slate-400">
                            <div class="flex flex-col items-center opacity-40">
                                <i class="fas fa-history text-4xl mb-4"></i>
                                <p class="text-sm font-bold uppercase tracking-widest">Data Log Tidak Ditemukan</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
        <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/30">
            <div class="pagination-indigo">
                {{ $logs->appends(request()->all())->links('pagination::bootstrap-4') }}
            </div>
        </div>
        @endif
    </div>
</div>

<style>
    /* Sinkronisasi dengan Modul Kelola Aturan */
    .custom-input-clean, .custom-select-clean {
        border: 1px solid #e2e8f0;
        border-radius: 0.75rem;
        padding: 0.6rem 0.8rem;
        font-size: 0.85rem;
        background-color: #ffffff;
        color: #1e293b;
        transition: all 0.2s;
        outline: none;
    }
    .custom-input-clean:focus, .custom-select-clean:focus {
        border-color: #4f46e5;
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
    }
    .btn-filter-primary {
        background-color: #4f46e5;
        color: #ffffff;
        border-radius: 0.75rem;
        font-weight: 800;
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        padding: 0.65rem 1rem;
        border: none;
        transition: 0.2s;
    }
    .btn-filter-primary:hover {
        background-color: #4338ca;
        transform: translateY(-1px);
    }
    .btn-filter-secondary {
        background-color: #f1f5f9;
        color: #64748b;
        border-radius: 0.75rem;
        font-weight: 800;
        padding: 0.65rem 1rem;
        text-decoration: none !important;
        transition: 0.2s;
    }
    .btn-filter-secondary:hover {
        background-color: #e2e8f0;
        color: #1e293b;
    }
    .btn-action {
        width: 34px;
        height: 34px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        background: #f8fafc;
        border: 1px solid #f1f5f9;
        color: #94a3b8;
        transition: 0.2s;
    }
    .custom-solid-table thead th {
        vertical-align: middle;
    }
    .custom-solid-table tbody td {
        vertical-align: middle;
    }
</style>