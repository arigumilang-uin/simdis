<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Jadwal Pelajaran {{ $schoolName }}</title>
    <style>
        @page {
            size: 594mm 420mm landscape;
            margin: 10mm;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 9pt;
            line-height: 1.2;
            color: #000000;
        }

        .container {
            padding: 5mm;
        }

        /* Header */
        .header {
            text-align: center;
            margin-bottom: 5mm;
            border-bottom: 2px solid #000;
            padding-bottom: 3mm;
        }

        .header h1 {
            font-size: 16pt;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 2mm;
        }

        .header h2 {
            font-size: 12pt;
            font-weight: bold;
            text-transform: uppercase;
        }

        /* Table */
        .jadwal-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8pt;
            margin-bottom: 8mm;
        }

        .jadwal-table th,
        .jadwal-table td {
            border: 1px solid #000;
            padding: 1px 2px;
            text-align: center;
            vertical-align: middle;
        }

        .jadwal-table thead th {
            background-color: #d9d9d9;
            color: #000000;
            font-weight: bold;
            font-size: 7pt;
            padding: 2px 3px;
        }

        .jadwal-table thead th.jurusan-header {
            background-color: #bfbfbf;
        }

        .jadwal-table thead th.kelas-header {
            background-color: #d9d9d9;
            font-size: 6pt;
        }

        .jadwal-table tbody td {
            font-size: 6pt;
            padding: 1px 2px;
            line-height: 1.1;
        }

        .jadwal-table tbody td.hari-cell {
            font-weight: bold;
            background-color: #f2f2f2;
            text-align: center;
            font-size: 7pt;
            padding: 1px 2px;
            width: 35px;
            max-width: 35px;
        }

        .jadwal-table tbody td.waktu-cell {
            font-size: 5.5pt;
            white-space: nowrap;
            padding: 1px 2px;
        }

        .jadwal-table tbody td.jam-cell {
            font-weight: bold;
            font-size: 7pt;
            padding: 1px 2px;
        }

        .jadwal-table tbody td.mapel-cell {
            font-size: 5.5pt;
            padding: 1px 1px;
        }

        .jadwal-table tbody td.guru-cell {
            font-size: 5pt;
            padding: 1px 1px;
            color: #333;
            /* font-style: italic; */ /* Optional style */
        }

        /* Break row (istirahat, ishoma, upacara, lainnya) */
        .jadwal-table tbody td.break-cell {
            background-color: #e6e6e6;
            font-style: italic;
            font-size: 6pt;
            font-weight: bold;
            text-align: center;
        }

        /* First row of day - thicker top border */
        .jadwal-table tbody tr.first-row-of-day td {
            border-top: 2.5px solid #000;
        }

        /* Footer / Signature */
        .footer {
            margin-top: 8mm;
        }

        .signature {
            float: right;
            width: 70mm;
            text-align: center;
        }

        .signature .label {
            font-size: 11pt;
            margin-bottom: 2mm;
        }

        .signature .position {
            font-size: 11pt;
            font-weight: bold;
            margin-bottom: 20mm;
        }

        .signature .name {
            font-size: 11pt;
            font-weight: bold;
            text-decoration: underline;
            margin-bottom: 1mm;
        }

        .signature .nip {
            font-size: 10pt;
        }

        .clearfix::after {
            content: "";
            display: table;
            clear: both;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>Jadwal Pelajaran {{ $schoolName }}</h1>
            <h2>SEMESTER {{ $semester }} TAHUN AJARAN {{ $tahunAjaran }}</h2>
        </div>

        @php
            // Count total kelas for colspan
            $totalKelas = 0;
            foreach ($jurusans as $jurusan) {
                $totalKelas += $jurusan->kelas->count();
            }
            // Colspan for break rows: JAM KE- + all kelas columns * 2 (mapel + guru)
            $breakColspan = 1 + ($totalKelas * 2);
            
            // Calculate rowspan for each hari
            $hariRowspan = [];
            foreach ($jadwalData as $row) {
                $hari = $row['hari'];
                if (!isset($hariRowspan[$hari])) {
                    $hariRowspan[$hari] = 0;
                }
                $hariRowspan[$hari]++;
            }
            
            $prevHari = null;
        @endphp

        <table class="jadwal-table">
            <thead>
                <!-- Row 1: Main headers + Jurusan names -->
                <tr>
                    <th rowspan="2" style="width: 35px;">HARI</th>
                    <th rowspan="2" style="width: 50px;">WAKTU</th>
                    <th rowspan="2" style="width: 25px;">JAM KE-</th>
                    @foreach($jurusans as $jurusan)
                        @if($jurusan->kelas->count() > 0)
                            <th colspan="{{ $jurusan->kelas->count() * 2 }}" class="jurusan-header">
                                {{ $jurusan->nama_jurusan }}
                            </th>
                        @endif
                    @endforeach
                </tr>
                <!-- Row 2: Kelas names -->
                <tr>
                    @foreach($jurusans as $jurusan)
                        @foreach($jurusan->kelas as $kelas)
                            <th colspan="2" class="kelas-header">{{ $kelas->nama_kelas }}</th>
                        @endforeach
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($jadwalData as $index => $row)
                    @php
                        $isBreak = $row['tipe'] !== 'pelajaran';
                        $isFirstRowOfDay = $row['hari'] !== $prevHari;
                        $rowClass = $isFirstRowOfDay ? 'first-row-of-day' : '';
                    @endphp
                    <tr class="{{ $rowClass }}">
                        @if($isFirstRowOfDay)
                            <td class="hari-cell" rowspan="{{ $hariRowspan[$row['hari']] }}">
                                {{ strtoupper($row['hari']) }}
                            </td>
                            @php $prevHari = $row['hari']; @endphp
                        @endif
                        <td class="waktu-cell">{{ $row['waktu'] }}</td>
                        @if($isBreak)
                            <td class="break-cell" colspan="{{ $breakColspan }}">
                                {{ strtoupper($row['tipe']) }}
                            </td>
                        @else
                            <td class="jam-cell">{{ $row['jam_ke'] }}</td>
                            @foreach($jurusans as $jurusan)
                                @foreach($jurusan->kelas as $kelas)
                                    @php
                                        $cellData = $row['kelas'][$kelas->id] ?? null;
                                        $mapel = '-';
                                        $guru = '';
                                        if (is_array($cellData)) {
                                            $mapel = $cellData['mapel'];
                                            $guru = $cellData['guru'];
                                        } else {
                                            $mapel = $cellData ?? '-';
                                        }
                                    @endphp
                                    <td class="mapel-cell" style="width: 35px;">{{ $mapel }}</td>
                                    <td class="guru-cell" style="width: 20px;">{{ $guru }}</td>
                                @endforeach
                            @endforeach
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Footer / Signature -->
        <div class="footer clearfix">
            <div class="signature">
                <div class="label">Mengetahui,</div>
                <div class="position">KEPALA SEKOLAH</div>
                <div class="name">
                    @if($kepalaSekolah)
                        {{ $kepalaSekolah->nama }}
                    @else
                        _______________________
                    @endif
                </div>
                <div class="nip">
                    @if($kepalaSekolah)
                        {{ $kepalaSekolah->getFormattedIdentifier() }}
                    @else
                        NIP. _______________________
                    @endif
                </div>
            </div>
        </div>
    </div>
</body>
</html>
