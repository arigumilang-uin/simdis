<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JadwalMengajar;
use App\Models\Jurusan;
use App\Models\TemplateJam;
use App\Models\PeriodeSemester;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

/**
 * Jadwal PDF Controller
 * 
 * Generate PDF jadwal pelajaran lengkap
 */
class JadwalPdfController extends Controller
{
    /**
     * Build data for PDF view
     */
    private function getPdfData()
    {
        // Get active periode
        $periode = PeriodeSemester::where('is_active', true)->first();
        
        if (!$periode) {
            return null;
        }

        // Get all jurusan with their kelas
        $jurusans = Jurusan::with(['kelas' => function($q) {
            $q->orderBy('tingkat')->orderBy('nama_kelas');
        }])->orderBy('nama_jurusan')->get();

        // Get all hari from template jam
        $hariList = TemplateJam::where('periode_semester_id', $periode->id)
            ->distinct()
            ->orderByRaw("FIELD(hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu')")
            ->pluck('hari');

        // Build jadwal data structure
        $jadwalData = [];
        $romanNumerals = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 
                          'XI', 'XII', 'XIII', 'XIV', 'XV', 'XVI', 'XVII', 'XVIII', 'XIX', 'XX'];

        foreach ($hariList as $hari) {
            // Convert hari to string if it's a backed enum
            $hariStr = $hari instanceof \BackedEnum ? $hari->value : $hari;
            
            // Get template jam for this hari
            $templateJams = TemplateJam::where('periode_semester_id', $periode->id)
                ->where('hari', $hari)
                ->orderBy('urutan')
                ->get();

            $jamCounter = 0;
            
            foreach ($templateJams as $template) {
                // Convert tipe to string
                $tipeStr = $template->tipe instanceof \BackedEnum 
                    ? $template->tipe->value 
                    : $template->tipe;
                
                $jamKe = '-';
                if ($tipeStr === 'pelajaran') {
                    $jamCounter++;
                    $jamKe = $romanNumerals[$jamCounter - 1] ?? $jamCounter;
                }

                $jamMulai = $template->jam_mulai instanceof \DateTime 
                    ? $template->jam_mulai->format('H:i') 
                    : substr($template->jam_mulai, 0, 5);
                $jamSelesai = $template->jam_selesai instanceof \DateTime 
                    ? $template->jam_selesai->format('H:i') 
                    : substr($template->jam_selesai, 0, 5);

                $rowData = [
                    'hari' => $hariStr,
                    'waktu' => $jamMulai . ' - ' . $jamSelesai,
                    'jam_ke' => $jamKe,
                    'tipe' => $tipeStr,
                    'template_id' => $template->id,
                    'kelas' => [],
                ];

                // Get jadwal for each kelas
                foreach ($jurusans as $jurusan) {
                    foreach ($jurusan->kelas as $kelas) {
                        if ($tipeStr === 'pelajaran') {
                            $jadwal = JadwalMengajar::with(['mataPelajaran', 'guru'])
                                ->where('periode_semester_id', $periode->id)
                                ->where('kelas_id', $kelas->id)
                                ->where('template_jam_id', $template->id)
                                ->first();

                            // Use array for pelajaran data: [Mapel, Initial Guru]
                            $rowData['kelas'][$kelas->id] = [
                                'mapel' => $jadwal ? ($jadwal->mataPelajaran?->kode_mapel ?? '-') : '-',
                                'guru' => $jadwal ? $this->getInisialGuru($jadwal->guru?->username) : '',
                            ];
                        } else {
                            // For non-pelajaran, store string. View will treat it as colspan content.
                            $rowData['kelas'][$kelas->id] = strtoupper($tipeStr);
                        }
                    }
                }

                $jadwalData[] = $rowData;
            }
        }

        // Get kepala sekolah
        $kepalaSekolah = User::whereHas('role', function($q) {
            $q->where('nama_role', 'Kepala Sekolah');
        })->first();

        // School info
        $schoolName = 'SMK NEGERI 1 LUBUK DALAM';
        $semesterValue = $periode->semester instanceof \App\Enums\Semester 
            ? $periode->semester->value 
            : $periode->semester;
        $semester = strtoupper($semesterValue);
        $tahunAjaran = $this->getTahunAjaran($periode);

