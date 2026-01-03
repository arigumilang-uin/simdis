<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'Sistem Kedisiplinan') | {{ school_name() }}</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
  
  <style>
      body {
          font-family: 'Inter', sans-serif;
          background-color: #f4f6f9;
          font-size: 0.9rem; /* Ukuran font pas, tidak terlalu besar */
      }

      /* Navbar lebih bersih */
      .main-header {
          border-bottom: 1px solid #dee2e6;
          box-shadow: 0 1px 2px rgba(0,0,0,0.05); /* Shadow sangat tipis */
      }
      
      /* Sidebar: Warna solid profesional */
      .main-sidebar {
          background-color: #343a40; /* Dark Grey standar */
          box-shadow: none;
      }
      .brand-link {
          border-bottom: 1px solid #4b545c;
      }
      
      /* Menu Sidebar */
      .nav-sidebar .nav-link {
          border-radius: 4px; /* Sudut sedikit membulat */
          padding: 8px 12px;
      }
      /* Menu Aktif: Biru AdminLTE standar (Konsisten) */
      .nav-pills .nav-link.active, .nav-pills .show > .nav-link {
          background-color: #007bff; 
          color: #fff;
          font-weight: 600;
          box-shadow: 0 2px 4px rgba(0,0,0,0.1);
      }
      
      /* Header Kategori Menu */
      .nav-header {
          font-size: 0.75rem;
          font-weight: 700;
          color: #6c757d !important;
          text-transform: uppercase;
          letter-spacing: 0.5px;
          padding: 1rem 1rem 0.5rem;
      }

      /* Content Wrapper */
      .content-wrapper {
          background-color: #f4f6f9; /* Abu-abu sangat muda, nyaman di mata */
      }
      .content-header h1 {
          font-size: 1.5rem;
          font-weight: 600;
          color: #343a40;
      }

      /* Card di seluruh aplikasi */
      .card {
          box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
          border-radius: 4px;
      }
  </style>

  @yield('styles')
</head>

