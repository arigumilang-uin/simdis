    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        {{-- Total Siswa --}}
        <a href="{{ route('siswa.index') }}" class="stat-card group">
            <div class="stat-card-icon primary">
                <x-ui.icon name="users" size="24" />
            </div>
            <div class="stat-card-content">
                <p class="stat-card-label">Total Siswa</p>
                <p class="stat-card-value">{{ number_format($totalSiswa ?? 0) }}</p>
                <p class="text-xs text-gray-500 mt-1">Data Pokok</p>
            </div>
        </a>
        
        {{-- Pelanggaran Periode --}}
        <div class="stat-card group cursor-default">
            <div class="stat-card-icon danger">
                <x-ui.icon name="alert-circle" size="24" />
            </div>
            <div class="stat-card-content">
                <p class="stat-card-label">Pelanggaran</p>
                <p class="stat-card-value">{{ number_format($pelanggaranFiltered ?? 0) }}</p>
                <p class="text-xs text-gray-500 mt-1">Periode Terpilih</p>
            </div>
        </div>
        
        {{-- Kasus Aktif --}}
        <a href="{{ route('tindak-lanjut.index') }}" class="stat-card group">
            <div class="stat-card-icon warning">
                <x-ui.icon name="clipboard" size="24" />
            </div>
            <div class="stat-card-content">
                <p class="stat-card-label">Kasus Aktif</p>
                <p class="stat-card-value">{{ number_format($kasusAktif ?? 0) }}</p>
                <p class="text-xs text-gray-500 mt-1">Belum Selesai</p>
            </div>
        </a>
        
        {{-- Butuh Persetujuan --}}
        <a href="{{ route('kepala-sekolah.approvals.index') }}" class="stat-card group">
            <div class="stat-card-icon success">
                <x-ui.icon name="check-circle" size="24" />
            </div>
            <div class="stat-card-content">
                <p class="stat-card-label">Approval</p>
                <p class="stat-card-value">{{ number_format($butuhPersetujuan ?? 0) }}</p>
                <p class="text-xs text-gray-500 mt-1">Menunggu</p>
            </div>
        </a>
    </div>
