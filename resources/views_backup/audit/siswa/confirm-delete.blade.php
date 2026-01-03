@extends('layouts.app')

@section('title', 'Konfirmasi Penghapusan Siswa')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/audit/index.css') }}">
@endsection

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-3"><i class="fas fa-times-circle mr-2 text-danger"></i> Konfirmasi Penghapusan</h1>
        </div>
    </div>

    <!-- Final Warning -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-danger border-left-danger" style="border-left: 4px solid #dc3545;">
                <strong><i class="fas fa-skull-crossbones mr-2"></i> PERINGATAN AKHIR</strong>
                <ul class="mb-0 mt-2">
                    <li>Anda akan menghapus <strong>{{ $siswas->count() }} siswa</strong> beserta data terkait.</li>
                    <li>{{ $totalRiwayat }} riwayat pelanggaran akan dihapus.</li>
                    <li>{{ $totalTindak }} tindak lanjut akan dihapus.</li>
                    <li>{{ $totalSurat }} surat panggilan akan dihapus.</li>
                    <li>Akun Wali Murid tidak akan dihapus, tapi hubungan mereka ke siswa akan dihapus.</li>
                    <li>Ini adalah <strong>soft-delete</strong> dan dapat di-restore oleh administrator database.</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Daftar Siswa -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow">
                <div class="card-header bg-danger text-white font-weight-bold">
                    <i class="fas fa-list mr-2"></i> {{ $siswas->count() }} Siswa yang akan dihapus
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-striped mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th>No</th>
                                    <th>NISN</th>
                                    <th>Nama</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($siswas as $idx => $siswa)
                                    <tr>
                                        <td>{{ $idx + 1 }}</td>
                                        <td><code>{{ $siswa['nisn'] }}</code></td>
                                        <td>{{ $siswa['nama_siswa'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Confirmation Form -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow">
                <div class="card-header bg-dark text-white font-weight-bold">
                    <i class="fas fa-keyboard mr-2"></i> Ketik Konfirmasi
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('audit.siswa.destroy') }}" id="confirmForm">
                        @csrf
                        @method('DELETE')

                        <p class="mb-3">Untuk melanjutkan, ketik <code class="bg-light p-1">DELETE</code> pada field di bawah:</p>

                        <div class="form-group">
                            <input 
                                type="text" 
                                class="form-control form-control-lg" 
                                id="confirmation" 
                                name="confirmation" 
                                placeholder="Ketik DELETE"
                                required
                                onkeyup="updateDeleteBtn()"
                            >
                            <small class="form-text text-muted">Perhatian: ketikan harus tepat (case-sensitive)</small>
                        </div>

                        <div class="form-check mb-3">
                            <input type="checkbox" class="form-check-input" id="understandCheck" required>
                            <label class="form-check-label" for="understandCheck">
                                Saya memahami konsekuensi penghapusan ini dan telah membuat backup data.
                            </label>
                        </div>

        <div class="form-check mb-3">
            <input type="checkbox" class="form-check-input" id="forceDeleteCheck" name="force_delete" value="1">
            <label class="form-check-label" for="forceDeleteCheck">
                <strong>Hard-delete (permanent)</strong> - Jika tidak dicentang, akan menggunakan soft-delete (dapat di-restore).
            </label>
        </div>

        @if(count($orphanedWalis) > 0)
        <div class="alert alert-warning border-left-warning" style="border-left: 4px solid #ffc107;">
            <strong><i class="fas fa-exclamation-circle mr-2"></i> Akun Wali Murid Orphaned</strong>
            <p class="mb-2 mt-2">
                Berikut {{ count($orphanedWalis) }} akun Wali Murid yang tidak memiliki relasi siswa lagi setelah penghapusan ini:
            </p>
            <div class="table-responsive">
                <table class="table table-sm table-bordered mb-0 bg-white">
                    <thead class="bg-light">
                        <tr>
                            <th>Nama Wali</th>
                            <th>Username</th>
                            <th>Email</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orphanedWalis as $wali)
                        <tr>
                            <td>{{ $wali->nama }}</td>
                            <td><code>{{ $wali->username }}</code></td>
                            <td>{{ $wali->email }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <p class="small text-muted mt-2 mb-0">
                💡 <strong>Rekomendasi:</strong> Centang opsi di bawah untuk otomatis menghapus akun Wali Murid yang orphaned ini.
            </p>
        </div>

        <div class="form-check mb-3">
            <input type="checkbox" class="form-check-input" id="deleteOrphanedWaliCheck" name="delete_orphaned_wali" value="1">
            <label class="form-check-label" for="deleteOrphanedWaliCheck">
                <strong>Hapus akun Wali Murid orphaned</strong> - Akan menghapus {{ count($orphanedWalis) }} akun yang tidak memiliki siswa lagi.
            </label>
        </div>
        @endif                        <div class="form-group">
                            <button type="submit" class="btn btn-danger btn-lg btn-block" id="deleteBtn" disabled>
                                <i class="fas fa-trash-alt mr-2"></i> Hapus Sekarang ({{ $siswas->count() }} siswa)
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Info Sidebar -->
        <div class="col-lg-4">
            <div class="card border-0 shadow bg-light">
                <div class="card-header bg-info text-white font-weight-bold">
                    <i class="fas fa-info-circle mr-2"></i> Info
                </div>
                <div class="card-body small">
                    <p><strong>Soft-Delete vs Hard-Delete:</strong></p>
                    <ul>
                        <li><strong>Soft-Delete</strong> (default): Data ditandai dihapus tapi masih ada di DB, dapat di-restore.</li>
                        <li><strong>Hard-Delete</strong>: Data dihapus permanen dari DB.</li>
                    </ul>
                    <hr>
                    <p><strong>Apa yang terjadi kemudian:</strong></p>
                    <ol>
                        <li>Data siswa dihapus</li>
                        <li>Semua riwayat pelanggaran dihapus</li>
                        <li>Semua tindak lanjut dihapus</li>
                        <li>Semua surat panggilan dihapus</li>
                        <li>Redirect ke halaman daftar siswa dengan notifikasi sukses</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Back Button -->
    <div class="row mt-4">
        <div class="col-12">
            <a href="{{ route('audit.siswa.summary') }}" class="btn btn-secondary btn-lg">
                <i class="fas fa-arrow-left mr-2"></i> Kembali ke Preview
            </a>
        </div>
    </div>
</div>

<script>
function updateDeleteBtn() {
    const confirmation = document.getElementById('confirmation').value;
    const understand = document.getElementById('understandCheck').checked;
    const isValid = confirmation === 'DELETE' && understand;
    document.getElementById('deleteBtn').disabled = !isValid;
}
</script>
@endsection

@section('scripts')
    <script src="{{ asset('js/pages/audit.js') }}"></script>
@endsectiondocument.getElementById('understandCheck').addEventListener('change', updateDeleteBtn);
</script>
@endsection
