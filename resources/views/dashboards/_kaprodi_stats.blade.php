    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        {{-- Siswa di Jurusan --}}
        <x-dashboard.stat-card 
            label="Total Siswa" 
            value="{{ number_format($totalSiswa ?? 0) }}" 
            icon="users"
            color="primary"
        >
            <x-slot name="footer">
                <span class="text-xs text-gray-500">Siswa di Jurusan</span>
            </x-slot>
        </x-dashboard.stat-card>
        
        {{-- Pelanggaran --}}
        <x-dashboard.stat-card 
            label="Pelanggaran" 
            value="{{ number_format($totalPelanggaran ?? 0) }}" 
            icon="alert-circle"
            color="rose"
        >
            <x-slot name="footer">
                <span class="text-xs text-gray-500">Periode ini</span>
            </x-slot>
        </x-dashboard.stat-card>
        
        {{-- Kasus Aktif --}}
        <x-dashboard.stat-card 
            label="Kasus Aktif" 
            value="{{ number_format($totalKasus ?? 0) }}" 
            icon="clipboard"
            color="amber"
        >
            <x-slot name="footer">
                <span class="text-xs text-gray-500">Perlu Perhatian</span>
            </x-slot>
        </x-dashboard.stat-card>
    </div>
