<div class="animate-in fade-in duration-500">
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
            <h5 class="text-xs font-black uppercase tracking-widest text-slate-500 m-0 flex items-center gap-2">
                <i class="fas fa-filter text-indigo-500"></i> Filter Pengguna
            </h5>
        </div>
        <div class="p-6">
            <form method="GET" action="{{ route('audit.activity.index') }}" class="grid grid-cols-1 md:grid-cols-12 gap-4">
                <input type="hidden" name="tab" value="last-login">
                
                <div class="md:col-span-5">
                    <label class="text-[10px] font-bold text-slate-400 uppercase mb-1.5 block tracking-tight">Cari Pengguna</label>
                    <input type="text" name="search" class="custom-input-clean w-full" 
                           placeholder="Nama, Username, atau Email..." value="{{ request('search') }}">
                </div>

                <div class="md:col-span-4">
                    <label class="text-[10px] font-bold text-slate-400 uppercase mb-1.5 block tracking-tight">Role / Jabatan</label>
                    <select name="role_id" class="custom-select-clean w-full">
                        <option value="">Semua Role</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}" {{ request('role_id') == $role->id ? 'selected' : '' }}>
                                {{ $role->nama_role }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="md:col-span-3 flex items-end gap-2">
                    <button type="submit" class="btn-filter-primary flex-1">
                        <i class="fas fa-search mr-1"></i> Filter
                    </button>
                    @if(request()->hasAny(['search', 'role_id']))
                        <a href="{{ route('audit.activity.index', ['tab' => 'last-login']) }}" class="btn-filter-secondary px-4">
                            <i class="fas fa-redo"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="px-6 py-4 bg-slate-50/50 border-b border-slate-100 flex justify-between items-center">
            <span class="text-[11px] font-black text-slate-400 uppercase tracking-widest">Daftar Sesi Login</span>
            <span class="text-[10px] font-bold text-indigo-600 bg-indigo-50 px-3 py-1 rounded-full border border-indigo-100">
                Total: {{ $users->total() }} User
            </span>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse custom-solid-table">
                <thead>
                    <tr class="text-[10px] font-bold text-slate-400 uppercase tracking-wider bg-slate-50 border-b border-slate-100">
                        <th class="px-6 py-4 w-16 text-center">#</th>
                        <th class="px-6 py-4 w-72">Identitas Pengguna</th>
                        <th class="px-6 py-4 w-40 text-center">Role / Jabatan</th>
                        <th class="px-6 py-4">Kontak Email</th>
                        <th class="px-6 py-4 pr-8 text-right">Aktivitas Terakhir</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($users as $key => $user)
                    <tr class="hover:bg-slate-50/50 transition-all duration-200">
                        <td class="px-6 py-4 text-center">
                            <span class="text-xs font-bold text-slate-300 leading-none">
                                {{ $users->firstItem() + $key }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-xs border border-indigo-100 shadow-sm">
                                    {{ strtoupper(substr($user->nama, 0, 1)) }}
                                </div>
                                <div class="flex flex-col min-w-0">
                                    <span class="text-sm font-bold text-slate-700 leading-tight truncate">{{ $user->nama }}</span>
                                    <span class="text-[10px] font-mono text-indigo-500 mt-1 tracking-tight uppercase">{{ $user->username }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="custom-badge-base bg-slate-100 text-slate-600 border border-slate-200 whitespace-nowrap">
                                {{ $user->role->nama_role ?? '-' }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2 text-xs text-slate-500">
                                <i class="far fa-envelope text-[10px] text-slate-300"></i>
                                <span class="font-medium italic truncate">{{ $user->email }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-right pr-8">
                            @if($user->last_login_at)
                                <div class="flex flex-col items-end">
                                    <span class="text-sm font-bold text-slate-700 leading-none">{{ formatRelative($user->last_login_at) }}</span>
                                    <span class="text-[10px] font-mono text-slate-400 uppercase tracking-tighter mt-1.5">
                                        {{ formatDateTime($user->last_login_at, 'd M Y, H:i:s') }}
                                    </span>
                                </div>
                            @else
                                <span class="text-[10px] font-bold text-slate-300 uppercase tracking-widest italic leading-none">Belum pernah login</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-20 text-center text-slate-400 bg-white">
                            <div class="flex flex-col items-center opacity-40">
                                <i class="fas fa-users-slash text-4xl mb-4 text-slate-300"></i>
                                <p class="text-sm font-bold uppercase tracking-widest">Tidak ada data pengguna ditemukan</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
        <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/30">
            <div class="flex justify-center">
                {{ $users->appends(request()->all())->links('pagination::bootstrap-4') }}
            </div>
        </div>
        @endif
    </div>
</div>

<style>
    /* Sinkronisasi Lebar dan Presisi Form */
    .custom-input-clean, .custom-select-clean {
        border: 1px solid #e2e8f0;
        border-radius: 0.75rem;
        padding: 0.65rem 0.8rem;
        font-size: 0.85rem;
        background-color: #ffffff;
        color: #1e293b;
        transition: all 0.2s;
        outline: none;
    }
    .custom-input-clean:focus, .custom-select-clean:focus {
        border-color: #4f46e5;
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.08);
    }
    .btn-filter-primary {
        background-color: #4f46e5;
        color: #ffffff;
        border-radius: 0.75rem;
        font-weight: 800;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        padding: 0.7rem 1rem;
        border: none;
        transition: all 0.2s ease;
    }
    .btn-filter-primary:hover {
        background-color: #4338ca;
        transform: translateY(-1px);
        box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.2);
    }
    .btn-filter-secondary {
        background-color: #f1f5f9;
        color: #64748b;
        border-radius: 0.75rem;
        font-weight: 800;
        padding: 0.7rem 1rem;
        transition: all 0.2s;
        text-decoration: none !important;
        display: inline-flex;
        align-items: center;
    }
    .btn-filter-secondary:hover {
        background-color: #e2e8f0;
        color: #1e293b;
    }
    /* Typography & Consistency */
    .custom-solid-table thead th { vertical-align: middle; }
    .custom-solid-table tbody td { vertical-align: middle; border-bottom: 1px solid #f1f5f9; }
    .custom-badge-base {
        display: inline-flex;
        align-items: center;
        padding: 0.2rem 0.6rem;
        border-radius: 0.5rem;
        font-size: 10px;
        font-weight: 800;
        text-transform: uppercase;
    }
</style>