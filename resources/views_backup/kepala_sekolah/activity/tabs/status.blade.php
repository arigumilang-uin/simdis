<div class="animate-in fade-in duration-500">
    
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
            <h5 class="text-[10px] font-black uppercase tracking-[0.15em] text-slate-400 m-0 flex items-center gap-2">
                <i class="fas fa-filter text-indigo-500 text-xs"></i> Panel Filter Pengguna
            </h5>
        </div>
        <div class="p-6">
            <form method="GET" action="{{ route('audit.activity.index') }}" class="grid grid-cols-1 md:grid-cols-12 gap-4">
                <input type="hidden" name="tab" value="status">
                
                <div class="md:col-span-2">
                    <label class="text-[10px] font-bold text-slate-400 uppercase mb-1.5 block tracking-tight">Status</label>
                    <select name="status" class="custom-select-clean w-full">
                        <option value="">Semua Status</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Aktif</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                    </select>
                </div>

                <div class="md:col-span-3">
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

                <div class="md:col-span-4">
                    <label class="text-[10px] font-bold text-slate-400 uppercase mb-1.5 block tracking-tight">Pencarian</label>
                    <input type="text" name="search" class="custom-input-clean w-full" 
                           placeholder="Nama, Username, Email..." value="{{ request('search') }}">
                </div>

                <div class="md:col-span-3 flex items-end gap-2">
                    <button type="submit" class="btn-filter-primary flex-1">
                        <i class="fas fa-search mr-1"></i> Cari
                    </button>
                    @if(request()->hasAny(['search', 'role_id', 'status']))
                        <a href="{{ route('audit.activity.index', ['tab' => 'status']) }}" class="btn-filter-secondary px-4">
                            <i class="fas fa-redo"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="px-6 py-4 bg-slate-50/50 border-b border-slate-100 flex justify-between items-center">
            <span class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Manajemen Akses Akun</span>
            <span class="text-[10px] font-bold text-indigo-600 bg-indigo-50 px-3 py-1 rounded-full border border-indigo-100 shadow-sm">
                Total: {{ $users->total() }} Pengguna
            </span>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse custom-solid-table">
                <thead>
                    <tr class="text-[10px] font-bold text-slate-400 uppercase tracking-wider bg-slate-50 border-b border-slate-100">
                        <th class="px-6 py-4 w-16 text-center">#</th>
                        <th class="px-6 py-4 w-72">Identitas & Jabatan</th>
                        <th class="px-6 py-4">Kontak Email</th>
                        <th class="px-6 py-4 w-32 text-center">Status</th>
                        <th class="px-6 py-4 pr-8 text-center w-48">Kontrol Akses</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($users as $key => $user)
                    <tr class="hover:bg-slate-50/50 transition-all duration-200">
                        <td class="px-6 py-4 text-center">
                            <span class="text-xs font-bold text-slate-300">{{ $users->firstItem() + $key }}</span>
                        </td>

                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-xs border border-indigo-100 shadow-sm">
                                    {{ strtoupper(substr($user->nama, 0, 1)) }}
                                </div>
                                <div class="flex flex-col min-w-0">
                                    <span class="text-sm font-bold text-slate-700 leading-tight truncate">{{ $user->nama }}</span>
                                    <span class="text-[10px] font-mono text-indigo-500 mt-1 tracking-tight uppercase">{{ $user->username }}</span>
                                    <span class="text-[9px] text-slate-400 font-bold uppercase mt-0.5 tracking-tighter">{{ $user->role->nama_role ?? '-' }}</span>
                                </div>
                            </div>
                        </td>

                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2 text-xs text-slate-500">
                                <i class="far fa-envelope text-[10px] text-slate-300"></i>
                                <span class="font-medium italic">{{ $user->email }}</span>
                            </div>
                        </td>

                        <td class="px-6 py-4 text-center">
                            @if($user->is_active)
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-emerald-50 text-emerald-600 text-[9px] font-black uppercase border border-emerald-100">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Aktif
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-slate-100 text-slate-400 text-[9px] font-black uppercase border border-slate-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-slate-300"></span> Off
                                </span>
                            @endif
                        </td>

                        <td class="px-6 py-4 text-center pr-8">
                            @if(Auth::id() != $user->id)
                                <form action="{{ route('users.toggle-active', $user->id) }}" method="POST" class="m-0">
                                    @csrf
                                    <button type="submit" 
                                            class="btn-status-toggle w-full {{ $user->is_active ? 'hover:bg-rose-50 hover:text-rose-600 hover:border-rose-200' : 'hover:bg-emerald-50 hover:text-emerald-600 hover:border-emerald-200' }}"
                                            onclick="return confirm('Yakin ingin {{ $user->is_active ? 'menonaktifkan' : 'mengaktifkan' }} akun {{ $user->nama }}?')">
                                        <i class="fas {{ $user->is_active ? 'fa-user-slash' : 'fa-user-check' }} mr-2"></i>
                                        {{ $user->is_active ? 'Suspend' : 'Activate' }}
                                    </button>
                                </form>
                            @else
                                <div class="px-3 py-2 rounded-xl bg-indigo-50/50 border border-indigo-100/50 text-indigo-400 text-[10px] font-black uppercase tracking-widest cursor-default">
                                    <i class="fas fa-user-shield mr-1"></i> My Account
                                </div>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-24 text-center">
                            <div class="flex flex-col items-center">
                                <i class="fas fa-user-clock text-4xl text-slate-100 mb-4"></i>
                                <p class="text-sm font-bold text-slate-300 uppercase tracking-[0.2em]">Tidak Ada Data Pengguna</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
        <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/30 flex justify-center">
            {{ $users->appends(request()->all())->links('pagination::bootstrap-4') }}
        </div>
        @endif
    </div>
</div>

<style>
    /* SOLID TABLE PRECISION */
    .btn-status-toggle {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.6rem 0;
        border-radius: 0.75rem;
        font-size: 10px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        background-color: #ffffff;
        border: 1px solid #e2e8f0;
        color: #64748b;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        cursor: pointer;
    }

    /* CSS Sync with Master */
    .custom-input-clean, .custom-select-clean {
        height: 42px;
        border: 1px solid #e2e8f0;
        border-radius: 0.75rem;
        padding: 0 1rem;
        font-size: 0.85rem;
        background-color: #ffffff;
        color: #1e293b;
        transition: 0.2s;
        outline: none;
    }
    .btn-filter-primary {
        height: 42px;
        background-color: #4f46e5;
        color: white;
        border-radius: 0.75rem;
        font-weight: 800;
        font-size: 0.75rem;
        text-transform: uppercase;
        border: none;
    }
</style>