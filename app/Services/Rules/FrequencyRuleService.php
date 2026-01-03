<?php

namespace App\Services\Rules;

use App\Models\PelanggaranFrequencyRule;
use App\Repositories\FrequencyRuleRepository;
use App\Repositories\JenisPelanggaranRepository;
use Illuminate\Database\Eloquent\Collection;

/**
 * Frequency Rule Service
 * 
 * Business logic for managing Frequency Rules
 * 
 * CLEAN ARCHITECTURE:
 * - Controller calls this Service
 * - Service contains business logic
 * - Service calls Repository for data
 * 
 * SINGLE RESPONSIBILITY:
 * - Manage frequency rules lifecycle
 * - Handle jenis pelanggaran activation
 * - Coordinate between repositories
 */
class FrequencyRuleService
{
    public function __construct(
        private FrequencyRuleRepository $ruleRepo,
        private JenisPelanggaranRepository $jenisRepo
    ) {}

    /**
     * Get all rules for a jenis pelanggaran
     */
    public function getRulesForJenisPelanggaran(int $jenisPelanggaranId): Collection
    {
        return $this->ruleRepo->findByJenisPelanggaran($jenisPelanggaranId);
    }

    /**
     * Create new frequency rule
     * 
     * Business Logic:
     * - Calculate next display_order
     * - Create rule
     * - Activate jenis pelanggaran if first rule
     */
    public function createRule(int $jenisPelanggaranId, array $data): PelanggaranFrequencyRule
    {
        // Calculate display_order if not provided
        if (!isset($data['display_order'])) {
            $maxOrder = $this->ruleRepo->getMaxDisplayOrder($jenisPelanggaranId);
            $data['display_order'] = ($maxOrder ?? 0) + 1;
        }

        // Add jenis_pelanggaran_id
        $data['jenis_pelanggaran_id'] = $jenisPelanggaranId;

        // Create rule
        $rule = $this->ruleRepo->create($data);

        // Activate jenis pelanggaran (has rules now)
        $this->jenisRepo->activateFrequencyRules($jenisPelanggaranId);

        return $rule;
    }

    /**
     * Update existing rule
     */
    public function updateRule(int $ruleId, array $data): bool
    {
        return $this->ruleRepo->update($ruleId, $data);
    }

    /**
     * Delete rule
     * 
     * Business Logic:
     * - Delete rule
     * - If no more rules, deactivate jenis pelanggaran
     */
    public function deleteRule(int $ruleId): bool
    {
        $rule = $this->ruleRepo->findOrFail($ruleId);
        $jenisPelanggaranId = $rule->jenis_pelanggaran_id;

        // Delete rule
        $deleted = $this->ruleRepo->delete($ruleId);

        if ($deleted) {
            // Check if any rules remaining
            $remainingRules = $this->ruleRepo->countByJenisPelanggaran($jenisPelanggaranId);

            // If no more rules, deactivate jenis pelanggaran
            if ($remainingRules == 0) {
                $this->jenisRepo->deactivateFrequencyRules($jenisPelanggaranId);
            }
        }

        return $deleted;
    }

    /**
     * Get jenis pelanggaran with frequency rules
     */
    public function getJenisPelanggaranWithRules(int $jenisPelanggaranId)
    {
        return $this->jenisRepo->findWithFrequencyRules($jenisPelanggaranId);
    }

    /**
     * Check if jenis pelanggaran has rules
     */
    public function hasRules(int $jenisPelanggaranId): bool
    {
        return $this->ruleRepo->hasRules($jenisPelanggaranId);
    }

    /**
     * Count rules for jenis pelanggaran
     */
    public function countRules(int $jenisPelanggaranId): int
    {
        return $this->ruleRepo->countByJenisPelanggaran($jenisPelanggaranId);
    }
}