<body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed layout-footer-fixed">
<div class="wrapper">

  <nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
      </li>
      <li class="nav-item d-none d-sm-inline-block">
        <span class="nav-link font-weight-bold text-muted">Tahun Ajaran {{ school_year() }}</span>
      </li>
    </ul>

    <ul class="navbar-nav ml-auto">
      
      @if(auth()->check() && auth()->user()->hasRole('Kepala Sekolah'))
      <li class="nav-item dropdown">
        <a class="nav-link" data-toggle="dropdown" href="#">
          <i class="far fa-bell"></i>
          @php $unread = auth()->user()->unreadNotifications()->count(); @endphp
          @if($unread > 0)
            <span class="badge badge-danger navbar-badge">{{ $unread }}</span>
          @endif
        </a>
        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
          <span class="dropdown-header font-weight-bold">{{ $unread }} Notifikasi</span>
          <div class="dropdown-divider"></div>
          
          @forelse(auth()->user()->unreadNotifications()->limit(5)->get() as $n)
            <a href="{{ $n->data['url'] ?? '#' }}" class="dropdown-item">
              <i class="fas fa-envelope mr-2 text-warning"></i> {{ Str::limit($n->data['siswa_nama'] ?? 'Info Baru', 20) }}
              <span class="float-right text-muted text-sm">{{ $n->created_at->diffForHumans() }}</span>
            </a>
            <div class="dropdown-divider"></div>
          @empty
            <a href="#" class="dropdown-item text-center text-muted text-sm">Tidak ada notifikasi</a>
          @endforelse
          
          <a href="{{ route('kepala-sekolah.approvals.index') }}" class="dropdown-item dropdown-footer">Lihat Semua</a>
        </div>
      </li>
      @endif

      <li class="nav-item">
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-link nav-link text-danger" onclick="return confirm('Keluar dari sistem?')">
                <i class="fas fa-sign-out-alt"></i>
            </button>
        </form>
      </li>
    </ul>
  </nav>
  <aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="#" class="brand-link">
      <img src="https://adminlte.io/themes/v3/dist/img/AdminLTELogo.png" alt="Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
      <span class="brand-text font-weight-light">SIMDIS <strong>SMK</strong></span>
    </a>

    <div class="sidebar">
      @auth
      <div class="user-panel mt-3 pb-3 mb-3 d-flex align-items-center">
        <div class="image">
          <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->username) }}&background=007bff&color=fff" class="img-circle elevation-2" alt="User Image">
        </div>
        <div class="info">
          <a href="{{ route('account.edit') }}" class="d-block">{{ Str::limit(Auth::user()->username, 18) }}</a>
          <small class="text-muted text-uppercase" style="font-size: 10px; letter-spacing: 1px;">
              {{ Auth::user()->effectiveRoleName() ?? Auth::user()->role?->nama_role }}
          </small>
        </div>
      </div>
      @endauth

      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
          
            @php
              $realIsDeveloper = Auth::user()->isDeveloper();
              $override = session('developer_role_override');
              $isDev = $realIsDeveloper && ! $override;
              $role = Auth::user()->effectiveRoleName() ?? Auth::user()->role?->nama_role;
            @endphp

            @if($realIsDeveloper)
            <li class="nav-header">DEVELOPER MODE</li>
            <li class="nav-item has-treeview {{ $override ? 'menu-open' : '' }}">
              <a href="#" class="nav-link {{ $override ? 'active' : '' }}">
                <i class="nav-icon fas fa-code"></i>
                <p>Switch Role <i class="right fas fa-angle-left"></i></p>
              </a>
              <ul class="nav nav-treeview">
                <li class="nav-item">
                  <a href="{{ route('dashboard.developer') }}" class="nav-link">
                    <i class="far fa-circle nav-icon"></i> <p>Console</p>
                  </a>
                </li>
                @foreach(['Operator Sekolah', 'Kepala Sekolah', 'Wali Kelas', 'Kaprodi', 'Wali Murid', 'Waka Kesiswaan', 'Waka Sarana', 'Guru'] as $roleName)
                <li class="nav-item">
                  <a href="{{ route('developer.impersonate', ['role' => $roleName]) }}" class="nav-link {{ $override == $roleName ? 'active' : '' }}">
                    <i class="far fa-circle nav-icon"></i> <p>{{ $roleName }}</p>
                  </a>
                </li>
                @endforeach
                <li class="nav-item">
                  <a href="{{ route('developer.impersonate.clear') }}" class="nav-link text-warning">
                    <i class="fas fa-undo nav-icon"></i> <p>Reset</p>
                  </a>
                </li>
              </ul>
            </li>
            @endif

            @unless($realIsDeveloper && ! $override)
            
            <li class="nav-item">
                @php
                    $dashRoute = '#';
                    if(($realIsDeveloper && !$override) || $role == 'Developer') $dashRoute = route('dashboard.developer');
                    elseif($isDev || $role == 'Operator Sekolah' || $role == 'Waka Kesiswaan') $dashRoute = route('dashboard.admin');
                    elseif($role == 'Kepala Sekolah') $dashRoute = route('dashboard.kepsek');
                    elseif($role == 'Wali Kelas') $dashRoute = route('dashboard.walikelas');
                    elseif($role == 'Kaprodi') $dashRoute = route('dashboard.kaprodi');
                    elseif($role == 'Wali Murid') $dashRoute = route('dashboard.wali_murid');
                    elseif($role == 'Waka Sarana') $dashRoute = route('dashboard.waka-sarana');
                @endphp
                <a href="{{ $dashRoute }}" class="nav-link {{ Request::is('dashboard*') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-tachometer-alt"></i>
                    <p>Dashboard</p>
                </a>
            </li>

            @if($isDev || in_array($role, ['Guru', 'Wali Kelas', 'Waka Kesiswaan', 'Kaprodi', 'Waka Sarana']))
            <li class="nav-header">OPERASIONAL</li>
            <li class="nav-item">
                <a href="{{ route('pelanggaran.create') }}" class="nav-link {{ Request::is('pelanggaran*') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-edit"></i>
                    <p>Catat Pelanggaran</p>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('my-riwayat.index') }}" class="nav-link {{ Request::is('riwayat/saya*') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-list-alt"></i>
                    <p>Riwayat Saya</p>
                </a>
            </li>
            @endif

            @if($isDev || in_array($role, ['Operator Sekolah', 'Waka Kesiswaan', 'Wali Kelas', 'Kaprodi', 'Kepala Sekolah']))
            <li class="nav-header">MONITORING</li>
            
            @if($isDev || in_array($role, ['Operator Sekolah', 'Waka Kesiswaan', 'Wali Kelas', 'Kaprodi']))
            <li class="nav-item">
                <a href="{{ route('siswa.index') }}" class="nav-link {{ Request::is('siswa*') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-users"></i>
                    <p>@if($role == 'Wali Kelas') Data Siswa Kelas @else Data Siswa @endif</p>
                </a>
            </li>
            @endif

            <li class="nav-item">
                <a href="{{ route('riwayat.index') }}" class="nav-link {{ Request::is('riwayat-pelanggaran*') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-history"></i>
                    <p>Log Pelanggaran</p>
                </a>
            </li>

            @if($isDev || in_array($role, ['Wali Kelas', 'Kaprodi', 'Waka Kesiswaan', 'Kepala Sekolah', 'Operator Sekolah']))
            <li class="nav-item">
                <a href="{{ route('tindak-lanjut.index') }}" class="nav-link {{ Request::is('tindak-lanjut*') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-folder-open"></i>
                    <p>Daftar Kasus</p>
                </a>
            </li>
            @endif

            @if($isDev || in_array($role, ['Wali Kelas', 'Kaprodi', 'Waka Kesiswaan', 'Kepala Sekolah']))
            <li class="nav-item">
                <a href="{{ route('pembinaan.index') }}" class="nav-link {{ Request::is('pembinaan*') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-user-tie"></i>
                    <p>Siswa Perlu Pembinaan</p>
                </a>
            </li>
            @endif

            @if($isDev || in_array($role, ['Waka Kesiswaan', 'Kepala Sekolah']))
            <li class="nav-item">
                @php $jurusanRoute = in_array($role, ['Kepala Sekolah', 'Waka Kesiswaan']) ? route('kepala-sekolah.data.jurusan') : route('data-jurusan.index'); @endphp
                <a href="{{ $jurusanRoute }}" class="nav-link {{ Request::is('*jurusan*') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-shapes"></i>
                    <p>Data Jurusan</p>
                </a>
            </li>
            <li class="nav-item">
                @php $kelasRoute = in_array($role, ['Kepala Sekolah', 'Waka Kesiswaan']) ? route('kepala-sekolah.data.kelas') : route('data-kelas.index'); @endphp
                <a href="{{ $kelasRoute }}" class="nav-link {{ Request::is('*kelas*') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-columns"></i>
                    <p>Data Kelas</p>
                </a>
            </li>
            @endif
            @endif

            @if($isDev || $role == 'Operator Sekolah')
            <li class="nav-header">ADMINISTRASI</li>
            <li class="nav-item">
                <a href="{{ route('users.index') }}" class="nav-link {{ Request::is('users*') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-user-cog"></i>
                    <p>Manajemen User</p>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('frequency-rules.index') }}" class="nav-link {{ Request::is('frequency-rules*') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-gavel"></i>
                    <p>Aturan & Poin</p>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('pembinaan-internal-rules.index') }}" class="nav-link {{ Request::is('pembinaan-internal-rules*') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-hand-holding-heart"></i>
                    <p>Pembinaan Internal</p>
                </a>
            </li>
            <li class="nav-item">
              <a href="{{ route('audit.activity.index') }}" class="nav-link {{ Request::is('audit/activity*') ? 'active' : '' }}">
                <i class="nav-icon fas fa-fingerprint"></i>
                <p>Audit Log</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="{{ route('jurusan.index') }}" class="nav-link {{ Request::is('jurusan*') ? 'active' : '' }}">
                <i class="nav-icon fas fa-layer-group"></i>
                <p>Master Jurusan</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="{{ route('kelas.index') }}" class="nav-link {{ Request::is('kelas*') ? 'active' : '' }}">
                <i class="nav-icon fas fa-chalkboard"></i>
                <p>Master Kelas</p>
              </a>
            </li>
            @endif

            @if($isDev || $role == 'Waka Kesiswaan')
            <li class="nav-header">KESISWAAN</li>
            <li class="nav-item">
                <a href="{{ route('frequency-rules.index') }}" class="nav-link {{ Request::is('frequency-rules*') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-list"></i>
                    <p>Aturan Tata Tertib</p>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('pembinaan-internal-rules.index') }}" class="nav-link {{ Request::is('pembinaan-internal-rules*') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-hands-helping"></i>
                    <p>Pembinaan Internal</p>
                </a>
            </li>
            @endif

            @if($isDev || $role == 'Kepala Sekolah')
            <li class="nav-header">KEPALA SEKOLAH</li>
            <li class="nav-item">
                <a href="{{ route('kepala-sekolah.approvals.index') }}" class="nav-link {{ Request::is('kepala-sekolah/approvals*') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-check-double"></i>
                    <p>
                        Persetujuan
                        @if($pc = \App\Models\TindakLanjut::where('status', 'Menunggu Persetujuan')->count())
                            <span class="badge badge-danger right">{{ $pc }}</span>
                        @endif
                    </p>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('kepala-sekolah.reports.index') }}" class="nav-link {{ Request::is('kepala-sekolah/reports*') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-file-alt"></i>
                    <p>Laporan</p>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('kepala-sekolah.siswa-perlu-pembinaan.index') }}" class="nav-link {{ Request::is('kepala-sekolah/siswa-perlu-pembinaan*') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-user-check"></i>
                    <p>Siswa Pembinaan</p>
                </a>
            </li>
            @endif

            @endunless
            
            <li class="nav-header">PENGATURAN</li>
            <li class="nav-item">
                <a href="{{ route('account.edit') }}" class="nav-link {{ Request::is('akun') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-user-circle"></i>
                    <p>Profil Saya</p>
                </a>
            </li>

        </ul>
      </nav>
    </div>
  </aside>

  <div class="content-wrapper">
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0">@yield('title')</h1>
          </div>
        </div>
      </div>
    </div>

    <div class="content">
      <div class="container-fluid">
        @yield('content')
      </div>
    </div>
  </div>

  <footer class="main-footer">
    <div class="float-right d-none d-sm-inline">
      {{ sistem_info('nama_lengkap') }} v{{ sistem_info('versi') }}
    </div>
    <strong>&copy; {{ date('Y') }} <a href="#">{{ school_name() }}</a>.</strong>
  </footer>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script src="{{ asset('js/components/stickyFilter.js') }}"></script>
<script src="{{ asset('js/utils/search.js') }}"></script>

@stack('scripts')
</body>
</html>