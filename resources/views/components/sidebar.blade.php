{{-- Sidebar Component - Clean & Well-Organized --}}
@php
    $user = Auth::user();
    $role = $user?->effectiveRoleName() ?? $user?->role?->nama_role ?? 'Guest';
    $isDeveloper = $user?->isDeveloper() ?? false;
    $override = session('developer_role_override');
    
    // Get user identifier with priority: NIP > NI PPPK > NUPTK > HP
    $userIdentifier = null;
    if ($user) {
        if (!empty($user->nip)) {
            $userIdentifier = $user->nip;
        } elseif (!empty($user->ni_pppk)) {
            $userIdentifier = $user->ni_pppk;
        } elseif (!empty($user->nuptk)) {
            $userIdentifier = $user->nuptk;
        } elseif (!empty($user->no_hp)) {
            $userIdentifier = $user->no_hp;
        }
    }
    
    // Check conditions
    $hasJadwal = $user ? \App\Models\JadwalMengajar::where('user_id', $user->id)->where('is_active', true)->exists() : false;
    $canAccessReport = in_array($role, ['Wali Kelas', 'Kaprodi', 'Waka Kurikulum', 'Waka Kesiswaan', 'Operator Sekolah', 'Kepala Sekolah']) || $isDeveloper;
    $canCatatPelanggaran = in_array($role, ['Guru', 'Wali Kelas', 'Waka Kesiswaan', 'Waka Kurikulum', 'Kaprodi', 'Waka Sarana']) || $isDeveloper;
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
        <div>IDEAL</div>
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
        @if($userIdentifier)
            <div class="sidebar-user-id">{{ $userIdentifier }}</div>
        @endif
        <div class="sidebar-user-name">{{ Str::limit($user->username ?? 'Pengguna', 18) }}</div>
        <div class="sidebar-user-role">{{ $role }}</div>
    </div>
</div>
@endauth

