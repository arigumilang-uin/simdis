@extends('layouts.app')

@section('title', 'Audit & Manajemen Data Siswa')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/audit/index.css') }}">
@endsection

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-3"><i class="fas fa-shield-alt mr-2 text-warning"></i> Audit & Manajemen Data Siswa</h1>
            <p class="text-muted">Hapus atau pindahkan siswa dengan hati-hati. Pastikan Anda memahami dampak penghapusan data.</p>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow">
                <div class="card-header bg-warning text-dark font-weight-bold">
                    <i class="fas fa-exclamation-triangle mr-2"></i> Pilih Scope Penghapusan
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('audit.siswa.preview') }}" id="auditForm">
                        @csrf

                        <!-- Pilih Scope -->
                        <div class="form-group">
                            <label for="scope" class="font-weight-bold">Scope Penghapusan *</label>
                            <select class="form-control form-control-lg" id="scope" name="scope" required onchange="updateScopeFields()">
                                <option value="">-- Pilih Scope --</option>
                                <option value="kelas">Hapus per Kelas</option>
                                <option value="jurusan">Hapus per Jurusan</option>
                                <option value="tingkat">Hapus per Tingkat (e.g., 10, 11, 12)</option>
                            </select>
                            @error('scope')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Kelas Scope -->
                        <div class="form-group" id="kelasGroup" style="display: none;">
                            <label for="kelas_id" class="font-weight-bold">Pilih Kelas</label>
                            <select class="form-control" id="kelas_id" name="kelas_id">
                                <option value="">-- Pilih Kelas --</option>
                                @foreach($allKelas as $kelas)
                                    <option value="{{ $kelas->id }}">
                                        {{ $kelas->nama_kelas }} ({{ $kelas->siswa->count() }} siswa)
                                    </option>
                                @endforeach
                            </select>
                            @error('kelas_id')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Jurusan Scope -->
                        <div class="form-group" id="jurusanGroup" style="display: none;">
                            <label for="jurusan_id" class="font-weight-bold">Pilih Jurusan</label>
                            <select class="form-control" id="jurusan_id" name="jurusan_id">
                                <option value="">-- Pilih Jurusan --</option>
                                @foreach($allJurusan as $jurusan)
                                    <option value="{{ $jurusan->id }}">
                                        {{ $jurusan->nama_jurusan }} ({{ $jurusan->siswa()->count() }} siswa)
                                    </option>
                                @endforeach
                            </select>
                            @error('jurusan_id')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Tingkat Scope -->
                        <div class="form-group" id="tingkatGroup" style="display: none;">
                            <label for="tingkat" class="font-weight-bold">Pilih Tingkat</label>
                            <input type="text" class="form-control" id="tingkat" name="tingkat" placeholder="e.g., 10, 11, 12">
                            <small class="form-text text-muted">Ketik "10" untuk kelas X, "11" untuk XI, "12" untuk XII</small>
                            @error('tingkat')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Alert Box -->
                        <div class="alert alert-danger border-left-danger" role="alert" style="border-left: 4px solid #dc3545;">
                            <strong><i class="fas fa-exclamation-circle mr-2"></i> Peringatan</strong>
                            <ul class="mb-0 mt-2">
                                <li>Penghapusan akan menghapus semua <strong>riwayat pelanggaran</strong>, <strong>tindak lanjut</strong>, dan <strong>surat panggilan</strong> siswa.</li>
                                <li>Akun Wali Murid <strong>TIDAK akan dihapus</strong>, hanya hubungan ke siswa yang dihapus.</li>
                                <li>Proses ini adalah <strong>soft-delete</strong> (dapat di-restore dari database admin).</li>
                                <li>Selalu buat backup sebelum menghapus data.</li>
                            </ul>
                        </div>

                        <!-- Tombol Submit -->
                        <div class="form-group">
                            <button type="submit" class="btn btn-warning btn-lg btn-block" id="submitBtn" disabled>
                                <i class="fas fa-search mr-2"></i> Lanjut ke Preview (Dry-Run)
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Info Box -->
        <div class="col-lg-4">
            <div class="card border-0 shadow bg-light">
                <div class="card-header bg-info text-white font-weight-bold">
                    <i class="fas fa-info-circle mr-2"></i> Informasi
                </div>
                <div class="card-body small">
                    <p><strong>Apa yang akan terjadi?</strong></p>
                    <ol>
                        <li>Anda memilih scope penghapusan (kelas, jurusan, atau tingkat)</li>
                        <li>Sistem menampilkan preview (berapa banyak data yang akan terhapus)</li>
                        <li>Anda mengunduh backup CSV sebagai precaution</li>
                        <li>Anda mengetik "DELETE" untuk mengkonfirmasi</li>
                        <li>Data dihapus secara soft-delete (dapat di-restore)</li>
                    </ol>
                    <hr>
                    <p><strong>Alternatif: Artisan Command</strong></p>
                    <p>Anda juga bisa gunakan terminal:</p>
                    <code class="bg-white p-2 d-block text-break">php artisan siswa:bulk-delete --kelas=ID --dry-run</code>
                    <small class="text-muted">Lihat dokumentasi untuk opsi lengkap.</small>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function updateScopeFields() {
    const scope = document.getElementById('scope').value;
    document.getElementById('kelasGroup').style.display = scope === 'kelas' ? 'block' : 'none';
    document.getElementById('jurusanGroup').style.display = scope === 'jurusan' ? 'block' : 'none';
    document.getElementById('tingkatGroup').style.display = scope === 'tingkat' ? 'block' : 'none';
    updateSubmitBtn();
}

function updateSubmitBtn() {
    const scope = document.getElementById('scope').value;
    const kelas = document.getElementById('kelas_id').value;
    const jurusan = document.getElementById('jurusan_id').value;
    const tingkat = document.getElementById('tingkat').value;

    const isValid = (scope === 'kelas' && kelas) || (scope === 'jurusan' && jurusan) || (scope === 'tingkat' && tingkat);
    document.getElementById('submitBtn').disabled = !isValid;
}

document.getElementById('kelas_id').addEventListener('change', updateSubmitBtn);
document.getElementById('jurusan_id').addEventListener('change', updateSubmitBtn);
document.getElementById('tingkat').addEventListener('input', updateSubmitBtn);
</script>
@endsection

@section('scripts')
    <script src="{{ asset('js/pages/audit.js') }}"></script>
@endsection
