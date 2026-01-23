<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\JadwalMengajar;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\TemplateJam;
use App\Models\PeriodeSemester;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Jadwal Mengajar Seeder (Fixed - No Conflicts)
 * 
 * Rules:
 * - Satu guru hanya mengajar di SATU kelas pada satu waktu
 * - Jadwal dalam blok (2-3 jam berturut untuk satu mapel)
 */
class JadwalMengajarSeeder extends Seeder
{
    private int $periodeId;
    
    public function run(): void
    {
        // Clear existing data
        JadwalMengajar::query()->forceDelete();
        
        // Get active periode
        $periode = PeriodeSemester::where('is_active', true)->first();
        
        if (!$periode) {
            $this->command->error('Tidak ada periode semester aktif!');
            return;
        }

        $this->periodeId = $periode->id;
        $this->command->info("📅 Periode: {$periode->nama_periode}");

        // Get all kelas grouped by tingkat
        $kelasX = Kelas::where('tingkat', 'X')->orderBy('nama_kelas')->get();
        $kelasXI = Kelas::where('tingkat', 'XI')->orderBy('nama_kelas')->get();
        $kelasXII = Kelas::where('tingkat', 'XII')->orderBy('nama_kelas')->get();

        $this->command->info("🏫 Kelas: X({$kelasX->count()}), XI({$kelasXI->count()}), XII({$kelasXII->count()})");

        // Get all template jam per hari (pelajaran only)
        $hariList = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
        $templatesByHari = [];
        
        foreach ($hariList as $hari) {
            $templatesByHari[$hari] = TemplateJam::where('periode_semester_id', $periode->id)
                ->where('hari', $hari)
                ->where('tipe', 'pelajaran')
                ->where('is_active', true)
                ->orderBy('urutan')
                ->get();
        }

        $totalCreated = 0;
        
        // Process tingkat by tingkat
        $tingkatKelas = [
            'X' => $kelasX,
            'XI' => $kelasXI,
            'XII' => $kelasXII,
        ];

        foreach ($tingkatKelas as $tingkat => $kelasList) {
            $this->command->info("📚 Processing Tingkat {$tingkat}...");
            
            // Get mapel pool for this tingkat
            $mapelPool = $this->getMapelForTingkat($tingkat);
            
            foreach ($kelasList as $kelasIndex => $kelas) {
                $this->command->info("  → Jadwal untuk {$kelas->nama_kelas}");
                
                foreach ($hariList as $hari) {
                    $templates = $templatesByHari[$hari]->values();
                    $templateCount = $templates->count();
                    
                    if ($templateCount === 0) continue;

                    // Shuffle mapel for variety per kelas per hari
                    $shuffledMapel = $mapelPool->shuffle()->values();
                    
                    $slotIndex = 0;
                    $mapelIndex = 0;
                    
                    while ($slotIndex < $templateCount && $mapelIndex < $shuffledMapel->count()) {
                        // Determine block size (2 or 3 slots)
                        $remainingSlots = $templateCount - $slotIndex;
                        $blockSize = $remainingSlots >= 3 ? rand(2, 3) : min(2, $remainingSlots);
                        
                        // Get mapel
                        $mapel = $shuffledMapel[$mapelIndex];
                        
                        // Get templates for this block
                        $blockTemplates = $templates->slice($slotIndex, $blockSize)->values();
                        
                        // Find available guru for ALL slots in this block
                        $guru = $this->findAvailableGuru($mapel, $blockTemplates);
                        
                        if ($guru) {
                            // Create jadwal for this block
                            foreach ($blockTemplates as $template) {
                                JadwalMengajar::create([
                                    'periode_semester_id' => $this->periodeId,
                                    'kelas_id' => $kelas->id,
                                    'template_jam_id' => $template->id,
                                    'mata_pelajaran_id' => $mapel->id,
                                    'user_id' => $guru->id,
                                ]);
                                $totalCreated++;
                            }
                            $slotIndex += $blockSize;
                        } else {
                            // No available guru, skip this slot
                            $slotIndex++;
                        }
                        
                        $mapelIndex++;
                    }
                }
            }
        }

        $this->command->info('');
        $this->command->info("✅ Total jadwal created: {$totalCreated}");
        
        // Verify no conflicts
        $this->verifyNoConflicts();
    }

    /**
     * Get mapel based on tingkat
     */
    private function getMapelForTingkat(string $tingkat): Collection
    {
        $mapelUmum = MataPelajaran::where('kelompok', 'A')
            ->where('is_active', true)
            ->with('guruPengampu')
            ->get();
            
        $mapelKejuruan = MataPelajaran::where('kelompok', 'B')
            ->where('is_active', true)
            ->with('guruPengampu')
            ->get();
        
        $pool = collect();
        
        // Always include umum
        $pool = $pool->merge($mapelUmum);
        
        // Add kejuruan based on tingkat
        if ($tingkat === 'X') {
            $pool = $pool->merge($mapelKejuruan->whereIn('kode_mapel', [
                'DDPPLG', 'SISKOM', 'KJD', 'PROGDAS', 'DGD', 'SIMDIG', 'K3LH'
            ]));
        } elseif ($tingkat === 'XI') {
            $pool = $pool->merge($mapelKejuruan->whereIn('kode_mapel', [
                'DDIT', 'PWPB', 'PBO', 'BASDAT', 'PPL', 'PKK', 'KPL'
            ]));
        } else {
            $pool = $pool->merge($mapelKejuruan->whereIn('kode_mapel', [
                'WEB', 'MOB', 'BD', 'IOT', 'CLOUD', 'CS', 'AI', 'DKV'
            ]));
        }
        
        // Filter only mapel with guru assigned
        return $pool->filter(fn($m) => $m->guruPengampu->isNotEmpty())->values();
    }

    /**
     * Find available guru for mapel at specific time slots
     * Checks database to ensure no conflicts
     */
    private function findAvailableGuru($mapel, Collection $templates): ?User
    {
        $templateIds = $templates->pluck('id')->toArray();
        
        // Get all guru that can teach this mapel
        $guruForMapel = $mapel->guruPengampu;
        
        if ($guruForMapel->isEmpty()) {
            return null;
        }

        // Check each guru if available for ALL slots in the block
        foreach ($guruForMapel as $guru) {
            // Check if guru already has jadwal in any of these template_jam_ids
            $hasConflict = JadwalMengajar::where('periode_semester_id', $this->periodeId)
                ->where('user_id', $guru->id)
                ->whereIn('template_jam_id', $templateIds)
                ->exists();
            
            if (!$hasConflict) {
                return $guru;
            }
        }

        return null;
    }
    
    /**
     * Verify no conflicts exist
     */
    private function verifyNoConflicts(): void
    {
        $conflicts = JadwalMengajar::where('periode_semester_id', $this->periodeId)
            ->selectRaw('user_id, template_jam_id, COUNT(*) as count')
            ->groupBy('user_id', 'template_jam_id')
            ->havingRaw('COUNT(*) > 1')
            ->count();
        
        if ($conflicts > 0) {
            $this->command->error("⚠️ WARNING: Found {$conflicts} conflict groups!");
        } else {
            $this->command->info("✅ No conflicts detected!");
        }
    }
}
