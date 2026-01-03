{{-- Navbar Component --}}
<div class="navbar-left">
    <!-- Mobile Menu Toggle -->
    <button type="button" class="navbar-toggle" @click="toggle()">
        <x-ui.icon name="menu" size="24" />
    </button>
    
    <!-- Mobile Brand (Only visible on mobile) -->
    <div class="flex items-center gap-2 lg:hidden">
        <picture>
            <source srcset="{{ asset('assets/images/logo_smk.webp') }}" type="image/webp">
            <img src="{{ asset('assets/images/logo_smk.png') }}" 
                 alt="Logo SMK" 
                 class="w-8 h-8 object-contain"
                 loading="eager">
        </picture>
        <div class="leading-tight">
            <div class="font-semibold text-gray-800 text-sm">SIMDIS</div>
            <div class="text-[10px] text-gray-500">SMKN 1 Lubuk Dalam</div>
        </div>
    </div>
    
    <!-- Breadcrumb / School Year (Desktop only) -->
    <div class="navbar-title hidden lg:block">
        <span class="text-gray-400">Tahun Ajaran:</span>
        <span class="font-medium text-gray-700">{{ school_year() ?? date('Y') . '/' . (date('Y') + 1) }}</span>
    </div>
</div>

<div class="navbar-right">
    {{-- Notifications (Kepala Sekolah Only) --}}
    @if(Auth::check() && Auth::user()->hasRole('Kepala Sekolah'))
        @php
            $unreadCount = Auth::user()->unreadNotifications()->count();
            $notifications = Auth::user()->unreadNotifications()->limit(5)->get();
        @endphp
        <div x-data="dropdown" class="dropdown relative">
            <button type="button" class="navbar-btn" @click="toggle()">
                <x-ui.icon name="bell" size="20" />
                @if($unreadCount > 0)
                    <span class="navbar-btn-badge"></span>
                @endif
            </button>
            
            <div class="dropdown-menu" style="width: 320px;" @click.away="close()" x-show="open" x-transition x-cloak>
                <div class="px-4 py-3 border-b border-gray-100">
                    <h4 class="font-semibold text-gray-800">Notifikasi</h4>
                    <p class="text-xs text-gray-500">{{ $unreadCount }} belum dibaca</p>
                </div>
                
                <div class="max-h-64 overflow-y-auto">
                    @forelse($notifications as $notification)
                        <a href="{{ $notification->data['url'] ?? '#' }}" class="dropdown-item !py-3">
                            <div class="w-8 h-8 bg-amber-100 text-amber-600 rounded-lg flex items-center justify-center shrink-0">
                                <x-ui.icon name="mail" size="14" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm text-gray-800 truncate">{{ $notification->data['siswa_nama'] ?? 'Notifikasi Baru' }}</p>
                                <p class="text-xs text-gray-400">{{ $notification->created_at->diffForHumans() }}</p>
                            </div>
                        </a>
                    @empty
                        <div class="py-8 text-center text-gray-400">
                            <x-ui.icon name="bell-off" size="32" class="mx-auto mb-2 opacity-50" />
                            <p class="text-sm">Tidak ada notifikasi</p>
                        </div>
                    @endforelse
                </div>
                
                @if($unreadCount > 0)
                    <div class="p-2 border-t border-gray-100">
                        <a href="{{ route('kepala-sekolah.approvals.index') }}" class="block text-center text-sm text-primary-600 hover:text-primary-700 font-medium py-2 rounded-lg hover:bg-gray-50">
                            Lihat Semua
                        </a>
                    </div>
                @endif
            </div>
        </div>
    @endif
    
    {{-- User Dropdown --}}
    <div x-data="dropdown" class="dropdown relative">
        <button type="button" class="navbar-user" @click="toggle()">
            <div class="navbar-user-avatar">
                {{ strtoupper(substr(Auth::user()->username ?? 'U', 0, 1)) }}
            </div>
            <span class="navbar-user-name hidden sm:block">{{ Str::limit(Auth::user()->username ?? 'User', 12) }}</span>
            <x-ui.icon name="chevron-down" size="16" class="text-gray-400 hidden sm:block" />
        </button>
        
        <div class="dropdown-menu" @click.away="close()" x-show="open" x-transition x-cloak>
            <div class="px-4 py-3 border-b border-gray-100">
                <p class="font-medium text-gray-800">{{ Auth::user()->username ?? 'User' }}</p>
                <p class="text-xs text-gray-500">{{ Auth::user()->effectiveRoleName() ?? Auth::user()->role?->nama_role ?? 'User' }}</p>
            </div>
            
            <a href="{{ route('account.edit') }}" class="dropdown-item">
                <x-ui.icon name="user" size="16" />
                <span>Profil Saya</span>
            </a>
            
            <div class="dropdown-divider"></div>
            
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="dropdown-item danger w-full" onclick="return confirm('Keluar dari sistem?')">
                    <x-ui.icon name="log-out" size="16" />
                    <span>Keluar</span>
                </button>
            </form>
        </div>
    </div>
</div>
