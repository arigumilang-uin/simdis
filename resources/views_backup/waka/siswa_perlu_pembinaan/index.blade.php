@extends('layouts.app')

@section('title', 'Siswa Perlu Pembinaan')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12">
            <h4><i class="fas fa-user-check mr-2"></i> Siswa Perlu Pembinaan Internal</h4>
            <p class="text-muted">
                Monitoring siswa berdasarkan <strong>akumulasi poin</strong> pelanggaran untuk pembinaan internal.
                <br><small><em>Data ini adalah rekomendasi pembinaan, bukan trigger surat pemanggilan otomatis.</em></small>
            </p>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-3">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $stats['total_siswa'] }}</h3>
                    <p>Total Siswa Perlu Pembinaan</p>
                </div>
                <div class="icon">
                    <i class="fas fa-users"></i>
                </div>
            </div>
        </div>
        
        @foreach($stats['by_range'] as $stat)
            @if($stat['count'] > 0)
            <div class="col-lg-3 col-6">
                <div class="small-box {{ $loop->index == 0 ? 'bg-success' : ($loop->index == 1 ? 'bg-warning' : ($loop->index == 2 ? 'bg-orange' : 'bg-danger')) }}">
                    <div class="inner">
                        <h3>{{ $stat['count'] }}</h3>
                        <p>{{ $stat['rule']->getRangeText() }}</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                </div>
            </div>
            @endif
        @endforeach
    </div>

    <!-- Filters & Export -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0"><i class="fas fa-filter"></i> Filter & Export</h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('kepala-sekolah.siswa-perlu-pembinaan.index') }}" class="form-inline">
                <div class="form-group mr-2 mb-2">
                    <label for="rule_id" class="mr-2">Range Poin:</label>
                    <select name="rule_id" id="rule_id" class="form-control">
                        <option value="">Semua Range</option>
                        @foreach($rules as $rule)
                            <option value="{{ $rule->id }}" {{ $ruleId == $rule->id ? 'selected' : '' }}>
                                {{ $rule->getRangeText() }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="form-group mr-2 mb-2">
                    <label for="kelas_id" class="mr-2">Kelas:</label>
                    <select name="kelas_id" id="kelas_id" class="form-control">
                        <option value="">Semua Kelas</option>
                        @foreach($kelasList as $kelas)
                            <option value="{{ $kelas->id }}" {{ $kelasId == $kelas->id ? 'selected' : '' }}>
                                {{ $kelas->nama_kelas }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="form-group mr-2 mb-2">
                    <label for="jurusan_id" class="mr-2">Jurusan:</label>
                    <select name="jurusan_id" id="jurusan_id" class="form-control">
                        <option value="">Semua Jurusan</option>
                        @foreach($jurusanList as $jurusan)
                            <option value="{{ $jurusan->id }}" {{ $jurusanId == $jurusan->id ? 'selected' : '' }}>
                                {{ $jurusan->nama_jurusan }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <button type="submit" class="btn btn-primary mr-2 mb-2">
                    <i class="fas fa-search"></i> Filter
                </button>
                
                <a href="{{ route('kepala-sekolah.siswa-perlu-pembinaan.index') }}" class="btn btn-secondary mb-2">
                    <i class="fas fa-redo"></i> Reset
                </a>
            </form>
            
            <div class="mt-2">
                <a href="{{ route('kepala-sekolah.siswa-perlu-pembinaan.export-csv', request()->query()) }}" class="btn btn-success btn-sm">
                    <i class="fas fa-file-csv"></i> Export CSV
                </a>
            </div>
        </div>
    </div>

    <!-- Table Siswa -->
    <div class="card mt-3">
        <div class="card-header">
            <h5 class="card-title mb-0">Daftar Siswa ({{ $siswaList->count() }} siswa)</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0">
                    <thead>
                        <tr>
                            <th width="8%">NIS</th>
                            <th width="18%">Nama Siswa</th>
                            <th width="10%">Kelas</th>
                            <th width="12%">Jurusan</th>
                            <th width="8%" class="text-center">Total Poin</th>
                            <th width="12%">Range Poin</th>
                            <th width="20%">Rekomendasi Pembinaan</th>
                            <th width="12%">Pembina</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($siswaList as $item)
                        <tr>
                            <td>{{ $item['siswa']->nis }}</td>
                            <td>
                                <a href="{{ route('siswa.show', $item['siswa']->id) }}" class="text-primary">
                                    <strong>{{ $item['siswa']->nama_lengkap }}</strong>
                                </a>
                            </td>
                            <td>{{ $item['siswa']->kelas->nama_kelas ?? '-' }}</td>
                            <td>{{ $item['siswa']->kelas->jurusan->nama_jurusan ?? '-' }}</td>
                            <td class="text-center">
                                <span class="badge badge-{{ $item['total_poin'] > 300 ? 'danger' : ($item['total_poin'] > 100 ? 'warning' : 'info') }} badge-lg">
                                    {{ $item['total_poin'] }}
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-secondary">
                                    {{ $item['rekomendasi']['range_text'] }}
                                </span>
                            </td>
                            <td>
                                <small>{{ $item['rekomendasi']['keterangan'] }}</small>
                            </td>
                            <td>
                                @foreach($item['rekomendasi']['pembina_roles'] as $role)
                                    <span class="badge badge-primary badge-sm">{{ $role }}</span>
                                @endforeach
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="fas fa-info-circle"></i> 
                                @if($ruleId || $kelasId || $jurusanId)
                                    Tidak ada siswa yang sesuai dengan filter.
                                @else
                                    Tidak ada siswa yang perlu pembinaan saat ini.
                                @endif
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Info Box -->
    <div class="row mt-3">
        <div class="col-md-12">
            <div class="alert alert-info">
                <h5><i class="fas fa-info-circle"></i> Catatan Penting</h5>
                <ul class="mb-0">
                    <li><strong>Pembinaan Internal</strong> adalah rekomendasi konseling berdasarkan akumulasi poin, TIDAK trigger surat pemanggilan otomatis.</li>
                    <li><strong>Surat Pemanggilan</strong> hanya trigger dari pelanggaran dengan sanksi "Panggilan orang tua" (diatur di Frequency Rules).</li>
                    <li>Data ini dapat digunakan untuk <strong>monitoring proaktif</strong> sebelum siswa mencapai threshold surat pemanggilan.</li>
                    <li>Klik nama siswa untuk melihat detail profil dan riwayat pelanggaran lengkap.</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
