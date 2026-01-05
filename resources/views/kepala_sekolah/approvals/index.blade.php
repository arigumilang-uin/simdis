@extends('layouts.app')

@section('page-header')
    <x-page-header title="Persetujuan Tindak Lanjut" subtitle="Daftar kasus yang menunggu persetujuan Kepala Sekolah" icon="check-square" />
@endsection

@section('content')
<div class="card">
    <div class="card-body">
        @if($kasusMenunggu->isEmpty())
            <div class="text-center py-10">
                <div class="bg-gray-100 rounded-full p-4 w-16 h-16 mx-auto flex items-center justify-center mb-4">
                    <x-ui.icon name="check-circle" size="32" class="text-green-500" />
                </div>
                <h3 class="text-lg font-medium text-gray-900">Semua Beres!</h3>
                <p class="text-gray-500 mt-1">Tidak ada kasus yang menunggu persetujuan saat ini.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>Siswa</th>
                            <th>Kelas</th>
                            <th>Pelanggaran / Pemicu</th>
                            <th>Sanksi</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($kasusMenunggu as $index => $item)
                            <tr>
                                <td>{{ $kasusMenunggu->firstItem() + $index }}</td>
                                <td>{{ $item->created_at->format('d/m/Y') }}</td>
                                <td>
                                    <div class="font-medium">{{ $item->siswa->nama_siswa ?? '-' }}</div>
                                    <div class="text-xs text-gray-500">{{ $item->siswa->nisn ?? '-' }}</div>
                                </td>
                                <td>{{ $item->siswa->kelas->nama_kelas ?? '-' }}</td>
                                <td>{{ $item->pemicu ?? '-' }}</td>
                                <td>{{ $item->sanksi_deskripsi ?? '-' }}</td>
                                <td>
                                    <span class="badge badge-warning">{{ $item->status }}</span>
                                </td>
                                <td>
                                    <a href="{{ route('kepala-sekolah.approvals.show', $item->id) }}" class="btn btn-sm btn-primary">
                                        Periksa
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $kasusMenunggu->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
