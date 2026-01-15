<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\JadwalMengajar;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\TemplateJam;
use App\Models\PeriodeSemester;
use App\Models\User;

/**
 * Jadwal Mengajar Seeder
 * 
 * Mengisi jadwal mengajar untuk semua kelas berdasarkan:
 * - Periode semester aktif
 * - Template jam yang sudah dibuat
 * - Mata pelajaran yang tersedia
 * - Guru pengampu yang sudah di-assign
 */
class JadwalMengajarSeeder extends Seeder
{
    public function run(): void
    {
        // Get active periode
        $periode = PeriodeSemester::where('is_active', true)->first();
        
        if (!$periode) {
            $this->command->error('Tidak ada periode semester aktif! Buat periode semester terlebih dahulu.');
            return;
        }

        $this->command->info("📅 Periode: {$periode->nama_periode}");

        // Get all kelas
        $kelasList = Kelas::orderBy('tingkat')->orderBy('nama_kelas')->get();
        
        if ($kelasList->isEmpty()) {
            $this->command->error('Tidak ada kelas! Jalankan KelasSeeder terlebih dahulu.');
            return;
        }

        $this->command->info("🏫 Total Kelas: {$kelasList->count()}");

        // Get mata pelajaran by kelompok
        $mapelUmum = MataPelajaran::where('kelompok', 'A')->where('is_active', true)->get();
        $mapelKejuruan = MataPelajaran::where('kelompok', 'B')->where('is_active', true)->get();
        $mapelMuatanLokal = MataPelajaran::where('kelompok', 'C')->where('is_active', true)->get();

        $this->command->info("📚 Mapel Umum: {$mapelUmum->count()}, Kejuruan: {$mapelKejuruan->count()}, Muatan Lokal: {$mapelMuatanLokal->count()}");

        // Get hari list
        $hariList = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

        $totalCreated = 0;

        foreach ($kelasList as $kelas) {
            $this->command->info("  → Mengisi jadwal untuk {$kelas->nama_kelas}...");
            
            // Determine tingkat for mapel distribution
            $tingkat = $kelas->tingkat;
            
            // Create jadwal for each day
            foreach ($hariList as $hari) {
                // Get template jam for this day (only pelajaran type)
                $templateJams = TemplateJam::where('periode_semester_id', $periode->id)
                    ->where('hari', $hari)
                    ->where('tipe', 'pelajaran')
                    ->where('is_active', true)
                    ->orderBy('urutan')
                    ->get();

                if ($templateJams->isEmpty()) {
                    continue;
                }

                // Prepare mapel list for this kelas
                // All kelas get Umum, all get some Kejuruan, all get some Muatan Lokal
                $mapelPool = collect();
                
                // Always include all Umum subjects
                $mapelPool = $mapelPool->merge($mapelUmum);
                
                // Include kejuruan based on tingkat
                if ($tingkat === 'X') {
                    // Kelas X: Basic kejuruan subjects
                    $mapelPool = $mapelPool->merge($mapelKejuruan->whereIn('kode_mapel', [
                        'DDPPLG', 'SISKOM', 'KJD', 'PROGDAS', 'DGD', 'SIMDIG', 'K3LH'
                    ]));
                } elseif ($tingkat === 'XI') {
                    // Kelas XI: Intermediate kejuruan
                    $mapelPool = $mapelPool->merge($mapelKejuruan->whereIn('kode_mapel', [
                        'DDIT', 'PWPB', 'PBO', 'BASDAT', 'PPL', 'PKK', 'KPL'
                    ]));
                } else {
                    // Kelas XII: Advanced kejuruan
                    $mapelPool = $mapelPool->merge($mapelKejuruan->whereIn('kode_mapel', [
                        'WEB', 'MOB', 'BD', 'IOT', 'CLOUD', 'CS', 'AI', 'DKV', 'TFA'
                    ]));
                }
                
                // Include some muatan lokal
                $mapelPool = $mapelPool->merge($mapelMuatanLokal->take(3));

                // Shuffle mapel pool for variety
                $mapelPool = $mapelPool->shuffle()->values();

                $mapelIndex = 0;
                $mapelCount = $mapelPool->count();

                foreach ($templateJams as $templateJam) {
                    // Get current mapel (cycle through pool)
                    $mapel = $mapelPool[$mapelIndex % $mapelCount];
                    
                    // Get guru for this mapel
                    $guru = $mapel->guruPengampu->first();
                    
                    if (!$guru) {
                        // Skip if no guru assigned
                        $mapelIndex++;
                        continue;
                    }

                    // Check if already exists
                    $exists = JadwalMengajar::where('periode_semester_id', $periode->id)
                        ->where('kelas_id', $kelas->id)
                        ->where('template_jam_id', $templateJam->id)
                        ->exists();

                    if (!$exists) {
                        JadwalMengajar::create([
                            'periode_semester_id' => $periode->id,
                            'kelas_id' => $kelas->id,
                            'template_jam_id' => $templateJam->id,
                            'mata_pelajaran_id' => $mapel->id,
                            'user_id' => $guru->id,
                        ]);
                        $totalCreated++;
                    }

                    $mapelIndex++;
                }
            }
        }

        $this->command->info('');
        $this->command->info("✅ Jadwal Mengajar seeded:");
        $this->command->info("   - {$totalCreated} jadwal created");
        $this->command->info("   - {$kelasList->count()} kelas");
        $this->command->info("   - 6 hari (Senin-Sabtu)");
    }
}
