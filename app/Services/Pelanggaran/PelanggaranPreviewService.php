<?php

namespace App\Services\Pelanggaran;

use App\Models\Siswa;
use App\Models\JenisPelanggaran;
use App\Models\RiwayatPelanggaran;
use App\Models\TindakLanjut;

/**
 * Service untuk preview dampak pencatatan pelanggaran sebelum submit.
 * 
 * Tanggung jawab:
 * - Simulasi evaluasi rules engine tanpa menyimpan data
 * - Memberikan warning/info tentang dampak pencatatan
 * - Mendeteksi high-impact scenarios
 * 
 * Design Pattern: Service Layer (Read-only simulation)
 */
class PelanggaranPreviewService
{
    /**
     * Preview dampak pencatatan pelanggaran untuk multiple siswa.
     * 
     * @param array $siswaIds
     * @param array $pelanggaranIds
     * @return array
     */
    public function previewImpact(array $siswaIds, array $pelanggaranIds): array
    {
        $totalRecords = count($siswaIds) * count($pelanggaranIds);
        $warnings = [];
        $infos = [];
        $requiresConfirmation = false;

        // Load data yang diperlukan
        $siswaList = Siswa::with(['kelas', 'riwayatPelanggaran.jenisPelanggaran'])
            ->whereIn('id', $siswaIds)
            ->get();
        
        $pelanggaranList = JenisPelanggaran::with('frequencyRules')
            ->whereIn('id', $pelanggaranIds)
            ->get();

        // Evaluasi dampak per siswa
        foreach ($siswaList as $siswa) {
            $siswaImpact = $this->evaluateSiswaImpact($siswa, $pelanggaranList);
            
            if ($siswaImpact['has_warning']) {
                $warnings[] = $siswaImpact['warning'];
                $requiresConfirmation = true;
            }
            
            if ($siswaImpact['has_info']) {
                $infos[] = $siswaImpact['info'];
            }
        }

        return [
            'total_records' => $totalRecords,
            'total_siswa' => count($siswaIds),
            'total_pelanggaran' => count($pelanggaranIds),
            'warnings' => $warnings,
            'infos' => $infos,
            'requires_confirmation' => $requiresConfirmation,
        ];
    }

    /**
     * Evaluasi dampak untuk satu siswa.
     * 
     * @param Siswa $siswa
     * @param \Illuminate\Support\Collection $pelanggaranList
     * @return array
     */
    private function evaluateSiswaImpact(Siswa $siswa, $pelanggaranList): array
    {
        $hasWarning = false;
        $hasInfo = false;
        $warning = '';
        $info = '';

        // Cek apakah siswa sudah punya kasus aktif
        $kasusAktif = TindakLanjut::where('siswa_id', $siswa->id)
            ->whereIn('status', ['Baru', 'Menunggu Persetujuan', 'Disetujui', 'Ditangani'])
            ->with('suratPanggilan')
            ->first();

        if ($kasusAktif) {
            $tipeSuratLama = $kasusAktif->suratPanggilan?->tipe_surat ?? 'N/A';
            $warning = "⚠️ <strong>{$siswa->nama_siswa}</strong> sudah punya kasus aktif ({$tipeSuratLama}). Pelanggaran ini mungkin akan meng-eskalasi ke level lebih tinggi.";
            $hasWarning = true;
        }

        // Simulasi frequency check untuk setiap pelanggaran
        foreach ($pelanggaranList as $pelanggaran) {
            if (!$pelanggaran->usesFrequencyRules()) {
                continue;
            }

            // Hitung frekuensi saat ini
            $currentFrequency = RiwayatPelanggaran::where('siswa_id', $siswa->id)
                ->where('jenis_pelanggaran_id', $pelanggaran->id)
                ->count();

            // Frekuensi setelah pencatatan ini
            $newFrequency = $currentFrequency + 1;

            // Cek apakah akan trigger threshold baru
            $rules = $pelanggaran->frequencyRules;
            $matchedRule = $rules->first(function ($rule) use ($newFrequency) {
                return $rule->matchesFrequency($newFrequency);
            });

            if ($matchedRule && $matchedRule->trigger_surat) {
                $tipeSurat = $matchedRule->getSuratType();
                
                if ($tipeSurat) {
                    $warning = "⚠️ <strong>{$siswa->nama_siswa}</strong> akan mencapai threshold <strong>{$tipeSurat}</strong> untuk pelanggaran \"{$pelanggaran->nama_pelanggaran}\".";
                    $hasWarning = true;
                }
            }
        }

        // Hitung total poin akumulasi setelah pencatatan
        $currentPoin = $siswa->riwayatPelanggaran->sum(function ($riwayat) {
            return $riwayat->jenisPelanggaran->poin ?? 0;
        });

        $additionalPoin = $pelanggaranList->sum('poin');
        $newTotalPoin = $currentPoin + $additionalPoin;

        // Info tentang pembinaan internal
        if ($newTotalPoin >= 55 && $currentPoin < 55) {
            $info = "ℹ️ <strong>{$siswa->nama_siswa}</strong> akan masuk rekomendasi pembinaan internal (total poin: {$newTotalPoin}).";
            $hasInfo = true;
        }

        return [
            'has_warning' => $hasWarning,
            'has_info' => $hasInfo,
            'warning' => $warning,
            'info' => $info,
        ];
    }
}
