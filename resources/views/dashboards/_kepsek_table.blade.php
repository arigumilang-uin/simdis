    @if(($kasusMenunggu ?? collect())->count() > 0)
        <div class="card border-amber-200 bg-amber-50/50">
            <div class="card-header border-b border-amber-200/50">
                <h3 class="card-title text-amber-800 flex items-center gap-2">
                    <x-ui.icon name="clock" size="18" />
                    Menunggu Persetujuan Anda
                </h3>
                <a href="{{ route('kepala-sekolah.approvals.index') }}" class="btn btn-sm btn-outline text-amber-700 border-amber-300 hover:bg-amber-100">
                    Lihat Semua
                </a>
            </div>
            <div class="table-container !rounded-none !border-0 !bg-transparent">
                <table class="table">
                    <thead class="bg-amber-100/50">
                        <tr>
                            <th>Siswa</th>
                            <th>Kelas</th>
                            <th>Tanggal</th>
                            <th class="w-32 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white">
                        @foreach($kasusMenunggu->take(5) as $kasus)
                            <tr>
                                <td>
                                    <div class="font-medium text-gray-800">{{ $kasus->siswa->nama_siswa ?? '-' }}</div>
                                </td>
                                <td><span class="badge badge-primary">{{ $kasus->siswa->kelas->nama_kelas ?? '-' }}</span></td>
                                <td class="text-gray-500">{{ $kasus->created_at->format('d M Y') }}</td>
                                <td class="text-center">
                                    <a href="{{ route('tindak-lanjut.show', $kasus->id) }}" class="btn btn-sm btn-primary">
                                        Review
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
