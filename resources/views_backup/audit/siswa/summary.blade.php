@extends('layouts.app')

@section('title', 'Preview Penghapusan Siswa')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/audit/index.css') }}">
@endsection

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-3"><i class="fas fa-eye mr-2 text-info"></i> Preview: Dampak Penghapusan</h1>
        </div>
    </div>

    <!-- Summary Card -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow">
                <div class="card-header bg-info text-white font-weight-bold">
                    <i class="fas fa-chart-bar mr-2"></i> Scope: {{ $scopeName }}
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3">
                            <div class="mb-3">
                                <h2 class="text-primary font-weight-bold">{{ $totalSiswa }}</h2>
                                <p class="text-muted">Siswa akan dihapus</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <h2 class="text-warning font-weight-bold">{{ $totalRiwayat }}</h2>
                                <p class="text-muted">Riwayat Pelanggaran</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <h2 class="text-danger font-weight-bold">{{ $totalTindak }}</h2>
                                <p class="text-muted">Tindak Lanjut</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <h2 class="text-secondary font-weight-bold">{{ $totalSurat }}</h2>
                                <p class="text-muted">Surat Panggilan</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Warning -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-warning border-left-warning" style="border-left: 4px solid #ffc107;">
                <strong><i class="fas fa-exclamation-circle mr-2"></i> Perhatian</strong>
                <p class="mb-0 mt-2">
                    Semua data di atas akan di-<strong>soft-delete</strong> (dapat di-restore). Akun Wali Murid (<strong>{{ $totalWali }} akun</strong>) tidak akan dihapus.
                </p>
            </div>
        </div>
    </div>

    <!-- Daftar Siswa -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow">
                <div class="card-header bg-light font-weight-bold">
                    <i class="fas fa-list mr-2"></i> Daftar {{ $totalSiswa }} Siswa
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-striped mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th>No</th>
                                    <th>NISN</th>
                                    <th>Nama</th>
                                    <th>Kelas</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($siswas as $idx => $siswa)
                                    <tr>
                                        <td>{{ $idx + 1 }}</td>
                                        <td><code>{{ $siswa->nisn }}</code></td>
                                        <td>{{ $siswa->nama_siswa }}</td>
                                        <td>{{ $siswa->kelas->nama_kelas ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-3">Tidak ada siswa</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="row">
        <div class="col-12">
            <div class="d-flex gap-2">
                <!-- Back Button -->
                <a href="{{ route('audit.siswa') }}" class="btn btn-secondary btn-lg">
                    <i class="fas fa-arrow-left mr-2"></i> Kembali
                </a>

                <!-- Export Button -->
                <a href="{{ route('audit.siswa.export') }}" class="btn btn-info btn-lg">
                    <i class="fas fa-download mr-2"></i> Download Backup CSV
                </a>

                <!-- Confirm Delete Button -->
                <a href="{{ route('audit.siswa.confirm-delete') }}" class="btn btn-danger btn-lg">
                    <i class="fas fa-trash-alt mr-2"></i> Lanjut ke Konfirmasi Penghapusan
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
    <script src="{{ asset('js/pages/audit.js') }}"></script>
@endsection
