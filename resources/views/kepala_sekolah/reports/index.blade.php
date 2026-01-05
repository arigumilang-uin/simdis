@extends('layouts.app')

@section('page-header')
    <x-page-header title="Laporan & Rekapitulasi" subtitle="Generate laporan pelanggaran dan tindak lanjut" icon="file-text" />
@endsection

@section('content')
<div class="space-y-6">
    {{-- Card: Filter Laporan --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Parameter Laporan</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('kepala-sekolah.reports.preview') }}" method="POST" target="_blank">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    {{-- Report Type --}}
                    <div class="form-group">
                        <label class="form-label">Jenis Laporan <span class="text-red-500">*</span></label>
                        <select name="report_type" class="form-input form-select" required>
                            <option value="pelanggaran">Riwayat Pelanggaran</option>
                            <option value="siswa">Siswa Bermasalah (Perlu Pembinaan)</option>
                            <option value="tindakan">Rekap Tindak Lanjut</option>
                        </select>
                        <p class="text-xs text-gray-500 mt-1">Pilih jenis data yang ingin ditampilkan.</p>
                    </div>

                    {{-- Jurusan --}}
                    <div class="form-group">
                        <label class="form-label">Jurusan</label>
                        <select name="jurusan_id" class="form-input form-select">
                            <option value="">Semua Jurusan</option>
                            @foreach($jurusans as $jurusan)
                                <option value="{{ $jurusan->id }}">{{ $jurusan->nama_jurusan }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Kelas --}}
                    <div class="form-group">
                        <label class="form-label">Kelas</label>
                        <select name="kelas_id" class="form-input form-select">
                            <option value="">Semua Kelas</option>
                            @foreach($kelas as $k)
                                <option value="{{ $k->id }}">{{ $k->nama_kelas }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Periode Mulai --}}
                    <div class="form-group">
                        <label class="form-label">Dari Tanggal</label>
                        <input type="date" name="periode_mulai" class="form-input">
                    </div>

                    {{-- Periode Akhir --}}
                    <div class="form-group">
                        <label class="form-label">Sampai Tanggal</label>
                        <input type="date" name="periode_akhir" class="form-input">
                    </div>
                </div>

                <div class="mt-6 flex justify-end">
                    <button type="submit" class="btn btn-primary">
                        <x-ui.icon name="eye" size="18" class="mr-2" />
                        Preview Laporan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