<!-- Navigation -->
<nav class="sidebar-nav">
    
    {{-- ========== DEVELOPER CONSOLE ========== --}}
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
    
    {{-- ========== DASHBOARD ========== --}}
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
                    $role === 'Waka Kurikulum' => route('dashboard.admin'),
                    default => route('dashboard.admin')
                };
            @endphp
            <li class="sidebar-menu-item">
                <a href="{{ $dashRoute }}" class="sidebar-menu-link {{ Request::is('dashboard*') ? 'active' : '' }}">
                    <x-ui.icon name="home" class="sidebar-menu-icon" />
                    <span>Dashboard</span>
                </a>
            </li>
        </ul>
    @endunless
    
    {{-- ========== ABSENSI ========== --}}
    @if($hasJadwal || $canAccessReport || $isDeveloper)
        <div class="sidebar-section">Absensi</div>
        <ul class="sidebar-menu">
            @if($hasJadwal || $isDeveloper)
                <li class="sidebar-menu-item">
                    <a href="{{ route('absensi.index') }}" class="sidebar-menu-link {{ Request::routeIs('absensi.index', 'absensi.grid') ? 'active' : '' }}">
                        <x-ui.icon name="calendar" class="sidebar-menu-icon" />
                        <span>Jadwal Mengajar</span>
                    </a>
                </li>
            @endif
            
            @if($canAccessReport)
                <li class="sidebar-menu-item">
                    <a href="{{ route('absensi.report') }}" class="sidebar-menu-link {{ Request::routeIs('absensi.report') ? 'active' : '' }}">
                        <x-ui.icon name="bar-chart" class="sidebar-menu-icon" />
                        <span>Rekap Absensi</span>
                    </a>
                </li>
            @endif
        </ul>
    @endif
    
    {{-- ========== PELANGGARAN & PEMBINAAN ========== --}}
    @if($canCatatPelanggaran)
        <div class="sidebar-section">Pelanggaran & Pembinaan</div>
        <ul class="sidebar-menu">
            <li class="sidebar-menu-item">
                <a href="{{ route('riwayat.create') }}" class="sidebar-menu-link {{ Request::routeIs('riwayat.create', 'pelanggaran.create') ? 'active' : '' }}">
                    <x-ui.icon name="alert-triangle" class="sidebar-menu-icon" />
                    <span>Catat Pelanggaran</span>
                </a>
            </li>
            <li class="sidebar-menu-item">
                <a href="{{ route('my-riwayat.index') }}" class="sidebar-menu-link {{ Request::routeIs('my-riwayat.*', 'riwayat.my') ? 'active' : '' }}">
                    <x-ui.icon name="file" class="sidebar-menu-icon" />
                    <span>Riwayat Pencatatan Saya</span>
                </a>
            </li>
        </ul>
    @endif
    
    {{-- ========== DATA SISWA (Wali Kelas, Kaprodi) ========== --}}
    @if(in_array($role, ['Wali Kelas', 'Kaprodi']) || $isDeveloper)
        <div class="sidebar-section">{{ $role === 'Wali Kelas' ? 'Siswa Kelas Saya' : 'Data Siswa' }}</div>
        <ul class="sidebar-menu">
            <li class="sidebar-menu-item">
                <a href="{{ route('siswa.index') }}" class="sidebar-menu-link {{ Request::routeIs('siswa.*') && !Request::routeIs('siswa.deleted') ? 'active' : '' }}">
                    <x-ui.icon name="users" class="sidebar-menu-icon" />
                    <span>Daftar Siswa</span>
                </a>
            </li>
        </ul>
    @endif
    
    {{-- ========== MONITORING SISWA ========== --}}
    @if(in_array($role, ['Operator Sekolah', 'Waka Kesiswaan', 'Wali Kelas', 'Kaprodi', 'Kepala Sekolah']) || $isDeveloper)
        <div class="sidebar-section">Monitoring Siswa</div>
        <ul class="sidebar-menu">
            <li class="sidebar-menu-item">
                <a href="{{ route('riwayat.index') }}" class="sidebar-menu-link {{ Request::routeIs('riwayat.index', 'riwayat.show', 'riwayat.edit') ? 'active' : '' }}">
                    <x-ui.icon name="file-text" class="sidebar-menu-icon" />
                    <span>Log Pelanggaran</span>
                </a>
            </li>
            
            @if(in_array($role, ['Wali Kelas', 'Kaprodi', 'Waka Kesiswaan', 'Kepala Sekolah', 'Operator Sekolah']) || $isDeveloper)
                <li class="sidebar-menu-item">
                    <a href="{{ route('tindak-lanjut.index') }}" class="sidebar-menu-link {{ Request::routeIs('tindak-lanjut.*') ? 'active' : '' }}">
                        <x-ui.icon name="alert-circle" class="sidebar-menu-icon" />
                        <span>Daftar Kasus</span>
                    </a>
                </li>
            @endif
            
            @if(in_array($role, ['Wali Kelas', 'Kaprodi', 'Waka Kesiswaan']) || $isDeveloper)
                <li class="sidebar-menu-item">
                    <a href="{{ route('pembinaan.index') }}" class="sidebar-menu-link {{ Request::routeIs('pembinaan.*') ? 'active' : '' }}">
                        <x-ui.icon name="user-check" class="sidebar-menu-icon" />
                        <span>Siswa Pembinaan</span>
                    </a>
                </li>
            @endif
        </ul>
    @endif
    
    {{-- ========== MASTER DATA (Operator, Waka Kurikulum) ========== --}}
    @if(in_array($role, ['Operator Sekolah', 'Waka Kurikulum']) || $isDeveloper)
        <div class="sidebar-section">Master Data</div>
        <ul class="sidebar-menu">
            {{-- Data Siswa with Submenu --}}
            <li class="sidebar-menu-item" x-data="{ open: {{ Request::routeIs('siswa.*') && !Request::routeIs('siswa.deleted') ? 'true' : 'false' }} }">
                <button type="button" @click="open = !open" class="sidebar-menu-link w-full justify-between {{ Request::routeIs('siswa.*') && !Request::routeIs('siswa.deleted') ? 'active' : '' }}">
                    <span class="flex items-center gap-3">
                        <x-ui.icon name="users" class="sidebar-menu-icon" />
                        <span>Data Siswa</span>
                    </span>
                    <x-ui.icon name="chevron-down" size="16" class="transition-transform duration-200" ::class="{ 'rotate-180': open }" />
                </button>
                <ul x-show="open" x-collapse x-cloak class="sidebar-submenu">
                    <li>
                        <a href="{{ route('siswa.index') }}" class="sidebar-submenu-link {{ Request::routeIs('siswa.index', 'siswa.show', 'siswa.edit', 'siswa.create') ? 'active' : '' }}">
                            <x-ui.icon name="list" size="14" />
                            <span>Daftar Siswa</span>
                        </a>
                    </li>
                    @if($role === 'Operator Sekolah' || $isDeveloper)
                    <li>
                        <a href="{{ route('siswa.bulk-create') }}" class="sidebar-submenu-link {{ Request::routeIs('siswa.bulk-create') ? 'active' : '' }}">
                            <x-ui.icon name="upload" size="14" />
                            <span>Import Data</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('siswa.transfer') }}" class="sidebar-submenu-link {{ Request::routeIs('siswa.transfer') ? 'active' : '' }}">
                            <x-ui.icon name="trending-up" size="14" />
                            <span>Kenaikan Kelas</span>
                        </a>
                    </li>
                    @endif
                </ul>
            </li>
            
            @if($role === 'Operator Sekolah' || $isDeveloper)
                {{-- Data Pengguna with Submenu --}}
                <li class="sidebar-menu-item" x-data="{ open: {{ Request::routeIs('users.*') && !Request::routeIs('users.trash') || (Request::routeIs('audit.activity.index') && in_array(request('tab'), ['last-login', 'status'])) ? 'true' : 'false' }} }">
                    <button type="button" @click="open = !open" class="sidebar-menu-link w-full justify-between {{ Request::routeIs('users.*') && !Request::routeIs('users.trash') || (Request::routeIs('audit.activity.index') && in_array(request('tab'), ['last-login', 'status'])) ? 'active' : '' }}">
                        <span class="flex items-center gap-3">
                            <x-ui.icon name="user" class="sidebar-menu-icon" />
                            <span>Data Pengguna</span>
                        </span>
                        <x-ui.icon name="chevron-down" size="16" class="transition-transform duration-200" ::class="{ 'rotate-180': open }" />
                    </button>
                    <ul x-show="open" x-collapse x-cloak class="sidebar-submenu">
                        <li>
                            <a href="{{ route('users.index') }}" class="sidebar-submenu-link {{ Request::routeIs('users.*') && !Request::routeIs('users.trash') ? 'active' : '' }}">
                                <x-ui.icon name="list" size="14" />
                                <span>Daftar Pengguna</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('audit.activity.index', ['tab' => 'last-login']) }}" class="sidebar-submenu-link {{ Request::routeIs('audit.activity.index') && request('tab') == 'last-login' ? 'active' : '' }}">
                                <x-ui.icon name="clock" size="14" />
                                <span>Log Login Terakhir</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('audit.activity.index', ['tab' => 'status']) }}" class="sidebar-submenu-link {{ Request::routeIs('audit.activity.index') && request('tab') == 'status' ? 'active' : '' }}">
                                <x-ui.icon name="shield" size="14" />
                                <span>Status Akun</span>
                            </a>
                        </li>
                    </ul>
                </li>
            @endif
            
            <li class="sidebar-menu-item">
                <a href="{{ route('jurusan.index') }}" class="sidebar-menu-link {{ Request::routeIs('jurusan.*') && !Request::routeIs('jurusan.trash') ? 'active' : '' }}">
                    <x-ui.icon name="hexagon" class="sidebar-menu-icon" />
                    <span>Data Jurusan</span>
                </a>
            </li>
            <li class="sidebar-menu-item">
                <a href="{{ route('konsentrasi.index') }}" class="sidebar-menu-link {{ Request::routeIs('konsentrasi.*') && !Request::routeIs('konsentrasi.trash') ? 'active' : '' }}">
                    <x-ui.icon name="layers" class="sidebar-menu-icon" />
                    <span>Data Konsentrasi</span>
                </a>
            </li>
            <li class="sidebar-menu-item">
                <a href="{{ route('kelas.index') }}" class="sidebar-menu-link {{ Request::routeIs('kelas.*') && !Request::routeIs('kelas.trash') ? 'active' : '' }}">
                    <x-ui.icon name="layout" class="sidebar-menu-icon" />
                    <span>Data Kelas</span>
                </a>
            </li>
        </ul>
    @endif
    
    {{-- ========== KURIKULUM & JADWAL (Operator, Waka Kurikulum) ========== --}}
    @if(in_array($role, ['Operator Sekolah', 'Waka Kurikulum']) || $isDeveloper)
        <div class="sidebar-section">Kurikulum & Jadwal</div>
        <ul class="sidebar-menu">
            <li class="sidebar-menu-item">
                <a href="{{ route('admin.kurikulum.index') }}" class="sidebar-menu-link {{ Request::routeIs('admin.kurikulum.*') && !Request::routeIs('admin.kurikulum.trash') ? 'active' : '' }}">
                    <x-ui.icon name="book" class="sidebar-menu-icon" />
                    <span>Kurikulum</span>
                </a>
            </li>
            <li class="sidebar-menu-item">
                <a href="{{ route('admin.periode-semester.index') }}" class="sidebar-menu-link {{ Request::routeIs('admin.periode-semester.*') && !Request::routeIs('admin.periode-semester.trash') ? 'active' : '' }}">
                    <x-ui.icon name="calendar" class="sidebar-menu-icon" />
                    <span>Periode Semester</span>
                </a>
            </li>
            <li class="sidebar-menu-item">
                <a href="{{ route('admin.mata-pelajaran.index') }}" class="sidebar-menu-link {{ Request::routeIs('admin.mata-pelajaran.*') ? 'active' : '' }}">
                    <x-ui.icon name="book" class="sidebar-menu-icon" />
                    <span>Mata Pelajaran</span>
                </a>
            </li>
            <li class="sidebar-menu-item">
                <a href="{{ route('admin.template-jam.index') }}" class="sidebar-menu-link {{ Request::routeIs('admin.template-jam.*') ? 'active' : '' }}">
                    <x-ui.icon name="clock" class="sidebar-menu-icon" />
                    <span>Template Jam</span>
                </a>
            </li>
            <li class="sidebar-menu-item">
                <a href="{{ route('admin.jadwal-mengajar.index') }}" class="sidebar-menu-link {{ Request::routeIs('admin.jadwal-mengajar.*') ? 'active' : '' }}">
                    <x-ui.icon name="grid" class="sidebar-menu-icon" />
                    <span>Jadwal Mengajar</span>
                </a>
            </li>
        </ul>
    @endif
    
    {{-- ========== TATA TERTIB (Waka Kesiswaan, Operator) ========== --}}
    @if(in_array($role, ['Waka Kesiswaan', 'Operator Sekolah']) || $isDeveloper)
        <div class="sidebar-section">Tata Tertib</div>
        <ul class="sidebar-menu">
            <li class="sidebar-menu-item">
                <a href="{{ route('frequency-rules.index') }}" class="sidebar-menu-link {{ Request::routeIs('frequency-rules.*') ? 'active' : '' }}">
                    <x-ui.icon name="shield-alert" class="sidebar-menu-icon" />
                    <span>Aturan & Poin Pelanggaran</span>
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
    
    {{-- ========== KEPALA SEKOLAH ========== --}}
    @if($role === 'Kepala Sekolah' || $isDeveloper)
        @php
            $pendingCount = \App\Models\TindakLanjut::where('status', 'Menunggu Persetujuan')->count();
        @endphp
        <div class="sidebar-section">Kepala Sekolah</div>
        <ul class="sidebar-menu">
            <li class="sidebar-menu-item">
                <a href="{{ route('kepala-sekolah.data.siswa') }}" class="sidebar-menu-link {{ Request::routeIs('kepala-sekolah.data.siswa*') ? 'active' : '' }}">
                    <x-ui.icon name="users" class="sidebar-menu-icon" />
                    <span>Data Siswa</span>
                </a>
            </li>
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
    
    {{-- ========== PENGATURAN SISTEM (Operator) ========== --}}
    @if($role === 'Operator Sekolah' || $isDeveloper)
        <div class="sidebar-section">Pengaturan Sistem</div>
        <ul class="sidebar-menu">
            <li class="sidebar-menu-item">
                <a href="{{ route('audit.activity.index') }}" class="sidebar-menu-link {{ Request::routeIs('audit.activity.index') && !request('tab') ? 'active' : '' }}">
                    <x-ui.icon name="activity" class="sidebar-menu-icon" />
                    <span>Audit Log</span>
                </a>
            </li>
            
            {{-- Arsip Data with Submenu --}}
            <li class="sidebar-menu-item" x-data="{ open: {{ Request::routeIs('siswa.deleted', 'jurusan.trash', 'kelas.trash', 'konsentrasi.trash', 'users.trash', 'admin.kurikulum.trash', 'admin.periode-semester.trash') ? 'true' : 'false' }} }">
                <button type="button" @click="open = !open" class="sidebar-menu-link w-full justify-between {{ Request::routeIs('siswa.deleted', 'jurusan.trash', 'kelas.trash', 'konsentrasi.trash', 'users.trash', 'admin.kurikulum.trash', 'admin.periode-semester.trash') ? 'active' : '' }}">
                    <span class="flex items-center gap-3">
                        <x-ui.icon name="archive" class="sidebar-menu-icon" />
                        <span>Arsip Data</span>
                    </span>
                    <x-ui.icon name="chevron-down" size="16" class="transition-transform duration-200" ::class="{ 'rotate-180': open }" />
                </button>
                <ul x-show="open" x-collapse x-cloak class="sidebar-submenu">
                    <li>
                        <a href="{{ route('siswa.deleted') }}" class="sidebar-submenu-link {{ Request::routeIs('siswa.deleted') ? 'active' : '' }}">
                            <x-ui.icon name="users" size="14" />
                            <span>Arsip Siswa</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('jurusan.trash') }}" class="sidebar-submenu-link {{ Request::routeIs('jurusan.trash') ? 'active' : '' }}">
                            <x-ui.icon name="hexagon" size="14" />
                            <span>Arsip Jurusan</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('konsentrasi.trash') }}" class="sidebar-submenu-link {{ Request::routeIs('konsentrasi.trash') ? 'active' : '' }}">
                            <x-ui.icon name="layers" size="14" />
                            <span>Arsip Konsentrasi</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('kelas.trash') }}" class="sidebar-submenu-link {{ Request::routeIs('kelas.trash') ? 'active' : '' }}">
                            <x-ui.icon name="layout" size="14" />
                            <span>Arsip Kelas</span>
                        </a>
                    </li>
                    @if(Route::has('admin.kurikulum.trash'))
                    <li>
                        <a href="{{ route('admin.kurikulum.trash') }}" class="sidebar-submenu-link {{ Request::routeIs('admin.kurikulum.trash') ? 'active' : '' }}">
                            <x-ui.icon name="book" size="14" />
                            <span>Arsip Kurikulum</span>
                        </a>
                    </li>
                    @endif
                    @if(Route::has('admin.periode-semester.trash'))
                    <li>
                        <a href="{{ route('admin.periode-semester.trash') }}" class="sidebar-submenu-link {{ Request::routeIs('admin.periode-semester.trash') ? 'active' : '' }}">
                            <x-ui.icon name="calendar" size="14" />
                            <span>Arsip Periode</span>
                        </a>
                    </li>
                    @endif
                    @if(Route::has('users.trash'))
                    <li>
                        <a href="{{ route('users.trash') }}" class="sidebar-submenu-link {{ Request::routeIs('users.trash') ? 'active' : '' }}">
                            <x-ui.icon name="user-x" size="14" />
                            <span>Arsip Pengguna</span>
                        </a>
                    </li>
                    @endif
                </ul>
            </li>
        </ul>
    @endif
    
    {{-- ========== AKUN SAYA ========== --}}
    <div class="sidebar-section">Akun Saya</div>
    <ul class="sidebar-menu">
        <li class="sidebar-menu-item">
            <a href="{{ route('account.edit') }}" class="sidebar-menu-link {{ Request::routeIs('account.*', 'profile.*') ? 'active' : '' }}">
                <x-ui.icon name="user" class="sidebar-menu-icon" />
                <span>Profil Saya</span>
            </a>
        </li>
    </ul>
    
</nav>

{{-- Sidebar Scroll Position Persistence --}}
<script>
    // Save scroll position when navigating away
    document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.querySelector('.sidebar-nav');
        if (!sidebar) return;
        
        // Restore scroll position on page load
        const savedScrollPos = sessionStorage.getItem('sidebarScrollPos');
        if (savedScrollPos !== null) {
            sidebar.scrollTop = parseInt(savedScrollPos, 10);
        }
        
        // Save scroll position on scroll
        let scrollTimeout;
        sidebar.addEventListener('scroll', function() {
            clearTimeout(scrollTimeout);
            scrollTimeout = setTimeout(function() {
                sessionStorage.setItem('sidebarScrollPos', sidebar.scrollTop);
            }, 100);
        });
        
        // Save scroll position before page unload
        window.addEventListener('beforeunload', function() {
            sessionStorage.setItem('sidebarScrollPos', sidebar.scrollTop);
        });
    });
</script>
