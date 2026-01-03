    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        {{-- Total Siswa --}}
        <x-dashboard.stat-card 
            label="Total Siswa" 
            value="{{ number_format($totalSiswa ?? 0) }}" 
            icon="users"
            color="primary"
        >
            <x-slot name="footer">
                <span class="text-xs text-gray-500">Siswa Aktif</span>
            </x-slot>
        </x-dashboard.stat-card>
        
        {{-- Total Pelanggaran --}}
        <x-dashboard.stat-card 
            label="Pelanggaran" 
            value="{{ number_format($totalPelanggaran ?? 0) }}" 
            icon="alert-circle"
            color="rose"
        >
            <x-slot name="footer">
                <span class="text-xs text-gray-500">Periode Ini</span>
            </x-slot>
        </x-dashboard.stat-card>
        
        {{-- Total Kasus --}}
        <x-dashboard.stat-card 
            label="Kasus Aktif" 
            value="{{ number_format($totalKasus ?? 0) }}" 
            icon="clipboard"
            color="amber"
        >
            <x-slot name="footer">
                <span class="text-xs text-gray-500">Perlu Penanganan</span>
            </x-slot>
        </x-dashboard.stat-card>
        
        {{-- Menunggu Persetujuan --}}
        <x-dashboard.stat-card 
            label="Persetujuan" 
            value="{{ number_format($totalKasusMenunggu ?? 0) }}" 
            icon="check-circle"
            color="emerald"
            href="{{ route('kepala-sekolah.approvals.index') }}"
        >
            <x-slot name="footer">
                <span class="text-xs text-gray-500">Menunggu Acc</span>
            </x-slot>
        </x-dashboard.stat-card>
    </div>
