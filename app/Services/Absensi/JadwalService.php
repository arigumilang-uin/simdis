<?php

namespace App\Services\Absensi;

use App\Enums\Hari;
use App\Models\JadwalMengajar;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\PeriodeSemester;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Jadwal Service
 * 
 * Logic untuk manajemen jadwal mengajar.
 * 
 * UPDATED: Menggunakan schema baru dengan template_jam dan periode_semester_id
 */
class JadwalService
{
    /**
     * Get jadwal hari ini untuk seorang guru
     */
    public function getJadwalHariIniForGuru(int $userId): Collection
    {
        $hariIni = Hari::today();
        
        if (!$hariIni) {
            return collect(); // Hari Minggu
        }

        $periode = PeriodeSemester::current();
        if (!$periode) {
            return collect();
        }

        return JadwalMengajar::with(['mataPelajaran', 'kelas.jurusan', 'templateJam'])
            ->where('jadwal_mengajar.is_active', true)
            ->where('jadwal_mengajar.periode_semester_id', $periode->id)
            ->where('jadwal_mengajar.user_id', $userId)
            ->whereHas('templateJam', function($q) use ($hariIni) {
                $q->where('hari', $hariIni->value);
            })
            ->join('template_jam', 'jadwal_mengajar.template_jam_id', '=', 'template_jam.id')
            ->orderBy('template_jam.urutan')
            ->select('jadwal_mengajar.*')
            ->get();
    }

    /**
     * Get all jadwal for a guru in current period
     * Merges consecutive time slots with same kelas, mapel, guru into one entry
     */
    public function getJadwalForGuru(int $userId): Collection
    {
        $periode = PeriodeSemester::current();
        if (!$periode) {
            return collect();
        }

        $jadwalList = JadwalMengajar::with(['mataPelajaran', 'kelas.jurusan', 'templateJam'])
            ->where('jadwal_mengajar.is_active', true)
            ->where('jadwal_mengajar.periode_semester_id', $periode->id)
            ->where('jadwal_mengajar.user_id', $userId)
            ->join('template_jam', 'jadwal_mengajar.template_jam_id', '=', 'template_jam.id')
            ->orderBy('template_jam.hari')
            ->orderBy('template_jam.urutan')
            ->select('jadwal_mengajar.*')
            ->get();

        // Merge consecutive jadwal
        $merged = $this->mergeConsecutiveJadwal($jadwalList);

        return $merged->groupBy(function($jadwal) {
            $hari = $jadwal->templateJam?->hari;
            return $hari instanceof \App\Enums\Hari ? $hari->value : ($hari ?? 'Unknown');
        });
    }

