<!-- PDF Report Template - Supports multiple report types -->
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; font-size: 11px; }
        h1 { text-align: center; margin-bottom: 5px; font-size: 16px; }
        .meta { text-align: center; margin-bottom: 20px; font-size: 11px; color: #666; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #333; padding: 6px 8px; text-align: left; font-size: 10px; }
        th { background-color: #f0f0f0; font-weight: bold; }
        .footer { text-align: right; margin-top: 30px; font-size: 10px; }
        .status-badge { padding: 2px 6px; border-radius: 3px; font-size: 9px; }
        .status-disetujui { background-color: #d1fae5; color: #047857; }
        .status-pending { background-color: #fff7ed; color: #c2410c; }
        .status-ditolak { background-color: #fee2e2; color: #b91c1c; }
        .status-default { background-color: #e0e7ff; color: #4338ca; }
    </style>
</head>
<body>
    <h1>{{ strtoupper($reportType ?? 'LAPORAN PELANGGARAN SISWA') }}</h1>
    <div class="meta">
        <p>{{ school_name() }}</p>
        <p>Tanggal Laporan: {{ now()->format('d M Y H:i') }}</p>
        @if(!empty($filters['periode_mulai']) && !empty($filters['periode_akhir']))
            <p>Periode: {{ \Carbon\Carbon::parse($filters['periode_mulai'])->format('d M Y') }} s/d {{ \Carbon\Carbon::parse($filters['periode_akhir'])->format('d M Y') }}</p>
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 4%">No</th>
                <th style="width: 10%">NISN</th>
                <th style="width: 18%">Nama Siswa</th>
                <th style="width: 10%">Kelas</th>
                <th style="width: 15%">Jurusan</th>
                @if(($reportType ?? '') === 'Laporan Pelanggaran')
                    <th style="width: 25%">Jenis Pelanggaran</th>
                    <th style="width: 10%">Tanggal</th>
                    <th style="width: 8%">Dicatat Oleh</th>
                @else
                    <th style="width: 25%">Keterangan</th>
                    <th style="width: 10%">Tanggal</th>
                    <th style="width: 8%">Status</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach($data as $idx => $row)
            <tr>
                <td>{{ $idx + 1 }}</td>
                <td>{{ $row->siswa->nisn ?? '-' }}</td>
                <td>{{ $row->siswa->nama_siswa ?? '-' }}</td>
                <td>{{ $row->siswa->kelas->nama_kelas ?? '-' }}</td>
                <td>{{ $row->siswa->kelas->jurusan->nama_jurusan ?? '-' }}</td>
                @if(($reportType ?? '') === 'Laporan Pelanggaran')
                    <td>{{ $row->jenisPelanggaran->nama ?? '-' }}</td>
                    <td>{{ isset($row->tanggal_kejadian) ? \Carbon\Carbon::parse($row->tanggal_kejadian)->format('d M Y') : '-' }}</td>
                    <td>{{ $row->user->nama ?? $row->user->username ?? '-' }}</td>
                @else
                    <td>{{ $row->sanksi_deskripsi ?? $row->pemicu ?? '-' }}</td>
                    <td>{{ $row->created_at ? $row->created_at->format('d M Y') : '-' }}</td>
                    <td>
                        @php
                            $statusValue = is_object($row->status) ? $row->status->value : ($row->status ?? '-');
                            $statusClass = match($statusValue) {
                                'Disetujui' => 'status-disetujui',
                                'Menunggu Persetujuan' => 'status-pending',
                                'Ditolak' => 'status-ditolak',
                                default => 'status-default',
                            };
                        @endphp
                        <span class="status-badge {{ $statusClass }}">{{ $statusValue }}</span>
                    </td>
                @endif
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Total: {{ count($data) }} record</p>
        <p>&copy; {{ now()->year }} - {{ sistem_info('nama_lengkap') }} {{ school_name() }}</p>
    </div>
</body>
</html>
