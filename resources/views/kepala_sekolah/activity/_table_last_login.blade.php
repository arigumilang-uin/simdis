    {{-- Table --}}
    <div class="card">
        <div class="card-header">
            <span class="card-title">Daftar Sesi Login</span>
            <span class="badge badge-primary">Total: {{ $users->total() ?? 0 }} User</span>
        </div>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th class="w-16 text-center">#</th>
                        <th>User</th>
                        <th class="w-48 text-left">Terakhir Login</th>
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
                            <td class="text-left">
                                @if($user->last_login_at)
                                    <div class="font-medium text-gray-700">{{ $user->last_login_at->diffForHumans() }}</div>
                                    <div class="text-[10px] text-gray-400 font-mono">{{ $user->last_login_at->format('d M Y, H:i:s') }}</div>
                                @else
                                    <span class="text-xs text-gray-400 italic">Belum pernah login</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3">
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
