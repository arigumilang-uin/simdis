    {{-- Table --}}
    <div class="card">
        <div class="card-header">
            <span class="card-title">Manajemen Akses Akun</span>
            <span class="badge badge-primary">Total: {{ $users->total() ?? 0 }} Pengguna</span>
        </div>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th class="w-16 text-center">#</th>
                        <th>User</th>
                        <th class="w-32 text-center">Status</th>
                        <th class="w-48 text-center">Kontrol Akses</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users ?? [] as $key => $user)
                        <tr>
                            <td class="text-center text-gray-400">{{ $users->firstItem() + $key }}</td>
                            <td>
                                <div class="font-bold text-gray-700">{{ $user->username }}</div>
                                <div class="text-xs text-gray-500">{{ $user->nama }}</div>
                            </td>
                            <td class="text-center">
                                @if($user->is_active)
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-emerald-50 text-emerald-600 text-[9px] font-bold uppercase border border-emerald-100">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-gray-100 text-gray-400 text-[9px] font-bold uppercase border border-gray-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-gray-300"></span> Off
                                    </span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if(Auth::id() != $user->id)
                                    <form action="{{ route('users.toggle-active', $user->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" 
                                                class="btn btn-outline text-xs w-full {{ $user->is_active ? 'hover:bg-red-50 hover:text-red-600 hover:border-red-200' : 'hover:bg-emerald-50 hover:text-emerald-600 hover:border-emerald-200' }}"
                                                onclick="return confirm('Yakin ingin {{ $user->is_active ? 'menonaktifkan' : 'mengaktifkan' }} akun {{ $user->nama }}?')">
                                            @if($user->is_active)
                                                <x-ui.icon name="user-x" size="14" class="mr-1" />
                                                Suspend
                                            @else
                                                <x-ui.icon name="user-check" size="14" class="mr-1" />
                                                Activate
                                            @endif
                                        </button>
                                    </form>
                                @else
                                    <div class="px-3 py-2 rounded-xl bg-indigo-50 text-indigo-400 text-[10px] font-bold uppercase">
                                        <x-ui.icon name="shield" size="12" class="inline mr-1" />
                                        My Account
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">
                                <x-ui.empty-state 
                                    icon="users" 
                                    title="Tidak Ada Data" 
                                    description="Tidak ada data pengguna ditemukan." 
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if(isset($users) && $users->hasPages())
        <div class="card-body border-t border-gray-100">
            {{ $users->appends(request()->except('render_partial'))->links() }}
        </div>
        @endif
    </div>
