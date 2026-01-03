<?php

namespace App\Repositories;

use App\Models\Siswa;
use App\Repositories\Contracts\SiswaRepositoryInterface;
use App\Data\Siswa\SiswaData;
use App\Data\Siswa\SiswaFilterData;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

/**
 * Siswa Repository Implementation
 * 
 * Handles all data access operations for Siswa entity.
 * Implements SiswaRepositoryInterface and extends BaseRepository.
 */
class SiswaRepository extends BaseRepository implements SiswaRepositoryInterface
{
    /**
     * SiswaRepository constructor.
     *
     * @param Siswa $model
     */
    public function __construct(Siswa $model)
    {
        parent::__construct($model);
    }

    /**
     * Find a siswa by NISN.
     *
     * @param string $nisn
     * @return SiswaData|null
     */
    public function findByNisn(string $nisn): ?SiswaData
    {
        $siswa = $this->model
            ->where('nisn', $nisn)
            ->with(['kelas.jurusan', 'waliMurid'])
            ->first();

        return $siswa ? SiswaData::from($siswa) : null;
    }

    /**
     * Find all siswa in a specific kelas.
     *
     * @param int $kelasId
     * @return Collection
     */
    public function findByKelas(int $kelasId): Collection
    {
        return $this->model
            ->where('kelas_id', $kelasId)
            ->with(['kelas.jurusan', 'waliMurid'])
            ->orderBy('nama_siswa')
            ->get();
    }

    /**
     * Find all siswa in a specific jurusan.
     *
     * @param int $jurusanId
     * @return Collection
     */
    public function findByJurusan(int $jurusanId): Collection
    {
        return $this->model
            ->whereHas('kelas', function ($query) use ($jurusanId) {
                $query->where('jurusan_id', $jurusanId);
            })
            ->with(['kelas.jurusan', 'waliMurid'])
            ->orderBy('nama_siswa')
            ->get();
    }

    /**
     * Find all siswa by wali murid.
     *
     * @param int $waliMuridId
     * @return Collection
     */
    public function findByWaliMurid(int $waliMuridId): Collection
    {
        return $this->model
            ->where('wali_murid_user_id', $waliMuridId)
            ->with(['kelas.jurusan', 'waliMurid'])
            ->orderBy('nama_siswa')
            ->get();
    }

    /**
     * Search siswa by name or NISN.
     *
     * @param string $keyword
     * @return Collection
     */
    public function searchByName(string $keyword): Collection
    {
        return $this->model
            ->where(function ($query) use ($keyword) {
                $query->where('nama_siswa', 'like', "%{$keyword}%")
                    ->orWhere('nisn', 'like', "%{$keyword}%");
            })
            ->with(['kelas.jurusan', 'waliMurid'])
            ->orderBy('nama_siswa')
            ->get();
    }

    /**
     * Get siswa with violation history.
     *
     * @return Collection
     */
    public function withViolations(): Collection
    {
        return $this->model
            ->has('riwayatPelanggaran')
            ->with(['kelas.jurusan', 'waliMurid'])
            ->orderBy('nama_siswa')
            ->get();
    }

    /**
     * Get siswa with active tindak lanjut cases.
     *
     * @return Collection
     */
    public function withActiveCases(): Collection
    {
        return $this->model
            ->whereHas('tindakLanjut', function ($query) {
                $query->whereIn('status', ['Baru', 'Menunggu Persetujuan', 'Disetujui', 'Ditangani']);
            })
            ->with(['kelas.jurusan', 'waliMurid'])
            ->orderBy('nama_siswa')
            ->get();
    }

    /**
     * Filter and paginate siswa based on filter criteria.
     * 
     * CRITICAL: This method accepts SiswaFilterData (DTO), NOT Illuminate\Http\Request.
     * This ensures clean separation between HTTP layer and repository layer.
     *
     * @param SiswaFilterData $filters
     * @return LengthAwarePaginator
     */
    public function filterAndPaginate(SiswaFilterData $filters): LengthAwarePaginator
    {
        // Start building query with eager loading to prevent N+1 queries
        $query = $this->model
            ->newQuery()
            ->with(['kelas.jurusan', 'waliMurid']);

        // Apply search filter (nama_siswa or nisn)
        if ($filters->search) {
            $query->where(function ($q) use ($filters) {
                $q->where('nama_siswa', 'like', "%{$filters->search}%")
                    ->orWhere('nisn', 'like', "%{$filters->search}%");
            });
        }

        // Apply kelas filter
        if ($filters->kelas_id) {
            $query->where('kelas_id', $filters->kelas_id);
        }

        // Apply jurusan filter (via kelas relationship)
        if ($filters->jurusan_id) {
            $query->whereHas('kelas', function ($q) use ($filters) {
                $q->where('jurusan_id', $filters->jurusan_id);
            });
        }

        // Apply wali murid filter
        if ($filters->wali_murid_user_id) {
            $query->where('wali_murid_user_id', $filters->wali_murid_user_id);
        }

        // Apply violations filter
        if ($filters->with_violations) {
            $query->has('riwayatPelanggaran');
        }

        // Apply active cases filter
        if ($filters->with_active_cases) {
            $query->whereHas('tindakLanjut', function ($q) {
                $q->whereIn('status', ['Baru', 'Menunggu Persetujuan', 'Disetujui', 'Ditangani']);
            });
        }

        // Apply sorting
        $sortBy = $filters->sortBy ?: 'nama_siswa';
        $sortDirection = $filters->getSortDirection();
        $query->orderBy($sortBy, $sortDirection);

        // Return paginated results
        return $query->paginate($filters->perPage);
    }

