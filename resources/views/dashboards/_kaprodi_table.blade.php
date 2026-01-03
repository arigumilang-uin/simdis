    {{-- Kasus Terbaru --}}
    <div class="card h-full flex flex-col">
        <div class="card-header border-b border-gray-100">
            <h3 class="card-title">Kasus Perlu Ditangani</h3>
            <a href="{{ route('tindak-lanjut.index') }}" class="btn btn-sm btn-secondary">Lihat Semua</a>
        </div>
        <div class="card-body p-0 flex-1">
            @forelse($kasusBaru ?? [] as $kasus)
                <a href="{{ route('tindak-lanjut.show', $kasus->id) }}" class="flex items-center gap-4 p-4 hover:bg-gray-50 border-b border-gray-100 last:border-b-0 group transition-colors">
                    <div class="w-10 h-10 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform">
                        <x-ui.icon name="clipboard" size="18" />
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-medium text-gray-800 truncate">{{ $kasus->siswa->nama_siswa ?? '-' }}</p>
                        <p class="text-sm text-gray-500">{{ $kasus->siswa->kelas->nama_kelas ?? '-' }} • {{ $kasus->created_at->diffForHumans() }}</p>
                    </div>
                    <span class="badge badge-{{ $kasus->status === 'Baru' ? 'info' : 'warning' }}">{{ $kasus->status }}</span>
                </a>
            @empty
                <div class="flex flex-col items-center justify-center py-12 px-4 text-center h-full">
                     <div class="w-12 h-12 rounded-full bg-gray-50 text-gray-300 flex items-center justify-center mb-3">
                        <x-ui.icon name="check-circle" size="24" />
                    </div>
                    <p class="text-sm text-gray-500">Bagus! Tidak ada kasus yang perlu ditangani saat ini.</p>
                </div>
            @endforelse
        </div>
    </div>
