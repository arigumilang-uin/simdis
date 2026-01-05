@extends('layouts.app')

@section('page-header')
    <x-page-header title="Preview Laporan" subtitle="{{ $reportType }}" icon="file-text">
        <div class="flex gap-2">
            <a href="{{ route('kepala-sekolah.reports.export-csv') }}" class="btn btn-outline-success">
                <x-ui.icon name="file-text" size="18" class="mr-2" />
                Export CSV/Excel
            </a>
            <a href="{{ route('kepala-sekolah.reports.export-pdf') }}" class="btn btn-outline-danger">
                <x-ui.icon name="file" size="18" class="mr-2" />
                Export PDF
            </a>
        </div>
    </x-page-header>
@endsection

@section('content')
<div class="card">
    <div class="card-body">
        @if($data->isEmpty())
            <div class="text-center py-10">
                <div class="bg-gray-100 rounded-full p-4 w-16 h-16 mx-auto flex items-center justify-center mb-4">
                    <x-ui.icon name="inbox" size="32" class="text-gray-400" />
                </div>
                <h3 class="text-lg font-medium text-gray-900">Tidak ada data</h3>
                <p class="text-gray-500 mt-1">Tidak ditemukan data yang sesuai dengan filter.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>No</th>
                            @if($reportType === 'Laporan Pelanggaran')
                                <th>Tanggal</th>
                                <th>Nama Siswa</th>
                                <th>Kelas</th>
                                <th>Pelanggaran</th>
                                <th>Poin</th>
                                <th>Pencatat</th>
                            @else
                                <th>Tanggal</th>
                                <th>Nama Siswa</th>
                                <th>Kelas</th>
                                <th>Keterangan</th>
                                <th>Status</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data as $index => $item)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                @if($reportType === 'Laporan Pelanggaran')
                                    <td>{{ \Carbon\Carbon::parse($item->tanggal_kejadian)->format('d/m/Y') }}</td>
                                    <td>
                                        <div class="font-medium">{{ $item->siswa->nama_siswa ?? '-' }}</div>
                                        <div class="text-xs text-gray-500">{{ $item->siswa->nisn ?? '-' }}</div>
                                    </td>
                                    <td>{{ $item->siswa->kelas->nama_kelas ?? '-' }}</td>
                                    <td>{{ $item->jenisPelanggaran->nama ?? '-' }}</td>
                                    <td><span class="badge badge-danger">{{ $item->jenisPelanggaran->poin ?? 0 }}</span></td>
                                    <td>{{ $item->user->nama ?? $item->user->username ?? '-' }}</td>
                                @else
                                    {{-- Tindak Lanjut / Siswa Bermasalah --}}
                                    <td>{{ $item->created_at->format('d/m/Y') }}</td>
                                    <td>
                                        <div class="font-medium">{{ $item->siswa->nama_siswa ?? '-' }}</div>
                                    </td>
                                    <td>{{ $item->siswa->kelas->nama_kelas ?? '-' }}</td>
                                    <td>{{ $item->sanksi_deskripsi ?? $item->pemicu ?? '-' }}</td>
                                    <td>
                                        @if(isset($item->status))
                                            <span class="badge {{ $item->status->color() }}">
                                                {{ $item->status->label() }}
                                            </span>
                                        @else
                                            -
                                        @endif
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
