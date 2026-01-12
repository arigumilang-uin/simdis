@php
    // Check if we're in preview mode (included in a page) or PDF mode (standalone)
    $isPreviewMode = isset($previewMode) && $previewMode === true;
@endphp

@if(!$isPreviewMode)
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Surat Panggilan - {{ $siswa->nama_siswa }}</title>
@endif
    <style>
        /* 
         * FINAL REVISION - COMPREHENSIVE FIXES
         * CSS scoped for both PDF mode and Preview mode
         */
        
        @page {
            size: 215mm 330mm; /* F4 */
            margin: 1cm 2cm 2cm 2cm; /* MARGIN ATAS DIKURANGI JADI 1CM */
        }
        
        /* PDF Mode: Apply to body */
        @if(!$isPreviewMode)
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12pt;
            line-height: 1.5;
            margin: 0;
            padding: 0;
            color: #000;
        }
        @endif
        
        /* Preview Mode: Scope all styles to .surat-preview-container */
        .surat-preview-container {
            font-family: 'Times New Roman', Times, serif !important;
            font-size: 12pt !important;
            line-height: 1.5 !important;
            color: #000 !important;
            padding: 20px;
        }
        
        .surat-preview-container table {
            width: 100%;
            border-collapse: collapse;
            border-spacing: 0;
        }
        
        .surat-preview-container td {
            padding: 0;
            vertical-align: top;
        }
        
        /* CSS KOP */
        .surat-preview-container .kop-provinsi,
        .kop-provinsi {
            font-size: 16pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0px;
            margin: 0;
            line-height: 1;
        }
        
        .surat-preview-container .kop-dinas,
        .kop-dinas {
            font-size: 16pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0px;
            margin: 0;
            line-height: 1;
        }
        
        .surat-preview-container .kop-sekolah-container,
        .kop-sekolah-container {
            width: 100%;
            text-align: center;
            margin: 5px 0;
            line-height: 1;
            display: block;
        }

        .surat-preview-container .kop-sekolah,
        .kop-sekolah {
            font-size: 13pt;
            font-weight: bold;     
            text-transform: uppercase;
            text-shadow: 1px 0 0 #000; 
            transform: scale(0.85, 2.5);
            transform-origin: center top; 
            display: inline-block;
            white-space: nowrap;
            margin-top: 2px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .surat-preview-container .kop-bidang,
        .kop-bidang {
            font-size: 11pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0px;
            margin-top: 18px; 
            display: inline-block;
            margin-bottom: 0px; 
        }
        
        .surat-preview-container .kop-alamat,
        .kop-alamat {
            font-size: 9pt;
            margin-top: 2px; 
            line-height: 1.2;
        }
        
        /* ISI SURAT */
        .surat-preview-container .indent,
        .indent { text-indent: 40px; }
        
        .surat-preview-container p,
        .surat-preview-container p { 
            text-align: left; 
            margin-bottom: 5px; 
            margin-top: 5px; 
        }

    </style>
@if(!$isPreviewMode)
</head>
<body>
@else
<div class="surat-preview-container">
@endif

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
            Menindak Lanjuti masalah kedisiplinan Siswa di Sekolah, kami bermaksud memanggil orang tua/wali dan juga peserta didik atas Nama <strong>{{ $siswa->nama_siswa }}</strong> Kelas/Jurusan <strong>{{ $siswa->kelas->nama_kelas ?? '...........' }}/{{ $siswa->kelas->jurusan->nama_jurusan ?? '...........' }}</strong>
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

    {{-- TANDA TANGAN DINAMIS BERDASARKAN PEMBINA YANG TERLIBAT --}}
    
    @php
        // Ambil pembina_roles dari surat (array of role names)
        $pembinaRoles = $surat->pembina_roles ?? [];
        
        // Urutan hierarki dari TERTINGGI ke TERENDAH
        $hierarki = ['Kepala Sekolah', 'Waka Kesiswaan', 'Waka Sarana', 'Kaprodi', 'Wali Kelas'];
        
        // Filter hanya pembina yang terlibat dan urutkan berdasarkan hierarki
        $activePembina = [];
        foreach ($hierarki as $role) {
            if (in_array($role, $pembinaRoles)) {
                $activePembina[] = $role;
            }
        }
        
        // Balik urutan: TERENDAH ke TERTINGGI (untuk layout: terendah di kanan)
        $activePembina = array_reverse($activePembina);
        $jumlahPembina = count($activePembina);
        
        // Helper function untuk mendapatkan data pembina
        $getPembinaData = function($jabatan) use ($surat, $siswa) {
            // Cari di pembina_data yang sudah disimpan di surat
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
                case 'Waka Sarana':
                    $user = \App\Models\User::whereHas('role', fn($q) => $q->where('nama_role', 'Waka Sarana'))->first();
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
        
        // Helper untuk menampilkan label jabatan yang proper
        $getJabatanLabel = function($role) {
            return match($role) {
                'Kepala Sekolah' => 'Kepala Sekolah',
                'Waka Kesiswaan' => 'Waka. Kesiswaan',
                'Waka Sarana' => 'Waka. Sarana',
                'Kaprodi' => 'Ketua Program Keahlian',
                'Wali Kelas' => 'Wali Kelas',
                default => $role,
            };
        };
    @endphp
    
    <table width="100%" border="0" style="margin-top: 40px;">
        
        @if($jumlahPembina === 1)
            {{-- 1 Pembina: Di KANAN --}}
            @php $p1 = $getPembinaData($activePembina[0]); @endphp
            <tr>
                <td width="50%" align="center">&nbsp;</td>
                <td width="50%" align="center">
                    {{ $getJabatanLabel($activePembina[0]) }}
                    <div style="height: 70px;"></div>
                    <strong style="text-decoration: underline;">{{ $p1['username'] ?? '(.................................................)' }}</strong><br>
                    {{ $p1['nip_label'] ?? 'NIP.' }} {{ $p1['nip'] ?? '' }}
                </td>
            </tr>
            
        @elseif($jumlahPembina === 2)
            {{-- 2 Pembina: Lebih tinggi di KIRI, lebih rendah di KANAN --}}
            {{-- activePembina[0] = terendah (kanan), activePembina[1] = tertinggi (kiri) --}}
            @php 
                $pRight = $getPembinaData($activePembina[0]); // Terendah
                $pLeft = $getPembinaData($activePembina[1]);  // Tertinggi
            @endphp
            <tr>
                <td width="50%" align="center">
                    {{ $getJabatanLabel($activePembina[1]) }}
                    <div style="height: 70px;"></div>
                    <strong style="text-decoration: underline;">{{ $pLeft['username'] ?? '(.................................................)' }}</strong><br>
                    {{ $pLeft['nip_label'] ?? 'NIP.' }} {{ $pLeft['nip'] ?? '' }}
                </td>
                <td width="50%" align="center">
                    {{ $getJabatanLabel($activePembina[0]) }}
                    <div style="height: 70px;"></div>
                    <strong style="text-decoration: underline;">{{ $pRight['username'] ?? '(.................................................)' }}</strong><br>
                    {{ $pRight['nip_label'] ?? 'NIP.' }} {{ $pRight['nip'] ?? '' }}
                </td>
            </tr>
            
        @elseif($jumlahPembina === 3)
            {{-- 3 Pembina: Terendah KANAN, menengah KIRI, Tertinggi BAWAH TENGAH --}}
            {{-- activePembina: [0]=terendah, [1]=menengah, [2]=tertinggi --}}
            @php 
                $pRight = $getPembinaData($activePembina[0]);  // Terendah
                $pLeft = $getPembinaData($activePembina[1]);   // Menengah
                $pBottom = $getPembinaData($activePembina[2]); // Tertinggi
            @endphp
            <tr>
                <td width="50%" align="center">
                    {{ $getJabatanLabel($activePembina[1]) }}
                    <div style="height: 70px;"></div>
                    <strong style="text-decoration: underline;">{{ $pLeft['username'] ?? '(.................................................)' }}</strong><br>
                    {{ $pLeft['nip_label'] ?? 'NIP.' }} {{ $pLeft['nip'] ?? '' }}
                </td>
                <td width="50%" align="center">
                    {{ $getJabatanLabel($activePembina[0]) }}
                    <div style="height: 70px;"></div>
                    <strong style="text-decoration: underline;">{{ $pRight['username'] ?? '(.................................................)' }}</strong><br>
                    {{ $pRight['nip_label'] ?? 'NIP.' }} {{ $pRight['nip'] ?? '' }}
                </td>
            </tr>
            <tr>
                <td colspan="2" align="center" style="padding-top: 40px;">
                    Mengetahui<br>
                    {{ $getJabatanLabel($activePembina[2]) }}
                    <div style="height: 70px;"></div>
                    <strong style="text-decoration: underline;">{{ $pBottom['username'] ?? '(.................................................)' }}</strong><br>
                    {{ $pBottom['nip_label'] ?? 'NIP.' }} {{ $pBottom['nip'] ?? '' }}
                </td>
            </tr>
            
        @elseif($jumlahPembina >= 4)
            {{-- 4+ Pembina: Dua baris --}}
            {{-- Baris 1: Kaprodi (kiri), Wali Kelas (kanan) --}}
            {{-- Baris 2: Waka (kiri), Kepala Sekolah (kanan) dengan "Mengetahui" --}}
            {{-- activePembina: [0]=Wali Kelas, [1]=Kaprodi, [2]=Waka, [3]=Kepala Sekolah --}}
            @php 
                $p0 = $getPembinaData($activePembina[0] ?? null); // Wali Kelas
                $p1 = $getPembinaData($activePembina[1] ?? null); // Kaprodi
                $p2 = $getPembinaData($activePembina[2] ?? null); // Waka
                $p3 = $getPembinaData($activePembina[3] ?? null); // Kepala Sekolah
            @endphp
            {{-- Baris 1: Kaprodi (kiri) + Wali Kelas (kanan) --}}
            <tr>
                <td width="50%" align="center">
                    {{ $getJabatanLabel($activePembina[1]) }}
                    <div style="height: 70px;"></div>
                    <strong style="text-decoration: underline;">{{ $p1['username'] ?? '(.................................................)' }}</strong><br>
                    {{ $p1['nip_label'] ?? 'NIP.' }} {{ $p1['nip'] ?? '' }}
                </td>
                <td width="50%" align="center">
                    {{ $getJabatanLabel($activePembina[0]) }}
                    <div style="height: 70px;"></div>
                    <strong style="text-decoration: underline;">{{ $p0['username'] ?? '(.................................................)' }}</strong><br>
                    {{ $p0['nip_label'] ?? 'NIP.' }} {{ $p0['nip'] ?? '' }}
                </td>
            </tr>
            {{-- Baris 2: Waka (kiri) + Kepala Sekolah (kanan dengan Mengetahui) --}}
            <tr>
                <td width="50%" align="center" style="padding-top: 40px;">
                    {{ $getJabatanLabel($activePembina[2]) }}
                    <div style="height: 70px;"></div>
                    <strong style="text-decoration: underline;">{{ $p2['username'] ?? '(.................................................)' }}</strong><br>
                    {{ $p2['nip_label'] ?? 'NIP.' }} {{ $p2['nip'] ?? '' }}
                </td>
                <td width="50%" align="center" style="padding-top: 40px;">
                    Mengetahui<br>
                    {{ $getJabatanLabel($activePembina[3]) }}
                    <div style="height: 70px;"></div>
                    <strong style="text-decoration: underline;">{{ $p3['username'] ?? '(.................................................)' }}</strong><br>
                    {{ $p3['nip_label'] ?? 'NIP.' }} {{ $p3['nip'] ?? '' }}
                </td>
            </tr>
        @else
            {{-- Fallback: Tidak ada pembina (seharusnya tidak terjadi) --}}
            <tr>
                <td colspan="2" align="center">
                    <em>Data pembina tidak tersedia</em>
                </td>
            </tr>
        @endif
        
    </table>

@if(!$isPreviewMode)
</body>
</html>
@else
</div>
@endif
