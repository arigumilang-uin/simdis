<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Surat Panggilan - {{ $siswa->nama_siswa }}</title>
    <style>
        /* 
         * FINAL REVISION - COMPREHENSIVE FIXES
         */
        
        @page {
            size: 215mm 330mm; /* F4 */
            margin: 1cm 2cm 2cm 2cm; /* MARGIN ATAS DIKURANGI JADI 1CM */
        }
        
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12pt;
            line-height: 1.5; /* JARAK ANTAR BARIS LEBIH LEGA */
            margin: 0;
            padding: 0;
            color: #000;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            border-spacing: 0;
        }
        
        td {
            padding: 0;
            vertical-align: top;
        }
        
        /* CSS KOP */
        .kop-provinsi {
            font-size: 16pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0px;
            margin: 0;
            line-height: 1;
        }
        
        .kop-dinas {
            font-size: 16pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0px;
            margin: 0;
            line-height: 1;
        }
        
        .kop-sekolah-container {
            width: 100%;
            text-align: center;
            margin: 5px 0;
            line-height: 1;
            display: block;
        }

        .kop-sekolah {
            font-size: 13pt; /* TURUN DARI 14PT AGAR MUAT */
            font-weight: bold;     
            text-transform: uppercase;
            
            text-shadow: 1px 0 0 #000; 
            transform: scale(0.85, 2.5);  /* LEBIH KURUS (0.85) AGAR TIDAK LEWAT MARGIN */
            transform-origin: center top; 
            
            display: inline-block;
            white-space: nowrap;
            
            margin-top: 2px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .kop-bidang {
            font-size: 11pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0px;
            /* JARAK KE ATAS DITAMBAH (KEBAWAHKAN SEDIKIT DARI SEKOLAH) */
            margin-top: 18px; 
            display: inline-block;
            /* JARAK KE BAWAH DIHAPUS BIAR DEKAT ALAMAT */
            margin-bottom: 0px; 
        }
        
        .kop-alamat {
            font-size: 9pt; /* TURUN DARI 10PT AGAR LEBIH KECIL */
            margin-top: 2px; 
            line-height: 1.2;
        }
        
        /* ISI SURAT */
        .indent { text-indent: 40px; }
        
        /* Justify diganti Left sesuai request "Rata Kiri" */
        p { text-align: left; margin-bottom: 5px; margin-top: 5px; } 

    </style>
</head>
<body>

    {{-- KOP SURAT --}}
    {{-- Hapus border-bottom di sini untuk hindari duplikasi --}}
    <table width="100%" border="0" cellpadding="0" cellspacing="0">
        <tr>
            <td width="15%" align="center" valign="top" style="padding-top: 5px;">
                @if(isset($logoBase64) && $logoBase64)
                    <img src="{{ $logoBase64 }}" width="90" style="margin-top: 0;">
                @else
                    <div style="width: 2.5cm; height: 2.5cm;"></div>
                @endif
            </td>
            <td width="85%" align="center" valign="top" style="padding-bottom: 8px;">
                <center>
                    <div class="kop-provinsi">PEMERINTAH PROVINSI RIAU</div>
                    <div class="kop-dinas">DINAS PENDIDIKAN</div>
                    
                    <div class="kop-sekolah-container">
                        <span class="kop-sekolah">SEKOLAH MENENGAH KEJURUAN (SMK) NEGERI 1 LUBUK DALAM</span>
                    </div>
                    
                    <div class="kop-bidang">BIDANG KEAHLIAN : {{ strtoupper($siswa->kelas->jurusan->nama_jurusan ?? 'AGRIBISNIS DAN AGROTEKNOLOGI') }}</div>
                    
                    <div class="kop-alamat">
                        Jl. Panglima Ghimbam Kecamatan Lubuk Dalam, Kabupaten Siak, Provinsi Riau Kode Pos : 28773<br>
                        Telp. 08128878822 Fax : - Email : smknegeri1lubukdalam@gmail.com<br>
                        AKREDITASI "A" NSS : 401091110006 NPSN : 10404972 NIS : 400060
                    </div>
                </center>
            </td>
        </tr>
    </table>
    
    {{-- GARIS KOP FIXED (VERSI BERSIH - TANPA OVERFLOW) --}}
    {{-- border-style: double adalah cara paling standar, tapi kadang kurang tebal --}}
    {{-- Kita pakai 2 Div terpisah biar kontrol penuh --}}
    
    {{-- GARIS KOP FIXED (WIDTH 100% KONSISTEN) --}}
    <div style="width: 100%; border-bottom: 3px solid #000; margin-top: 2px;"></div>
    <div style="width: 100%; border-bottom: 1px solid #000; margin-top: 1px;"></div>


    {{-- HEADER SURAT --}}
    <table width="100%" border="0" style="margin-top: 5px;">
        <tr>
            {{-- KOLOM KIRI --}}
            <td width="60%" valign="top" style="padding-top: 50px;"> 
                <table width="100%">
                    <tr>
                        <td width="60">Nomor</td>
                        <td width="15">:</td>
                        <td>{{ $surat->nomor_surat ?? '/421.5-SMKN 1 LD/ /' . date('Y') }}</td>
                    </tr>
                    <tr>
                        <td>Lamp</td>
                        <td>:</td>
                        <td>{{ $surat->lampiran ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>Hal</td>
                        <td>:</td>
                        <td><strong>{{ $surat->hal ?? 'Panggilan' }}</strong></td>
                    </tr>
                </table>
            </td>
            
            {{-- KOLOM KANAN --}}
            <td width="40%" valign="top" align="right">
                {{-- Wrapper Align Left, tapi posisinya di kanan --}}
                <div style="text-align: left; display: inline-block; width: 260px;">
                    
                    {{-- TANGGAL --}}
                    {{-- Sekarang rata kiri satu blok dengan Kepada --}}
                    <div style="margin-bottom: 30px;">
                        Lubuk Dalam, {{ \Carbon\Carbon::now()->isoFormat('D MMMM Y') }}
                    </div>
                    
                    {{-- KEPADA --}}
                    <div>
                        <div>Kepada :</div>
                        <div>Yth. Bapak/Ibu Orang Tua/Wali</div>
                        <div style="border-bottom: 1px dotted #000; display: block; padding-top: 5px;">
                            <strong>{{ $siswa->nama_siswa ?? '................................................' }}</strong>
                        </div>
                    </div>
                </div>
            </td>
        </tr>
    </table>

    {{-- ISI SURAT --}}
    <div style="margin-top: 20px;">
        {{-- Jarak bawah ditambah (20px) agar terpisah dari paragraf isi --}}
        <p style="margin-bottom: 20px;">Dengan Hormat,</p>
        
        {{-- Hapus class 'indent', ganti jadi rata kiri murni --}}
        <p style="text-align: left; margin-bottom: 5px;">
            Menindak Lanjuti masalah kedisiplinan Siswa di Sekolah, kami bermaksud memanggil orang tua/wali dan juga peserta didik atas Nama <strong>{{ $siswa->nama_siswa }}</strong> Kelas/Jurusan <strong>{{ $siswa->kelas->nama_kelas ?? '...........' }}</strong>
        </p>
        
        {{-- Margin top dikurangi (5px) agar DEKAT dengan teks di atasnya --}}
        <p style="margin-top: 5px; margin-bottom: 5px;">Adapun pemanggilan tersebut akan dilaksanakan pada :</p>
        
        {{-- TABLE DETAIL: Width 100% tapi di-offset margin left, jadi width efektif dikurangi margin --}}
        {{-- Agar kanan rata, pakai width: calc(100% - 30px) jika bisa, atau table width 100% di dalam wrapper div margin-left --}}
        <div style="margin-left: 30px;">
            <table style="width: 100%; margin-top: 5px; margin-bottom: 10px;">
                <tr>
                    <td width="120">Hari/Tanggal</td>
                    <td width="10">:</td>
                    <td><strong>{{ \Carbon\Carbon::parse($surat->tanggal_pertemuan)->locale('id')->isoFormat('dddd, D MMMM Y') }}</strong></td>
                </tr>
                <tr>
                    <td>Waktu</td>
                    <td>:</td>
                    <td>{{ \Carbon\Carbon::parse($surat->waktu_pertemuan)->format('H:i') }} WIB</td>
                </tr>
                <tr>
                    <td>Tempat</td>
                    <td>:</td>
                    <td>{{ $surat->tempat_pertemuan ?? 'Kampus SMKN 1 Lubuk Dalam' }}</td>
                </tr>
                <tr>
                    <td>Keperluan</td>
                    <td>:</td>
                    <td>{{ $surat->keperluan }}</td>
                </tr>
            </table>
        </div>
        
        {{-- Hapus class 'indent' --}}
        <p style="text-align: left; margin-top: 10px;">
            Demikian Surat Panggilan ini disampaikan, kehadiran Bapak/Ibu sangat diharapkan. Atas kerjasama yang baik diucapkan terimakasih.
        </p>
    </div>

    {{-- TANDA TANGAN DINAMIS BERDASARKAN PIHAK YANG TERLIBAT --}}
    
    @php
        // Hitung jumlah pihak yang terlibat
        $jumlahPihak = collect($pihakTerlibat)->filter()->count();
        
        // Tentukan template yang digunakan
        $templateType = '';
        if ($pihakTerlibat['wali_kelas'] && !$pihakTerlibat['kaprodi'] && !$pihakTerlibat['waka_kesiswaan'] && !$pihakTerlibat['kepala_sekolah']) {
            $templateType = 'wali_only';
        } elseif ($pihakTerlibat['wali_kelas'] && $pihakTerlibat['kaprodi'] && !$pihakTerlibat['waka_kesiswaan'] && !$pihakTerlibat['kepala_sekolah']) {
            $templateType = 'wali_kaprodi';
        } elseif ($pihakTerlibat['wali_kelas'] && $pihakTerlibat['waka_kesiswaan'] && !$pihakTerlibat['kaprodi'] && !$pihakTerlibat['kepala_sekolah']) {
            $templateType = 'wali_waka';
        } elseif ($pihakTerlibat['wali_kelas'] && $pihakTerlibat['kaprodi'] && $pihakTerlibat['waka_kesiswaan'] && !$pihakTerlibat['kepala_sekolah']) {
            $templateType = 'wali_kaprodi_waka';
        } else {
            $templateType = 'full'; // Template penuh (tanpa Kaprodi, hanya Wali+Waka+Kepsek)
        }
        
        // Helper function untuk mendapatkan data pembina dari pembina_data
        $getPembinaData = function($jabatan) use ($surat, $siswa) {
            // Cari di pembina_data
            if (isset($surat->pembina_data) && is_array($surat->pembina_data)) {
                foreach ($surat->pembina_data as $pembina) {
                    if (($pembina['jabatan'] ?? '') === $jabatan) {
                        return $pembina;
                    }
                }
            }
            
            // Fallback ke relasi jika tidak ada di pembina_data
            switch ($jabatan) {
                case 'Wali Kelas':
                    $user = $siswa->kelas->waliKelas ?? null;
                    break;
                case 'Kaprodi':
                    $user = $siswa->kelas->jurusan->kaprodi ?? null;
                    break;
                case 'Waka Kesiswaan':
                    $user = \App\Models\User::whereHas('role', fn($q) => $q->where('nama_role', 'Waka Kesiswaan'))->first();
                    break;
                case 'Kepala Sekolah':
                    $user = \App\Models\User::whereHas('role', fn($q) => $q->where('nama_role', 'Kepala Sekolah'))->first();
                    break;
                default:
                    $user = null;
            }
            
            if ($user) {
                $nipLabel = !empty($user->nip) ? 'NIP.' : (!empty($user->nuptk) ? 'NUPTK.' : 'NIP.');
                return [
                    'username' => $user->username,
                    'nama' => $user->nama,
                    'nip' => $user->nip ?? $user->nuptk ?? null,
                    'nip_label' => $nipLabel,
                ];
            }
            
            return null;
        };
        
        // Get pembina data
        $waliKelas = $getPembinaData('Wali Kelas');
        $kaprodi = $getPembinaData('Kaprodi');
        $wakaKesiswaan = $getPembinaData('Waka Kesiswaan');
        $kepalaSekolah = $getPembinaData('Kepala Sekolah');
    @endphp
    
    <table width="100%" border="0" style="margin-top: 40px;">
        
        @if($templateType === 'wali_only')
            {{-- Template 1: Hanya Wali Kelas (KANAN) --}}
            <tr>
                <td width="50%" align="center">
                    &nbsp;
                </td>
                <td width="50%" align="center">
                    Wali Kelas
                    <div style="height: 70px;"></div>
                    <strong style="text-decoration: underline;">{{ $waliKelas['username'] ?? '(.................................................)' }}</strong><br>
                    {{ $waliKelas['nip_label'] ?? 'NIP.' }} {{ $waliKelas['nip'] ?? '' }}
                </td>
            </tr>
            
        @elseif($templateType === 'wali_kaprodi')
            {{-- Template 2: Kaprodi (KIRI) + Wali Kelas (KANAN) --}}
            <tr>
                <td width="50%" align="center">
                    Ketua Program Keahlian
                    <div style="height: 70px;"></div>
                    <strong style="text-decoration: underline;">{{ $kaprodi['username'] ?? '(.................................................)' }}</strong><br>
                    {{ $kaprodi['nip_label'] ?? 'NIP.' }} {{ $kaprodi['nip'] ?? '' }}
                </td>
                <td width="50%" align="center">
                    Wali Kelas
                    <div style="height: 70px;"></div>
                    <strong style="text-decoration: underline;">{{ $waliKelas['username'] ?? '(.................................................)' }}</strong><br>
                    {{ $waliKelas['nip_label'] ?? 'NIP.' }} {{ $waliKelas['nip'] ?? '' }}
                </td>
            </tr>
            
        @elseif($templateType === 'wali_waka')
            {{-- Template 3: Wali Kelas + Waka Kesiswaan --}}
            <tr>
                <td width="50%" align="center">
                    Wali Kelas
                    <div style="height: 70px;"></div>
                    <strong style="text-decoration: underline;">{{ $waliKelas['username'] ?? '(.................................................)' }}</strong><br>
                    {{ $waliKelas['nip_label'] ?? 'NIP.' }} {{ $waliKelas['nip'] ?? '' }}
                </td>
                <td width="50%" align="center">
                    Waka. Kesiswaan
                    <div style="height: 70px;"></div>
                    <strong style="text-decoration: underline;">{{ $wakaKesiswaan['username'] ?? '(.................................................)' }}</strong><br>
                    {{ $wakaKesiswaan['nip_label'] ?? 'NIP.' }} {{ $wakaKesiswaan['nip'] ?? '' }}
                </td>
            </tr>
            
        @elseif($templateType === 'wali_kaprodi_waka')
            {{-- Template 4: Kaprodi (KIRI) + Wali (KANAN), Waka (BAWAH CENTERED) --}}
            <tr>
                <td width="50%" align="center">
                    Ketua Program Keahlian
                    <div style="height: 70px;"></div>
                    <strong style="text-decoration: underline;">{{ $kaprodi['username'] ?? '(.................................................)' }}</strong><br>
                    {{ $kaprodi['nip_label'] ?? 'NIP.' }} {{ $kaprodi['nip'] ?? '' }}
                </td>
                <td width="50%" align="center">
                    Wali Kelas
                    <div style="height: 70px;"></div>
                    <strong style="text-decoration: underline;">{{ $waliKelas['username'] ?? '(.................................................)' }}</strong><br>
                    {{ $waliKelas['nip_label'] ?? 'NIP.' }} {{ $waliKelas['nip'] ?? '' }}
                </td>
            </tr>
            <tr>
                <td colspan="2" align="center" style="padding-top: 40px;">
                    Waka. Kesiswaan
                    <div style="height: 70px;"></div>
                    <strong style="text-decoration: underline;">{{ $wakaKesiswaan['username'] ?? '(.................................................)' }}</strong><br>
                    {{ $wakaKesiswaan['nip_label'] ?? 'NIP.' }} {{ $wakaKesiswaan['nip'] ?? '' }}
                </td>
            </tr>
            
        @else
            {{-- Template 5: FULL (Waka KIRI + Wali KANAN, Kepsek BAWAH) --}}
            <tr>
                <td width="50%" align="center">
                    Waka. Kesiswaan
                    <div style="height: 70px;"></div>
                    <strong style="text-decoration: underline;">{{ $wakaKesiswaan['username'] ?? '(.................................................)' }}</strong><br>
                    {{ $wakaKesiswaan['nip_label'] ?? 'NIP.' }} {{ $wakaKesiswaan['nip'] ?? '' }}
                </td>
                <td width="50%" align="center">
                    Wali Kelas
                    <div style="height: 70px;"></div>
                    <strong style="text-decoration: underline;">{{ $waliKelas['username'] ?? '(.................................................)' }}</strong><br>
                    {{ $waliKelas['nip_label'] ?? 'NIP.' }} {{ $waliKelas['nip'] ?? '' }}
                </td>
            </tr>
            <tr>
                <td colspan="2" align="center" style="padding-top: 40px;">
                    Mengetahui<br>
                    Kepala Sekolah
                    <div style="height: 70px;"></div>
                    <strong style="text-decoration: underline;">{{ $kepalaSekolah['username'] ?? '(.................................................)' }}</strong><br>
                    {{ $kepalaSekolah['nip_label'] ?? 'NIP.' }} {{ $kepalaSekolah['nip'] ?? '' }}
                </td>
            </tr>
        @endif
        
    </table>

</body>
</html>
