<?php

namespace App\Services\Rules;

use App\Models\RulesEngineSetting;
use App\Models\RulesEngineSettingHistory;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Service untuk mengelola Rules Engine Settings
 * 
 * Tanggung jawab:
 * - Caching settings untuk performa optimal
 * - Validasi input sebelum update
 * - Tracking history perubahan
 * - Provide fallback values jika setting tidak ada
 */
class RulesEngineSettingsService
{
    /**
     * Cache key prefix
     */
    const CACHE_PREFIX = 'rules_engine_settings';
    const CACHE_TTL = 3600; // 1 hour

    /**
     * Get all settings grouped by category
     */
    public function getAllGrouped(): array
    {
        return Cache::remember(
            self::CACHE_PREFIX . '_all_grouped',
            self::CACHE_TTL,
            function () {
                return RulesEngineSetting::ordered()
                    ->get()
                    ->groupBy('category')
                    ->toArray();
            }
        );
    }

    /**
     * Get all settings as flat array
     */
    public function getAll(): array
    {
        return Cache::remember(
            self::CACHE_PREFIX . '_all',
            self::CACHE_TTL,
            function () {
                return RulesEngineSetting::ordered()->get()->toArray();
            }
        );
    }

    /**
     * Get setting value by key (with caching)
     */
    public function get(string $key, $default = null)
    {
        $cacheKey = self::CACHE_PREFIX . "_{$key}";
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($key, $default) {
            $setting = RulesEngineSetting::where('key', $key)->first();
            return $setting ? $setting->value : $default;
        });
    }

    /**
     * Get setting as integer
     */
    public function getInt(string $key, int $default = 0): int
    {
        $value = $this->get($key, $default);
        return (int) $value;
    }

    /**
     * Get setting as float
     */
    public function getFloat(string $key, float $default = 0.0): float
    {
        $value = $this->get($key, $default);
        return (float) $value;
    }

    /**
     * Get setting as boolean
     */
    public function getBool(string $key, bool $default = false): bool
    {
        $value = $this->get($key, $default);
        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Update single setting with validation
     * 
     * @throws ValidationException
     */
    public function update(string $key, $value, ?int $userId = null): bool
    {
        $setting = RulesEngineSetting::where('key', $key)->first();
        
        if (!$setting) {
            throw new \Exception("Setting with key '{$key}' not found");
        }

        // Validate value
        $this->validateValue($setting, $value);

        $oldValue = $setting->value;
        $setting->value = $value;
        $setting->save();

        // Create history record
        RulesEngineSettingHistory::create([
            'setting_id' => $setting->id,
            'old_value' => $oldValue,
            'new_value' => $value,
            'changed_by' => $userId,
        ]);

        // Clear cache
        $this->clearCache($key);

        return true;
    }

    /**
     * Bulk update settings with validation
     * 
     * @param array $data ['key' => 'value', ...]
     * @throws ValidationException
     */
    public function bulkUpdate(array $data, ?int $userId = null): array
    {
        $results = [];
        $errors = [];

        foreach ($data as $key => $value) {
            try {
                $this->update($key, $value, $userId);
                $results[$key] = true;
            } catch (\Exception $e) {
                $errors[$key] = $e->getMessage();
                $results[$key] = false;
            }
        }

        if (!empty($errors)) {
            throw ValidationException::withMessages($errors);
        }

        return $results;
    }

    /**
     * Validate setting value based on validation rules
     * 
     * @throws ValidationException
     */
    private function validateValue(RulesEngineSetting $setting, $value): void
    {
        if (!$setting->validation_rules) {
            return;
        }

        $validator = Validator::make(
            ['value' => $value],
            ['value' => $setting->validation_rules]
        );

        if ($validator->fails()) {
            throw ValidationException::withMessages([
                $setting->key => $validator->errors()->first('value')
            ]);
        }
    }

    /**
     * Reset setting to default value (from seeder)
     */
    public function reset(string $key, ?int $userId = null): bool
    {
        // Default values dari seeder
        $defaults = [
            'surat_2_min_poin' => 100,
            'surat_2_max_poin' => 500,
            'surat_3_min_poin' => 501,
            'akumulasi_sedang_min' => 55,
            'akumulasi_sedang_max' => 300,
            'akumulasi_kritis' => 301,
            'frekuensi_atribut' => 10,
            'frekuensi_alfa' => 4,
        ];

        if (!isset($defaults[$key])) {
            throw new \Exception("No default value for key '{$key}'");
        }

        return $this->update($key, $defaults[$key], $userId);
    }

    /**
     * Reset all settings to default
     */
    public function resetAll(?int $userId = null): bool
    {
        $defaults = [
            'surat_2_min_poin' => 100,
            'surat_2_max_poin' => 500,
            'surat_3_min_poin' => 501,
            'akumulasi_sedang_min' => 55,
            'akumulasi_sedang_max' => 300,
            'akumulasi_kritis' => 301,
            'frekuensi_atribut' => 10,
            'frekuensi_alfa' => 4,
        ];

        foreach ($defaults as $key => $value) {
            $this->update($key, $value, $userId);
        }

        return true;
    }

    /**
     * Get setting history
     */
    public function getHistory(string $key, int $limit = 10): array
    {
        $setting = RulesEngineSetting::where('key', $key)->first();
        
        if (!$setting) {
            return [];
        }

        return RulesEngineSettingHistory::with('user:id,username')
            ->where('setting_id', $setting->id)
            ->latest()
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * Clear cache for specific key or all
     */
    public function clearCache(?string $key = null): void
    {
        if ($key) {
            Cache::forget(self::CACHE_PREFIX . "_{$key}");
        }
        
        // Always clear grouped and all cache
        Cache::forget(self::CACHE_PREFIX . '_all_grouped');
        Cache::forget(self::CACHE_PREFIX . '_all');
    }

    /**
     * Validate consistency across related settings
     * Returns array of validation errors or empty array if valid
     */
    public function validateConsistency(array $data): array
    {
        $errors = [];

        // Rule 1: surat_2_min_poin < surat_2_max_poin < surat_3_min_poin
        $s2Min = isset($data['surat_2_min_poin']) ? (int)$data['surat_2_min_poin'] : $this->getInt('surat_2_min_poin');
        $s2Max = isset($data['surat_2_max_poin']) ? (int)$data['surat_2_max_poin'] : $this->getInt('surat_2_max_poin');
        $s3Min = isset($data['surat_3_min_poin']) ? (int)$data['surat_3_min_poin'] : $this->getInt('surat_3_min_poin');

        if ($s2Min >= $s2Max) {
            $errors['surat_2_min_poin'] = 'Poin minimum Surat 2 harus lebih kecil dari maksimum';
        }

        if ($s2Max >= $s3Min) {
            $errors['surat_2_max_poin'] = 'Poin maksimum Surat 2 harus lebih kecil dari minimum Surat 3';
        }

        // Rule 2: akumulasi_sedang_min < akumulasi_sedang_max < akumulasi_kritis
        $akumMin = isset($data['akumulasi_sedang_min']) ? (int)$data['akumulasi_sedang_min'] : $this->getInt('akumulasi_sedang_min');
        $akumMax = isset($data['akumulasi_sedang_max']) ? (int)$data['akumulasi_sedang_max'] : $this->getInt('akumulasi_sedang_max');
        $akumKritis = isset($data['akumulasi_kritis']) ? (int)$data['akumulasi_kritis'] : $this->getInt('akumulasi_kritis');

        if ($akumMin >= $akumMax) {
            $errors['akumulasi_sedang_min'] = 'Akumulasi minimum harus lebih kecil dari maksimum';
        }

        if ($akumMax >= $akumKritis) {
            $errors['akumulasi_sedang_max'] = 'Akumulasi maksimum harus lebih kecil dari kritis';
        }

        return $errors;
    }
}

