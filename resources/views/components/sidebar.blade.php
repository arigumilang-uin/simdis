{{-- Sidebar Component --}}
@php
    $user = Auth::user();
    $role = $user?->effectiveRoleName() ?? $user?->role?->nama_role ?? 'Guest';
    $isDeveloper = $user?->isDeveloper() ?? false;
    $override = session('developer_role_override');
@endphp

<!-- Brand (Hidden on mobile, shown in navbar instead) -->
<div class="sidebar-brand hidden lg:flex">
    <div class="sidebar-brand-logo">
        <picture>
            <source srcset="{{ asset('assets/images/logo_smk.webp') }}" type="image/webp">
            <img src="{{ asset('assets/images/logo_smk.png') }}" 
                 alt="Logo SMK" 
                 class="w-9 h-9 object-contain"
                 loading="eager">
        </picture>
    </div>
    <div class="sidebar-brand-text">
        <div>SIMDIS</div>
        <div class="text-xs font-normal opacity-70">SMKN 1 Lubuk Dalam</div>
    </div>
</div>

<!-- User Info -->
@auth
<div class="sidebar-user">
    <div class="sidebar-user-avatar">
        {{ strtoupper(substr($user->username ?? 'U', 0, 1)) }}
    </div>
    <div class="sidebar-user-info">
        <div class="sidebar-user-name">{{ Str::limit($user->username ?? 'User', 18) }}</div>
        <div class="sidebar-user-role">{{ $role }}</div>
    </div>
</div>
@endauth

