<?php

namespace App\Helpers;

use App\Models\RiwayatPelanggaran;
use App\Models\JenisPelanggaran;

/**
 * Helper untuk menampilkan poin yang ditambahkan per riwayat pelanggaran
 * 
 * Untuk frequency-based rules, poin tergantung pada urutan pelanggaran
 */
class PoinDisplayHelper
{
    /**
     * Get poin yang ditambahkan untuk riwayat pelanggaran tertentu
     * 
     * Logic:
     * - Hitung ini pelanggaran ke-berapa untuk siswa + jenis ini
     * - Check apakah frequency tersebut match dengan rule
     * - Return poin yang ditambahkan
     * 
     * @param RiwayatPelanggaran $riwayat
     * @return array ['poin' => int, 'frequency' => int, 'matched' => bool]
     */
    public static function getPoinForRiwayat(RiwayatPelanggaran $riwayat): array
    {
        $jenis = $riwayat->jenisPelanggaran;
        
        // Legacy rules: simple poin
        if (!$jenis->usesFrequencyRules()) {
            return [
                'poin' => $jenis->poin,
                'frequency' => null,
                'matched' => true,
            ];
        }
        
        // Frequency-based: calculate position
        if (isset($riwayat->calculated_frequency)) {
            // OPTIMIZATION: Use pre-calculated frequency from subquery
            $frequency = $riwayat->calculated_frequency;
        } else {
            // Count violations BEFORE or AT this one (by tanggal_kejadian)
            $frequency = RiwayatPelanggaran::where('siswa_id', $riwayat->siswa_id)
                ->where('jenis_pelanggaran_id', $riwayat->jenis_pelanggaran_id)
                ->where('tanggal_kejadian', '<=', $riwayat->tanggal_kejadian)
                ->where('id', '<=', $riwayat->id) // Include same timestamp, use ID as tiebreaker
                ->count();
        }
        
        // Check if this frequency matches any rule
        $matchedRule = $jenis->frequencyRules->first(function ($rule) use ($frequency) {
            return $rule->matchesFrequency($frequency);
        });
        
        if ($matchedRule) {
            return [
                'poin' => $matchedRule->poin,
                'frequency' => $frequency,
                'matched' => true,
            ];
        }
        
        // No match: this violation didn't add poin
        return [
            'poin' => 0,
            'frequency' => $frequency,
            'matched' => false,
        ];
    }
    
    /**
     * Get badge HTML untuk display poin
     * 
     * @param RiwayatPelanggaran $riwayat
     * @return string HTML badge
     */
    public static function getPoinBadge(RiwayatPelanggaran $riwayat): string
    {
        $result = self::getPoinForRiwayat($riwayat);
        
        if ($result['frequency'] === null) {
            // Legacy rules
            if ($result['poin'] > 0) {
                return '<span class="badge badge-danger">+' . $result['poin'] . '</span>';
            }
            return '<span class="badge badge-secondary">0</span>';
        }
        
        // Frequency-based rules
        if ($result['matched']) {
            // This violation triggered a rule
            return '<span class="badge badge-danger" title="Frekuensi ke-' . $result['frequency'] . ' (threshold reached)">+' . $result['poin'] . '</span>';
        } else {
            // This violation didn't trigger yet
            return '<span class="badge badge-secondary" title="Frekuensi ke-' . $result['frequency'] . ' (belum threshold)">+0</span>';
        }
    }
    
    /**
     * Get informative text untuk frequency-based violation
     * 
     * @param RiwayatPelanggaran $riwayat
     * @return string
     */
    public static function getFrequencyText(RiwayatPelanggaran $riwayat): string
    {
        $result = self::getPoinForRiwayat($riwayat);
        
        if ($result['frequency'] === null) {
            return ''; // Not frequency-based
        }
        
        $ordinal = match($result['frequency']) {
            1 => 'Pertama',
            2 => 'Kedua',
            3 => 'Ketiga',
            4 => 'Keempat',
            5 => 'Kelima',
            default => 'Ke-' . $result['frequency'],
        };
        
        if ($result['matched']) {
            return "Pelanggaran {$ordinal} (Threshold: +{$result['poin']} poin)";
        } else {
            return "Pelanggaran {$ordinal} (Belum threshold)";
        }
    }
}
