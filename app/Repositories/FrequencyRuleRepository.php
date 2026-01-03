<?php

namespace App\Repositories;

use App\Models\PelanggaranFrequencyRule;
use Illuminate\Database\Eloquent\Collection;

/**
 * Frequency Rule Repository
 * 
 * Responsibility: ALL database operations for Frequency Rules
 * 
 * CLEAN ARCHITECTURE:
 * - Controller calls Service
 * - Service calls Repository
 * - Repository queries database
 */
class FrequencyRuleRepository
{
    /**
     * Get all rules for a specific jenis pelanggaran
     */
    public function findByJenisPelanggaran(int $jenisPelanggaranId, ?string $orderBy = 'display_order'): Collection
    {
        $query = PelanggaranFrequencyRule::where('jenis_pelanggaran_id', $jenisPelanggaranId);
        
        if ($orderBy) {
            $query->orderBy($orderBy);
        }
        
        return $query->get();
    }

    /**
     * Get maximum display_order for a jenis pelanggaran
     */
    public function getMaxDisplayOrder(int $jenisPelanggaranId): ?int
    {
        return PelanggaranFrequencyRule::where('jenis_pelanggaran_id', $jenisPelanggaranId)
            ->max('display_order');
    }

    /**
     * Create new frequency rule
     */
    public function create(array $data): PelanggaranFrequencyRule
    {
        return PelanggaranFrequencyRule::create($data);
    }

    /**
     * Find rule by ID
     */
    public function findOrFail(int $ruleId): PelanggaranFrequencyRule
    {
        return PelanggaranFrequencyRule::findOrFail($ruleId);
    }

    /**
     * Update rule
     */
    public function update(int $ruleId, array $data): bool
    {
        $rule = $this->findOrFail($ruleId);
        return $rule->update($data);
    }

    /**
     * Delete rule
     */
    public function delete(int $ruleId): bool
    {
        $rule = $this->findOrFail($ruleId);
        return $rule->delete();
    }

    /**
     * Count rules for a jenis pelanggaran
     */
    public function countByJenisPelanggaran(int $jenisPelanggaranId): int
    {
        return PelanggaranFrequencyRule::where('jenis_pelanggaran_id', $jenisPelanggaranId)
            ->count();
    }

    /**
     * Check if jenis pelanggaran has any rules
     */
    public function hasRules(int $jenisPelanggaranId): bool
    {
        return $this->countByJenisPelanggaran($jenisPelanggaranId) > 0;
    }

    /**
     * Get all rules paginated for admin view
     */
    public function getAllPaginated(int $jenisPelanggaranId, int $perPage = 15)
    {
        return PelanggaranFrequencyRule::where('jenis_pelanggaran_id', $jenisPelanggaranId)
            ->orderBy('display_order')
            ->paginate($perPage);
    }

    /**
     * Reorder rules after deletion
     * Updates display_order for all rules after the deleted one
     */
    public function reorderAfterDeletion(int $jenisPelanggaranId, int $deletedOrder): void
    {
        PelanggaranFrequencyRule::where('jenis_pelanggaran_id', $jenisPelanggaranId)
            ->where('display_order', '>', $deletedOrder)
            ->decrement('display_order');
    }
}
