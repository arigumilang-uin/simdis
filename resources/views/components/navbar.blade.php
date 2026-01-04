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
        {{-- Desktop Trigger --}}
        <button type="button" class="hidden sm:flex group items-center gap-3 pl-4 pr-3 py-1.5 rounded-full border border-gray-200 bg-white hover:border-gray-300 hover:bg-gray-50 transition-all duration-200" @click="toggle()">
            <div class="flex flex-col items-end leading-none">
                <span class="text-sm font-semibold text-gray-700 tracking-tight group-hover:text-gray-900">
                    {{ Str::limit(Auth::user()->username ?? 'User', 15) }}
                </span>
                <span class="text-[10px] uppercase tracking-wider font-medium text-gray-400 mt-0.5 group-hover:text-primary-600">
                    {{ Auth::user()->effectiveRoleName() ?? 'User' }}
                </span>
            </div>
            
            <div class="h-8 w-px bg-gray-200 mx-1"></div>
            
            <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-gray-500 group-hover:bg-primary-50 group-hover:text-primary-600 transition-colors">
                <x-ui.icon name="chevron-down" size="16" class="transition-transform duration-200" ::class="{ 'rotate-180': open }" />
            </div>
        </button>

        {{-- Mobile Trigger --}}
        <button type="button" class="sm:hidden flex items-center justify-center w-10 h-10 rounded-full bg-white border border-gray-200 text-gray-600 hover:bg-gray-50" @click="toggle()">
             <x-ui.icon name="user" size="20" />
        </button>
        
        <div class="dropdown-menu mt-2 w-56 transform origin-top-right right-0 z-50" @click.away="close()" x-show="open" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100" x-transition:leave-end="transform opacity-0 scale-95" x-cloak>
            <div class="px-4 py-3 bg-gray-50 border-b border-gray-100 rounded-t-lg">
                <p class="font-semibold text-gray-800">{{ Auth::user()->username ?? 'User' }}</p>
                <div class="flex items-center gap-1.5 mt-1">
                    <div class="w-1.5 h-1.5 rounded-full bg-green-500"></div>
                    <p class="text-xs text-gray-500">{{ Auth::user()->effectiveRoleName() ?? 'User' }}</p>
                </div>
            </div>
            
            <div class="p-1.5">
                <a href="{{ route('account.edit') }}" class="flex items-center gap-2 px-3 py-2 text-sm font-medium text-gray-700 rounded-md hover:bg-gray-100 hover:text-primary-600 transition-colors">
                    <x-ui.icon name="user" size="16" />
                    <span>Profil Saya</span>
                </a>
            </div>
            
            <div class="h-px bg-gray-100 my-0.5 mx-1.5"></div>
            
            <div class="p-1.5">
                {{-- Logout Button using Native Form Attribute --}}
                <button type="submit" form="logout-form-navbar" class="w-full flex items-center gap-2 px-3 py-2 text-sm font-medium text-red-600 rounded-md hover:bg-red-50 hover:text-red-700 transition-colors cursor-pointer" onclick="return confirm('Apakah Anda yakin ingin keluar?')">
                    <x-ui.icon name="log-out" size="16" />
                    <span>Keluar</span>
                </button>
            </div>
        </div>
    </div>
    
    {{-- Hidden Logout Form --}}
    <form id="logout-form-navbar" action="{{ route('logout') }}" method="POST" class="hidden" style="display: none;">
        @csrf
    </form>
</div>
