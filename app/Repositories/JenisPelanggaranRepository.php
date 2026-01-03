<?php

namespace App\Repositories;

use App\Models\JenisPelanggaran;
use App\Repositories\Contracts\JenisPelanggaranRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Jenis Pelanggaran Repository Implementation
 * 
 * Handles all data access operations for JenisPelanggaran entity.
 * Implements JenisPelanggaranRepositoryInterface and extends BaseRepository.
 */
class JenisPelanggaranRepository extends BaseRepository implements JenisPelanggaranRepositoryInterface
{
    /**
     * JenisPelanggaranRepository constructor.
     *
     * @param JenisPelanggaran $model
     */
    public function __construct(JenisPelanggaran $model)
    {
        parent::__construct($model);
    }

    /**
     * Find jenis pelanggaran by kategori.
     *
     * @param int $kategoriId
     * @return Collection
     */
    public function findByKategori(int $kategoriId): Collection
    {
        return $this->model
            ->where('kategori_id', $kategoriId)
            ->where('is_active', true)
            ->with('kategoriPelanggaran')
            ->orderBy('poin')
            ->orderBy('nama_pelanggaran')
            ->get();
    }

    /**
     * Get all active jenis pelanggaran.
     * 
     * CACHED dengan rememberForever (master data).
     * Cache akan di-invalidate saat create/update/delete via clearCache().
     *
     * @return Collection
     */
    public function getActive(): Collection
    {
        return Cache::rememberForever('jenis_pelanggaran:active', function () {
            return $this->model
                ->where('is_active', true)
                ->with('kategoriPelanggaran')
                ->orderBy('poin')
                ->get();
        });
    }

    /**
     * Get jenis pelanggaran by filter category.
     *
     * @param string $filterCategory
     * @return Collection
     */
    public function getByFilterCategory(string $filterCategory): Collection
    {
        return $this->model
            ->where('filter_category', $filterCategory)
            ->where('is_active', true)
            ->with('kategoriPelanggaran')
            ->orderBy('poin')
            ->get();
    }

    /**
     * Search jenis pelanggaran by keyword.
     * Uses model scope for fuzzy search.
     *
     * @param string $keyword
     * @return Collection
     */
    public function searchByKeyword(string $keyword): Collection
    {
        return $this->model
            ->searchByKeyword($keyword)
            ->where('is_active', true)
            ->with('kategoriPelanggaran')
            ->orderBy('poin')
            ->get();
    }

    /**
     * Get jenis pelanggaran that have frequency rules.
     *
     * @return Collection
     */
    public function getWithFrequencyRules(): Collection
    {
        return $this->model
            ->where('has_frequency_rules', true)
            ->where('is_active', true)
            ->with(['kategoriPelanggaran', 'frequencyRules'])
            ->orderBy('poin')
            ->get();
    }

    /**
     * Get jenis pelanggaran with poin within range.
     *
     * @param int $minPoin
     * @param int $maxPoin
     * @return Collection
     */
    public function getByPoinRange(int $minPoin, int $maxPoin): Collection
    {
        return $this->model
            ->whereBetween('poin', [$minPoin, $maxPoin])
            ->where('is_active', true)
            ->with('kategoriPelanggaran')
            ->orderBy('poin')
            ->get();
    }

    /**
     * Override create untuk tambahkan cache invalidation.
     *
     * @param array $data
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function create(array $data): \Illuminate\Database\Eloquent\Model
    {
        $result = parent::create($data);
        $this->clearCache();
        return $result;
    }

    /**
     * Override update untuk tambahkan cache invalidation.
     *
     * @param int $id
     * @param array $data
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function update(int $id, array $data): \Illuminate\Database\Eloquent\Model
    {
        $result = parent::update($id, $data);
        $this->clearCache();
        return $result;
    }

    /**
     * Override delete untuk tambahkan cache invalidation.
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $result = parent::delete($id);
        $this->clearCache();
        return $result;
    }

    /**
     * Clear all jenis pelanggaran caches.
     *
     * @return void
     */
    private function clearCache(): void
    {
        Cache::forget('jenis_pelanggaran:active');
        // Tambahkan keys lain jika ada
    }

    /**
     * Find with frequency rules relation
     *
     * @param int $id
     * @return JenisPelanggaran|null
     */
    public function findWithFrequencyRules(int $id): ?JenisPelanggaran
    {
        return $this->model
            ->with(['kategoriPelanggaran', 'frequencyRules'])
            ->find($id);
    }

    /**
     * Update frequency rule status (has_frequency_rules and is_active)
     *
     * @param int $id
     * @param bool $hasRules
     * @param bool $isActive
     * @return bool
     */
    public function updateFrequencyStatus(int $id, bool $hasRules, bool $isActive): bool
    {
        $result = $this->model->where('id', $id)->update([
            'has_frequency_rules' => $hasRules,
            'is_active' => $isActive,
        ]);
        
        $this->clearCache();
        
        return $result;
    }

    /**
     * Activate jenis pelanggaran (has frequency rules now)
     *
     * @param int $id
     * @return bool
     */
    public function activateFrequencyRules(int $id): bool
    {
        return $this->updateFrequencyStatus($id, true, true);
    }

    /**
     * Deactivate jenis pelanggaran (no frequency rules)
     *
     * @param int $id
     * @return bool
     */
    public function deactivateFrequencyRules(int $id): bool
    {
        return $this->updateFrequencyStatus($id, false, false);
    }
    
    /**
     * Get paginated jenis pelanggaran with optional search
     * 
     * EXACT LOGIC from JenisPelanggaranController::index() (lines 25-32)
     * 
     * @param string|null $searchTerm
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getPaginatedWithSearch(?string $searchTerm = null, int $perPage = 10): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $query = $this->model->with('kategoriPelanggaran');
        
        // Search by nama_pelanggaran
        if ($searchTerm) {
            $query->where('nama_pelanggaran', 'like', '%' . $searchTerm . '%');
        }
        
        return $query->orderBy('poin', 'asc')->paginate($perPage);
    }
    
    /**
     * Get all kategori pelanggaran
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllKategori(): \Illuminate\Database\Eloquent\Collection
    {
        return \App\Models\KategoriPelanggaran::all();
    }
    
    /**
     * Check if jenis pelanggaran has riwayat records
     * 
     * EXACT LOGIC from JenisPelanggaranController::destroy() (line 115)
     * FIXED: Only check ACTIVE (non-deleted) riwayat records
     * 
     * @param JenisPelanggaran $jenisPelanggaran
     * @return bool
     */
    public function hasRiwayatRecords(JenisPelanggaran $jenisPelanggaran): bool
    {
        // Only check active records (exclude soft deleted)
        return $jenisPelanggaran->riwayatPelanggaran()
            ->whereNull('riwayat_pelanggaran.deleted_at')
            ->exists();
    }
}