        return [
            'schoolName' => $schoolName,
            'semester' => $semester,
            'tahunAjaran' => $tahunAjaran,
            'periode' => $periode,
            'jurusans' => $jurusans,
            'jadwalData' => $jadwalData,
            'hariList' => $hariList,
            'kepalaSekolah' => $kepalaSekolah,
        ];
    }

    /**
     * Generate PDF jadwal pelajaran (Download)
     */
    public function generate(Request $request)
    {
        $data = $this->getPdfData();
        
        if (!$data) {
            return back()->with('error', 'Tidak ada periode semester aktif.');
        }

        $pdf = Pdf::loadView('admin.jadwal-mengajar.pdf', $data);

        // Set paper to landscape A2 for wide table (594mm x 420mm)
        $pdf->setPaper([0, 0, 1683.78, 1190.55], 'landscape');
        
        $pdf->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'defaultFont' => 'Times New Roman',
        ]);

        $filename = 'Jadwal_Pelajaran_' . str_replace(' ', '_', $data['schoolName']) . '_' . $data['semester'] . '_' . str_replace('/', '-', $data['tahunAjaran']) . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Preview PDF in browser
     */
    public function preview(Request $request)
    {
        $data = $this->getPdfData();
        
        if (!$data) {
            return back()->with('error', 'Tidak ada periode semester aktif.');
        }

        $pdf = Pdf::loadView('admin.jadwal-mengajar.pdf', $data);

        // Set paper to landscape A2 (594mm x 420mm)
        $pdf->setPaper([0, 0, 1683.78, 1190.55], 'landscape');
        
        return $pdf->stream('Jadwal_Pelajaran_Preview.pdf');
    }

    /**
     * Get tahun ajaran from periode
     */
    private function getTahunAjaran(PeriodeSemester $periode): string
    {
        $year = $periode->tanggal_mulai->year;
        
        $semesterValue = $periode->semester instanceof \App\Enums\Semester 
            ? $periode->semester->value 
            : $periode->semester;
        
        if ($semesterValue === 'Ganjil') {
            return $year . '/' . ($year + 1);
        } else {
            return ($year - 1) . '/' . $year;
        }
    }

    /**
     * Helper: Get Inisial Guru from Username (Smart Logic)
     * "Joko, S.Pd., M.Pd" -> "JOK" (Single "Joko")
     * "Ari Gumilang" -> "AG"
     * "Dr. Susilo Bambang" -> "SB"
     */
    private function getInisialGuru(?string $username): string
    {
        if (empty($username)) return '';
        
        // 1. Remove academic titles after comma (e.g., ", S.Pd")
        $parts = explode(',', $username);
        $cleanName = trim($parts[0]);

        // 2. Remove common title prefixes (case-insensitive)
        $prefixes = ['Dr.', 'Dra.', 'Ir.', 'Prof.', 'H.', 'Hj.', 'Drs.', 'Ners.', 'Ns.'];
        foreach ($prefixes as $prefix) {
            if (stripos($cleanName, $prefix) === 0) {
                $cleanName = trim(substr($cleanName, strlen($prefix)));
            }
        }

        // 3. Remove punctuation residue and extra spaces
        $cleanName = trim(preg_replace('/\s+/', ' ', $cleanName));
        $cleanName = str_replace(['.', '-'], '', $cleanName); // Optional: remove dots in name parts like M. Yamin? No, M. is part of name.
        // Let's keep M. Yamin as M Yamin logic or keep M.
        // Revert str_replace dots, as "M. Yamin" should be "MY" not "M" if we handle it right.
        
        $words = explode(' ', $cleanName);
        
        // Filter empty words
        $words = array_filter($words, fn($w) => !empty($w));
        
        // 4. Generate Initials
        if (count($words) === 1) {
            // If single word, take first 3 letters (e.g. "Joko" -> "JOK")
            $word = reset($words);
            return strtoupper(substr($word, 0, 3));
        }
        
        $initials = '';
        foreach ($words as $w) {
            // Skip words that look like titles (contain dots and length > 2) just in case they weren't comma separated
            // e.g. "Joko S.Pd" (no comma)
            if (str_contains($w, '.') && strlen($w) > 2) {
                continue;
            }
            
            $initials .= mb_substr($w, 0, 1);
        }
        
        return strtoupper($initials);
    }
}