    /**
     * Merge jadwal with same kelas, mapel, guru, hari into single entry
     * Even if there's a break (istirahat) in between
     */
    private function mergeConsecutiveJadwal(Collection $jadwalList): Collection
    {
        $grouped = [];
        
        foreach ($jadwalList as $jadwal) {
            $hari = $jadwal->templateJam?->hari;
            $hariValue = $hari instanceof \App\Enums\Hari ? $hari->value : ($hari ?? 'Unknown');
            $key = $hariValue . '_' . $jadwal->kelas_id . '_' . $jadwal->mata_pelajaran_id;
            
            if (!isset($grouped[$key])) {
                $grouped[$key] = [
                    'item' => $jadwal,
                    'jadwal_ids' => [],
                    'time_slots' => [],
                    'urutan_list' => [],
                    'hari' => $hariValue,
                    'periode_id' => $jadwal->periode_semester_id,
                ];
            }
            
            $grouped[$key]['jadwal_ids'][] = $jadwal->id;
            $grouped[$key]['urutan_list'][] = $jadwal->templateJam?->urutan ?? 0;
            $grouped[$key]['time_slots'][] = [
                'urutan' => $jadwal->templateJam?->urutan ?? 0,
                'jam_mulai' => $jadwal->templateJam?->jam_mulai,
                'jam_selesai' => $jadwal->templateJam?->jam_selesai,
                'label' => $jadwal->templateJam?->label,
            ];
        }
        
        $result = collect();
        
        foreach ($grouped as $key => $data) {
            $item = $data['item'];
            $slots = $data['time_slots'];
            
            // Sort slots by urutan
            usort($slots, fn($a, $b) => ($a['urutan'] ?? 0) <=> ($b['urutan'] ?? 0));
            
            // Calculate merged time range
            $firstSlot = $slots[0] ?? null;
            $lastSlot = end($slots) ?: $firstSlot;
            
            // Merge consecutive slots into sessions and detect breaks
            $sessions = [];
            $breaks = [];
            $currentSession = null;
            
            $prevUrutan = null;
            foreach ($slots as $slot) {
                $urutan = $slot['urutan'] ?? 0;
                
                if ($prevUrutan === null || $urutan == $prevUrutan + 1) {
                    // Continue current session
                    if ($currentSession === null) {
                        $currentSession = ['start' => $slot['jam_mulai'], 'end' => $slot['jam_selesai'], 'start_urutan' => $urutan];
                    } else {
                        $currentSession['end'] = $slot['jam_selesai'];
                    }
                    $currentSession['end_urutan'] = $urutan;
                } else {
                    // Gap detected - find what's in the gap
                    if ($currentSession) {
                        $sessions[] = $currentSession;
                        
                        // Find break slots between sessions
                        $breakSlots = \App\Models\TemplateJam::where('periode_semester_id', $data['periode_id'])
                            ->where('hari', $data['hari'])
                            ->where('urutan', '>', $currentSession['end_urutan'])
                            ->where('urutan', '<', $urutan)
                            ->whereIn('tipe', ['istirahat', 'ishoma', 'upacara'])
                            ->orderBy('urutan')
                            ->get(['tipe', 'label', 'jam_mulai', 'jam_selesai']);
                        
                        foreach ($breakSlots as $breakSlot) {
                            $breaks[] = [
                                'tipe' => $breakSlot->tipe,
                                'label' => $breakSlot->label,
                                'jam_mulai' => $breakSlot->jam_mulai,
                                'jam_selesai' => $breakSlot->jam_selesai,
                            ];
                        }
                    }
                    $currentSession = ['start' => $slot['jam_mulai'], 'end' => $slot['jam_selesai'], 'start_urutan' => $urutan, 'end_urutan' => $urutan];
                }
                $prevUrutan = $urutan;
            }
            if ($currentSession) {
                $sessions[] = $currentSession;
            }
            
            // Format time display with break info inline
            $timeDisplayParts = [];
            $breakIndex = 0;
            foreach ($sessions as $idx => $s) {
                $start = $s['start'] instanceof \DateTime ? $s['start']->format('H:i') : substr($s['start'] ?? '', 0, 5);
                $end = $s['end'] instanceof \DateTime ? $s['end']->format('H:i') : substr($s['end'] ?? '', 0, 5);
                
                // Add session time
                $timeDisplayParts[] = $start . '-' . $end;
                
                // Add break info if there's a next session
                if ($idx < count($sessions) - 1 && isset($breaks[$breakIndex])) {
                    $breakType = ucfirst($breaks[$breakIndex]['tipe'] ?? 'Istirahat');
                    $timeDisplayParts[] = '[' . $breakType . ']';
                    $breakIndex++;
                }
            }
            $timeDisplay = implode(' ', $timeDisplayParts);
            
            // Format break info (summary)
            $breakInfo = collect($breaks)->map(function($b) {
                return ucfirst($b['tipe'] ?? 'Istirahat');
            })->unique()->implode(', ');
            
            // Set attributes
            $item->setAttribute('merged_jadwal_ids', $data['jadwal_ids']);
            $item->setAttribute('merged_jam_mulai', $firstSlot['jam_mulai'] ?? null);
            $item->setAttribute('merged_jam_selesai', $lastSlot['jam_selesai'] ?? null);
            $item->setAttribute('time_slots', $slots);
            $item->setAttribute('time_display', $timeDisplay);
            $item->setAttribute('session_count', count($sessions));
            $item->setAttribute('breaks', $breaks);
            $item->setAttribute('break_info', $breakInfo);
            $item->setAttribute('totalPertemuan', $item->pertemuan()->count());
            
            $result->push($item);
        }
        
        return $result;
    }

    /**
     * Get jadwal for a kelas in current period
     */
    public function getJadwalForKelas(int $kelasId): Collection
    {
        $periode = PeriodeSemester::current();
        if (!$periode) {
            return collect();
        }

        return JadwalMengajar::with(['mataPelajaran', 'guru', 'templateJam'])
            ->where('jadwal_mengajar.is_active', true)
            ->where('jadwal_mengajar.periode_semester_id', $periode->id)
            ->where('jadwal_mengajar.kelas_id', $kelasId)
            ->join('template_jam', 'jadwal_mengajar.template_jam_id', '=', 'template_jam.id')
            ->orderBy('template_jam.hari')
            ->orderBy('template_jam.urutan')
            ->select('jadwal_mengajar.*')
            ->get()
            ->groupBy(function($jadwal) {
                $hari = $jadwal->templateJam?->hari;
                return $hari instanceof \App\Enums\Hari ? $hari->value : ($hari ?? 'Unknown');
            });
    }

