    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        {{-- Total Siswa --}}
        <x-dashboard.stat-card 
            label="Total Siswa" 
            value="{{ number_format($totalSiswa ?? 0) }}" 
            icon="users" 
            color="primary"
            href="{{ route('siswa.index') }}"
            footer="Data Pokok"
        />
        
        {{-- Pelanggaran Periode --}}
        <x-dashboard.stat-card 
            label="Pelanggaran" 
            value="{{ number_format($pelanggaranFiltered ?? 0) }}" 
            icon="alert-circle" 
            color="rose"
            footer="Periode Terpilih"
        />
        
        {{-- Kasus Aktif --}}
        <x-dashboard.stat-card 
            label="Kasus Aktif" 
            value="{{ number_format($kasusAktif ?? 0) }}" 
            icon="clipboard" 
            color="amber"
            href="{{ route('tindak-lanjut.index') }}"
            footer="Belum Selesai"
        />
        
        {{-- Butuh Persetujuan --}}
        <x-dashboard.stat-card 
            label="Approval" 
            value="{{ number_format($butuhPersetujuan ?? 0) }}" 
            icon="check-circle" 
            color="emerald"
            href="{{ route('kepala-sekolah.approvals.index') }}"
            footer="Menunggu"
        />
    </div>
