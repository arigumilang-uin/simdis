<!DOCTYPE html>
<html>
<head>
    <title>Surat Panggilan Wali Murid</title>
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12pt;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
        }

        .header {
            text-align: center;
            border-bottom: 3px double black;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .header h2 {
            margin: 2px 0;
            font-size: 14pt;
            font-weight: bold;
            text-transform: uppercase;
        }

        .header p {
            margin: 2px 0;
            font-size: 10pt;
        }

        .meta {
            margin-bottom: 20px;
        }

        .meta table {
            width: 100%;
        }

        .meta td {
            padding: 2px 0;
            vertical-align: top;
        }

        .meta td:first-child {
            width: 100px;
        }

        .content {
            text-align: justify;
            margin-bottom: 30px;
        }

        .content p {
            margin: 10px 0;
        }

        table.data-siswa {
            width: 100%;
            margin: 15px 0;
        }

        table.data-siswa td {
            padding: 3px 0;
            vertical-align: top;
        }

        table.data-siswa td:first-child {
            width: 180px;
        }

        .ttd-container {
            margin-top: 40px;
            page-break-inside: avoid;
        }

        .ttd-location {
            text-align: right;
            margin-bottom: 30px;
        }

        /* Flexible signature layout */
        .ttd-grid {
            display: table;
            width: 100%;
            margin-top: 20px;
        }

        .ttd-row {
            display: table-row;
        }

        .ttd-cell {
            display: table-cell;
            text-align: center;
            vertical-align: top;
            padding: 10px;
        }

        /* 1-2 pembina: 2 columns (50% each) */
        .ttd-grid.cols-2 .ttd-cell {
            width: 50%;
        }

        /* 3 pembina: 3 columns (33% each) */
        .ttd-grid.cols-3 .ttd-cell {
            width: 33.33%;
        }

        /* 4 pembina: 4 columns (25% each) */
        .ttd-grid.cols-4 .ttd-cell {
            width: 25%;
        }

        .ttd-cell .jabatan {
            font-weight: normal;
            margin-bottom: 5px;
        }

        .ttd-cell .nama {
            font-weight: bold;
            margin-top: 60px;
        }

        .ttd-cell .nip {
            font-size: 10pt;
            margin-top: 2px;
        }

        @media print {
            body {
                padding: 0;
            }
        }
    </style>
</head>
<body>

    <div class="header">
        <h2>PEMERINTAH PROVINSI RIAU</h2>
        <h2>DINAS PENDIDIKAN</h2>
        <h2>BIDANG KEAHLIAN: {{ strtoupper($siswa->kelas->jurusan->nama_jurusan ?? 'AGRIBISNIS DAN AGROTEKNOLOGI') }}</h2>
        <p>Jl. Panglima Ghimbam Kecamatan Lubuk Dalam, Kabupaten Siak, Provinsi Riau</p>
        <p>Kode Pos: 28773 | Telp. 08126878622 | Email: smknegeri1lubukdalam@gmail.com</p>
        <p>AKREDITASI "A" | NSS: 401091110006 | NPSN: 10404972 | NIS: 400060</p>
    </div>

    <div class="meta">
        <p style="text-align: right; margin-bottom: 15px;">
            Lubuk Dalam, {{ \Carbon\Carbon::parse($surat->tanggal_surat)->locale('id')->isoFormat('D MMMM YYYY') }}
        </p>

        <table>
            <tr>
                <td>Nomor</td>
                <td>: {{ $surat->nomor_surat }}</td>
            </tr>
            <tr>
                <td>Lampiran</td>
                <td>: -</td>
            </tr>
        </table>

        <p style="margin-top: 15px;">
            Kepada Yth,<br>
            <strong>Bapak/Ibu Orang Tua/Wali dari {{ $siswa->nama_siswa }}</strong>
        </p>

        <table style="margin-top: 10px;">
            <tr>
                <td>Hal</td>
                <td>: <strong>Panggilan {{ $siswa->nama_siswa }}</strong></td>
            </tr>
        </table>
    </div>

    <div class="content">
        <p><strong>Dengan Hormat,</strong></p>

        <p>
            Menindak Lanjuti masalah kedisiplinan Siswa di Sekolah, kami bermaksud memanggil orang tua/wali 
            dan juga peserta didik atas:
        </p>

        <table class="data-siswa">
            <tr>
                <td>Nama</td>
                <td>: <strong>{{ $siswa->nama_siswa }}</strong></td>
            </tr>
            <tr>
                <td>Kelas/Jurusan</td>
                <td>: <strong>{{ $siswa->kelas->nama_kelas }} / {{ $siswa->kelas->jurusan->nama_jurusan }}</strong></td>
            </tr>
        </table>

        <p>Adapun pemanggilan tersebut akan dilaksanakan pada:</p>

        <table class="data-siswa" style="margin-left: 20px;">
            <tr>
                <td>Hari/Tanggal</td>
                <td>: <strong>{{ $surat->tanggal_pertemuan ? \Carbon\Carbon::parse($surat->tanggal_pertemuan)->locale('id')->isoFormat('dddd, D MMMM YYYY') : '........................................................' }}</strong></td>
            </tr>
            <tr>
                <td>Waktu</td>
                <td>: <strong>{{ $surat->waktu_pertemuan ?? '........................................................' }}</strong></td>
            </tr>
            <tr>
                <td>Tempat</td>
                <td>: <strong>Kampus SMKN 1 Lubuk Dalam</strong></td>
            </tr>
            <tr>
                <td>Keperluan</td>
                <td>: <strong>{{ $surat->keperluan ?? 'Pembinaan dan Konsultasi Siswa' }}</strong></td>
            </tr>
        </table>

        <p>
            Demikian Surat Panggilan ini disampaikan, kehadiran Bapak/Ibu sangat diharapkan. 
            Atas kerjasama yang baik diucapkan terimakasih.
        </p>
    </div>

    <div class="ttd-container">
        <div class="ttd-location">
            
        </div>

        @php
            $pembinaData = $surat->pembina_data ?? [];
            $jumlahPembina = count($pembinaData);
            
            // Tentukan layout berdasarkan jumlah pembina
            if ($jumlahPembina <= 2) {
                $cols = 2;
            } elseif ($jumlahPembina == 3) {
                $cols = 3;
            } else {
                $cols = 4;
            }
        @endphp

        @if($jumlahPembina > 0)
            <div class="ttd-grid cols-{{ $cols }}">
                @foreach($pembinaData as $index => $pembina)
                    @if($index % $cols == 0 && $index > 0)
                        </div><div class="ttd-row">
                    @endif
                    
                    @if($index % $cols == 0)
                        <div class="ttd-row">
                    @endif

                    <div class="ttd-cell">
                        <div class="jabatan">{{ $pembina['jabatan'] ?? '' }}</div>
                        <div class="nama">{{ $pembina['nama'] ?? '(...................................)' }}</div>
                        @if(isset($pembina['nip']) && $pembina['nip'])
                            <div class="nip">NIP. {{ $pembina['nip'] }}</div>
                        @else
                            <div class="nip">(...................................)</div>
                        @endif
                    </div>

                    @if(($index + 1) % $cols == 0 || $index == $jumlahPembina - 1)
                        </div>
                    @endif
                @endforeach
            </div>
        @else
            {{-- Fallback jika tidak ada pembina data (backward compatibility) --}}
            <div class="ttd-grid cols-2">
                <div class="ttd-row">
                    <div class="ttd-cell">
                        <div class="jabatan">Wali Kelas</div>
                        <div class="nama">{{ $siswa->kelas->waliKelas->nama ?? '(...................................)' }}</div>
                        @if($siswa->kelas->waliKelas && $siswa->kelas->waliKelas->nip)
                            <div class="nip">NIP. {{ $siswa->kelas->waliKelas->nip }}</div>
                        @else
                            <div class="nip">(...................................)</div>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </div>

</body>
</html>