<!-- Navigation -->
<nav class="sidebar-nav">
    
    {{-- Developer Switch Role (if applicable) --}}
    @if($isDeveloper)
        <div class="sidebar-section">Developer</div>
        <ul class="sidebar-menu">
            <li class="sidebar-menu-item">
                <a href="{{ route('dashboard.developer') }}" class="sidebar-menu-link {{ Request::routeIs('dashboard.developer') ? 'active' : '' }}">
                    <x-ui.icon name="terminal" class="sidebar-menu-icon" />
                    <span>Console</span>
                </a>
            </li>
        </ul>
    @endif
    
    {{-- Dashboard --}}
    @unless($isDeveloper && !$override)
        <div class="sidebar-section">Menu Utama</div>
        <ul class="sidebar-menu">
            @php
                $dashRoute = match(true) {
                    $isDeveloper && !$override => route('dashboard.developer'),
                    in_array($role, ['Operator Sekolah', 'Waka Kesiswaan']) => route('dashboard.admin'),
                    $role === 'Kepala Sekolah' => route('dashboard.kepsek'),
                    $role === 'Wali Kelas' => route('dashboard.walikelas'),
                    $role === 'Kaprodi' => route('dashboard.kaprodi'),
                    $role === 'Wali Murid' => route('dashboard.wali_murid'),
                    $role === 'Waka Sarana' => route('dashboard.waka-sarana'),
                    default => route('dashboard.admin')
                };
            @endphp
            <li class="sidebar-menu-item">
                <a href="{{ $dashRoute }}" class="sidebar-menu-link {{ Request::is('dashboard*') ? 'active' : '' }}">
                    <x-ui.icon name="grid" class="sidebar-menu-icon" />
                    <span>Dashboard</span>
                </a>
            </li>
        </ul>
    @endunless
    
    {{-- Operational Menu (Guru, Wali Kelas, Waka, Kaprodi, Waka Sarana) --}}
    @if(in_array($role, ['Guru', 'Wali Kelas', 'Waka Kesiswaan', 'Kaprodi', 'Waka Sarana']) || $isDeveloper)
        <div class="sidebar-section">Operasional</div>
        <ul class="sidebar-menu">
            <li class="sidebar-menu-item">
                <a href="{{ route('riwayat.create') }}" class="sidebar-menu-link {{ Request::routeIs('riwayat.create', 'pelanggaran.create') ? 'active' : '' }}">
                    <x-ui.icon name="edit" class="sidebar-menu-icon" />
                    <span>Catat Pelanggaran</span>
                </a>
            </li>
            <li class="sidebar-menu-item">
                <a href="{{ route('my-riwayat.index') }}" class="sidebar-menu-link {{ Request::routeIs('my-riwayat.*', 'riwayat.my') ? 'active' : '' }}">
                    <x-ui.icon name="file-text" class="sidebar-menu-icon" />
                    <span>Riwayat Saya</span>
                </a>
            </li>
        </ul>
    @endif
    
    {{-- Monitoring Menu (Operator, Waka, Wali Kelas, Kaprodi, Kepala Sekolah) --}}
    @if(in_array($role, ['Operator Sekolah', 'Waka Kesiswaan', 'Wali Kelas', 'Kaprodi', 'Kepala Sekolah']) || $isDeveloper)
        <div class="sidebar-section">Monitoring</div>
        <ul class="sidebar-menu">
            @if(in_array($role, ['Operator Sekolah', 'Waka Kesiswaan', 'Wali Kelas', 'Kaprodi']) || $isDeveloper)
                <li class="sidebar-menu-item">
                    <a href="{{ route('siswa.index') }}" class="sidebar-menu-link {{ Request::routeIs('siswa.*') && !Request::routeIs('siswa.transfer*') ? 'active' : '' }}">
                        <x-ui.icon name="users" class="sidebar-menu-icon" />
                        <span>{{ $role === 'Wali Kelas' ? 'Siswa Kelas' : 'Data Siswa' }}</span>
                    </a>
                </li>
            @endif
            
            <li class="sidebar-menu-item">
                <a href="{{ route('riwayat.index') }}" class="sidebar-menu-link {{ Request::routeIs('riwayat.index', 'riwayat.show', 'riwayat.edit') ? 'active' : '' }}">
                    <x-ui.icon name="list" class="sidebar-menu-icon" />
                    <span>Log Pelanggaran</span>
                </a>
            </li>
            
            @if(in_array($role, ['Wali Kelas', 'Kaprodi', 'Waka Kesiswaan', 'Kepala Sekolah', 'Operator Sekolah']) || $isDeveloper)
                <li class="sidebar-menu-item">
                    <a href="{{ route('tindak-lanjut.index') }}" class="sidebar-menu-link {{ Request::routeIs('tindak-lanjut.*') ? 'active' : '' }}">
                        <x-ui.icon name="clipboard" class="sidebar-menu-icon" />
                        <span>Daftar Kasus</span>
                    </a>
                </li>
            @endif
            
            @if(in_array($role, ['Wali Kelas', 'Kaprodi', 'Waka Kesiswaan', 'Kepala Sekolah']) || $isDeveloper)
                <li class="sidebar-menu-item">
                    <a href="{{ route('pembinaan.index') }}" class="sidebar-menu-link {{ Request::routeIs('pembinaan.*') ? 'active' : '' }}">
                        <x-ui.icon name="user-check" class="sidebar-menu-icon" />
                        <span>Siswa Pembinaan</span>
                    </a>
                </li>
            @endif
            
            @if(in_array($role, ['Waka Kesiswaan', 'Kepala Sekolah']) || $isDeveloper)
                <li class="sidebar-menu-item">
                    <a href="{{ in_array($role, ['Kepala Sekolah', 'Waka Kesiswaan']) ? route('kepala-sekolah.data.jurusan') : route('jurusan.index') }}" class="sidebar-menu-link {{ Request::is('*jurusan*') ? 'active' : '' }}">
                        <x-ui.icon name="hexagon" class="sidebar-menu-icon" />
                        <span>Data Jurusan</span>
                    </a>
                </li>
                <li class="sidebar-menu-item">
                    <a href="{{ in_array($role, ['Kepala Sekolah', 'Waka Kesiswaan']) ? route('kepala-sekolah.data.kelas') : route('kelas.index') }}" class="sidebar-menu-link {{ Request::is('*kelas*') ? 'active' : '' }}">
                        <x-ui.icon name="layout" class="sidebar-menu-icon" />
                        <span>Data Kelas</span>
                    </a>
                </li>
            @endif
        </ul>
    @endif
    
    {{-- Administration Menu (Operator Sekolah) --}}
    @if($role === 'Operator Sekolah' || $isDeveloper)
        <div class="sidebar-section">Administrasi</div>
        <ul class="sidebar-menu">
            <li class="sidebar-menu-item">
                <a href="{{ route('users.index') }}" class="sidebar-menu-link {{ Request::routeIs('users.*') ? 'active' : '' }}">
                    <x-ui.icon name="settings" class="sidebar-menu-icon" />
                    <span>Manajemen User</span>
                </a>
            </li>
            <li class="sidebar-menu-item">
                <a href="{{ route('siswa.transfer') }}" class="sidebar-menu-link {{ Request::routeIs('siswa.transfer') ? 'active' : '' }}">
                    <x-ui.icon name="arrow-right" class="sidebar-menu-icon" />
                    <span>Kenaikan Kelas</span>
                </a>
            </li>
            <li class="sidebar-menu-item">
                <a href="{{ route('frequency-rules.index') }}" class="sidebar-menu-link {{ Request::routeIs('frequency-rules.*') ? 'active' : '' }}">
                    <x-ui.icon name="book" class="sidebar-menu-icon" />
                    <span>Aturan & Poin</span>
                </a>
            </li>
            <li class="sidebar-menu-item">
                <a href="{{ route('pembinaan-internal-rules.index') }}" class="sidebar-menu-link {{ Request::routeIs('pembinaan-internal-rules.*') ? 'active' : '' }}">
                    <x-ui.icon name="shield" class="sidebar-menu-icon" />
                    <span>Pembinaan Internal</span>
                </a>
            </li>
            <li class="sidebar-menu-item">
                <a href="{{ route('audit.activity.index') }}" class="sidebar-menu-link {{ Request::routeIs('audit.*') ? 'active' : '' }}">
                    <x-ui.icon name="activity" class="sidebar-menu-icon" />
                    <span>Audit Log</span>
                </a>
            </li>
            <li class="sidebar-menu-item">
                <a href="{{ route('jurusan.index') }}" class="sidebar-menu-link {{ Request::is('jurusan*') && !Request::is('*data*') ? 'active' : '' }}">
                    <x-ui.icon name="hexagon" class="sidebar-menu-icon" />
                    <span>Master Jurusan</span>
                </a>
            </li>
            <li class="sidebar-menu-item">
                <a href="{{ route('kelas.index') }}" class="sidebar-menu-link {{ Request::is('kelas*') && !Request::is('*data*') ? 'active' : '' }}">
                    <x-ui.icon name="layout" class="sidebar-menu-icon" />
                    <span>Master Kelas</span>
                </a>
            </li>
        </ul>
    @endif
    
    {{-- Kesiswaan Menu (Waka Kesiswaan) --}}
    @if($role === 'Waka Kesiswaan' || $isDeveloper)
        <div class="sidebar-section">Kesiswaan</div>
        <ul class="sidebar-menu">
            <li class="sidebar-menu-item">
                <a href="{{ route('frequency-rules.index') }}" class="sidebar-menu-link {{ Request::routeIs('frequency-rules.*') ? 'active' : '' }}">
                    <x-ui.icon name="alert-circle" class="sidebar-menu-icon" />
                    <span>Aturan Tata Tertib</span>
                </a>
            </li>
            <li class="sidebar-menu-item">
                <a href="{{ route('pembinaan-internal-rules.index') }}" class="sidebar-menu-link {{ Request::routeIs('pembinaan-internal-rules.*') ? 'active' : '' }}">
                    <x-ui.icon name="shield" class="sidebar-menu-icon" />
                    <span>Pembinaan Internal</span>
                </a>
            </li>
        </ul>
    @endif
    
    {{-- Kepala Sekolah Menu --}}
    @if($role === 'Kepala Sekolah' || $isDeveloper)
        @php
            $pendingCount = \App\Models\TindakLanjut::where('status', 'Menunggu Persetujuan')->count();
        @endphp
        <div class="sidebar-section">Kepala Sekolah</div>
        <ul class="sidebar-menu">
            <li class="sidebar-menu-item">
                <a href="{{ route('kepala-sekolah.approvals.index') }}" class="sidebar-menu-link {{ Request::routeIs('kepala-sekolah.approvals.*') ? 'active' : '' }}">
                    <x-ui.icon name="check-square" class="sidebar-menu-icon" />
                    <span>Persetujuan</span>
                    @if($pendingCount > 0)
                        <span class="sidebar-menu-badge">{{ $pendingCount }}</span>
                    @endif
                </a>
            </li>
            <li class="sidebar-menu-item">
                <a href="{{ route('kepala-sekolah.reports.index') }}" class="sidebar-menu-link {{ Request::routeIs('kepala-sekolah.reports.*') ? 'active' : '' }}">
                    <x-ui.icon name="file-text" class="sidebar-menu-icon" />
                    <span>Laporan</span>
                </a>
            </li>
            <li class="sidebar-menu-item">
                <a href="{{ route('kepala-sekolah.siswa-perlu-pembinaan.index') }}" class="sidebar-menu-link {{ Request::routeIs('kepala-sekolah.siswa-perlu-pembinaan.*') ? 'active' : '' }}">
                    <x-ui.icon name="user-check" class="sidebar-menu-icon" />
                    <span>Siswa Pembinaan</span>
                </a>
            </li>
        </ul>
    @endif
    
    {{-- Settings (All Users) --}}
    <div class="sidebar-section">Pengaturan</div>
    <ul class="sidebar-menu">
        <li class="sidebar-menu-item">
            <a href="{{ route('account.edit') }}" class="sidebar-menu-link {{ Request::routeIs('account.*', 'profile.*') ? 'active' : '' }}">
                <x-ui.icon name="user" class="sidebar-menu-icon" />
                <span>Profil Saya</span>
            </a>
        </li>
    </ul>
    
</nav>