    /**
     * Check if NISN already exists.
     *
     * @param string $nisn
     * @param int|null $excludeSiswaId
     * @return bool
     */
    public function nisnExists(string $nisn, ?int $excludeSiswaId = null): bool
    {
        $query = $this->model->where('nisn', $nisn);

        if ($excludeSiswaId) {
            $query->where('id', '!=', $excludeSiswaId);
        }

        return $query->exists();
    }

    /**
     * Get total count of siswa by kelas.
     *
     * @param int $kelasId
     * @return int
     */
    public function countByKelas(int $kelasId): int
    {
        return $this->model->where('kelas_id', $kelasId)->count();
    }

    /**
     * Get total count of siswa by jurusan.
     *
     * @param int $jurusanId
     * @return int
     */
    public function countByJurusan(int $jurusanId): int
    {
        return $this->model
            ->whereHas('kelas', function ($query) use ($jurusanId) {
                $query->where('jurusan_id', $jurusanId);
            })
            ->count();
    }

    /**
     * Override find untuk tambahkan caching (TTL 10 menit).
     *
     * @param int $id
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function find(int $id): ?\Illuminate\Database\Eloquent\Model
    {
        $cacheKey = "siswa:find:{$id}";
        
        return Cache::remember($cacheKey, 600, function () use ($id) {
            return $this->model
                ->with(['kelas.jurusan', 'waliMurid'])
                ->find($id);
        });
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
        // Invalidate by kelas cache jika ada
        if (isset($data['kelas_id'])) {
            Cache::forget("siswa:by_kelas:{$data['kelas_id']}");
        }
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
        Cache::forget("siswa:find:{$id}");
        // Invalidate by kelas cache jika ada
        if (isset($data['kelas_id'])) {
            Cache::forget("siswa:by_kelas:{$data['kelas_id']}");
        }
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
        $siswa = $this->model->find($id);
        $result = parent::delete($id);
        Cache::forget("siswa:find:{$id}");
        if ($siswa && $siswa->kelas_id) {
            Cache::forget("siswa:by_kelas:{$siswa->kelas_id}");
        }
        return $result;
    }
    
    /**
     * PERFORMANCE OPTIMIZATION: Get lightweight siswa data for dropdown lists.
     * 
     * WHY THIS EXISTS:
     * - Loading 248 Siswa Models = ~5MB memory + relationship overhead
     * - This method = ~200KB memory (25x lighter!)
     * 
     * RETURNS: Collection of stdClass objects (NOT Eloquent Models)
     * - Each object has: id, nama_siswa, nisn, kelas_id, nama_kelas, jurusan_id, tingkat
     * 
     * USE CASE: Frontend filtering dropdowns that need ALL students but only basic info
     * 
     * @param int|null $kelasId Filter by kelas
     * @param int|null $jurusanId Filter by jurusan  
     * @return \Illuminate\Support\Collection<stdClass>
     */
    public function getForDropdown(?int $kelasId = null, ?int $jurusanId = null): \Illuminate\Support\Collection
    {
        $query = \Illuminate\Support\Facades\DB::table('siswa')
            ->leftJoin('kelas', 'siswa.kelas_id', '=', 'kelas.id')
            ->leftJoin('jurusan', 'kelas.jurusan_id', '=', 'jurusan.id')
            ->select([
                'siswa.id',
                'siswa.nama_siswa',
                'siswa.nisn',
                'siswa.kelas_id',
                'kelas.nama_kelas',
                'kelas.tingkat',
                'jurusan.id as jurusan_id',
                'jurusan.nama_jurusan'
            ])
            ->whereNull('siswa.deleted_at')
            ->orderBy('siswa.nama_siswa', 'asc');
        
        // Apply filters
        if ($kelasId) {
            $query->where('siswa.kelas_id', $kelasId);
        }
        
        if ($jurusanId) {
            $query->where('kelas.jurusan_id', $jurusanId);
        }
        
        return $query->get();
    }
}