    /**
     * Create new jadwal
     */
    public function createJadwal(array $data): JadwalMengajar
    {
        // Set default periode_semester_id jika tidak ada
        if (!isset($data['periode_semester_id'])) {
            $periode = PeriodeSemester::current();
            $data['periode_semester_id'] = $periode?->id;
        }

        return JadwalMengajar::create($data);
    }

    /**
     * Update jadwal
     */
    public function updateJadwal(int $jadwalId, array $data): JadwalMengajar
    {
        $jadwal = JadwalMengajar::findOrFail($jadwalId);
        $jadwal->update($data);
        return $jadwal->fresh();
    }

    /**
     * Delete jadwal
     */
    public function deleteJadwal(int $jadwalId): void
    {
        JadwalMengajar::findOrFail($jadwalId)->delete();
    }

    /**
     * Check for scheduling conflict (New schema version)
     * 
     * Cek 2 jenis conflict:
     * 1. Kelas tidak bisa punya 2 mata pelajaran di template_jam yang sama
     * 2. Guru tidak bisa mengajar 2 kelas di template_jam yang sama
     * 
     * @return array Array of conflict messages (empty if no conflict)
     */
    public function checkConflicts(
        int $kelasId,
        int $userId,
        int $templateJamId,
        int $periodeSemesterId,
        ?int $excludeJadwalId = null
    ): array {
        $conflicts = [];

        // 1. Check KELAS double-booking (same template_jam)
        $kelasQuery = JadwalMengajar::where('kelas_id', $kelasId)
            ->where('template_jam_id', $templateJamId)
            ->where('periode_semester_id', $periodeSemesterId);

        if ($excludeJadwalId) {
            $kelasQuery->where('id', '!=', $excludeJadwalId);
        }

        if ($kelasQuery->exists()) {
            $conflicts[] = 'Kelas sudah memiliki jadwal mata pelajaran lain di waktu tersebut';
        }

        // 2. Check GURU double-booking (same template_jam)
        $guruQuery = JadwalMengajar::where('user_id', $userId)
            ->where('template_jam_id', $templateJamId)
            ->where('periode_semester_id', $periodeSemesterId);

        if ($excludeJadwalId) {
            $guruQuery->where('id', '!=', $excludeJadwalId);
        }

        if ($guruQuery->exists()) {
            $conflicts[] = 'Guru sudah mengajar di kelas lain pada waktu tersebut';
        }

        return $conflicts;
    }

    /**
     * Get dropdown data for creating jadwal
     */
    public function getDropdownData(): array
    {
        $periode = PeriodeSemester::current();
        
        return [
            'guru' => User::whereHas('role', function($q) {
                    $q->whereIn('nama_role', ['Guru', 'Wali Kelas', 'Kaprodi', 'Waka Kesiswaan', 'Waka Kurikulum', 'Waka Sarana', 'Kepala Sekolah']);
                })
                ->where('is_active', true)
                ->orderBy('nama')
                ->get(['id', 'nama', 'username']),
            
            'mata_pelajaran' => MataPelajaran::active()
                ->orderBy('nama_mapel')
                ->get(['id', 'nama_mapel', 'kode_mapel']),
            
            'kelas' => Kelas::with('jurusan')
                ->orderBy('tingkat')
                ->orderBy('nama_kelas')
                ->get(),
            
            'hari' => Hari::forSelect(),
            
            'periode_semester' => $periode,
        ];
    }

    /**
     * Copy jadwal from another period
     */
    public function copyFromPeriod(
        int $fromPeriodeId,
        int $toPeriodeId
    ): int {
        $jadwalLama = JadwalMengajar::where('periode_semester_id', $fromPeriodeId)->get();

        $copied = 0;
        foreach ($jadwalLama as $jadwal) {
            // Check if template_jam exists in target period
            // For now, skip the check - assume same template_jam structure
            JadwalMengajar::create([
                'user_id' => $jadwal->user_id,
                'mata_pelajaran_id' => $jadwal->mata_pelajaran_id,
                'kelas_id' => $jadwal->kelas_id,
                'template_jam_id' => $jadwal->template_jam_id,
                'periode_semester_id' => $toPeriodeId,
                'is_active' => true,
            ]);
            $copied++;
        }

        return $copied;
    }
}
