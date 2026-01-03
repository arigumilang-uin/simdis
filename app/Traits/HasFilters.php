<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

/**
 * Trait HasFilters
 * 
 * Provides common filtering functionality for controllers
 * Reduces code duplication across multiple controllers
 */
trait HasFilters
{
    /**
     * Apply filters to a query builder
     * 
     * @param Builder $query
     * @param array $filters
     * @return Builder
     */
    protected function applyFilters(Builder $query, array $filters): Builder
    {
        foreach ($filters as $key => $value) {
            if ($value !== null && $value !== '') {
                $this->applyFilter($query, $key, $value);
            }
        }
        
        return $query;
    }

    /**
     * Apply a single filter to the query
     * 
     * @param Builder $query
     * @param string $key
     * @param mixed $value
     * @return void
     */
    protected function applyFilter(Builder $query, string $key, $value): void
    {
        // Date range filters
        if ($key === 'tanggal_dari') {
            $query->whereDate('tanggal_kejadian', '>=', $value);
            return;
        }
        
        if ($key === 'tanggal_sampai') {
            $query->whereDate('tanggal_kejadian', '<=', $value);
            return;
        }
        
        // Search filters (partial match)
        if ($key === 'search' || $key === 'nama') {
            $query->where(function($q) use ($value) {
                $q->where('nama', 'like', "%{$value}%")
                  ->orWhere('nis', 'like', "%{$value}%");
            });
            return;
        }
        
        // Exact match filters
        if ($key === 'role_id' || $key === 'kelas_id' || $key === 'jurusan_id') {
            $query->where($key, $value);
            return;
        }
        
        // Status filters
        if ($key === 'status' || $key === 'is_active') {
            $query->where($key, $value);
            return;
        }
        
        // Default: exact match
        $query->where($key, $value);
    }

    /**
     * Get filter values from request
     * 
     * @param array $allowedFilters
     * @return array
     */
    protected function getFilters(array $allowedFilters = []): array
    {
        $filters = request()->only($allowedFilters);
        
        // Remove empty values
        return array_filter($filters, function($value) {
            return $value !== null && $value !== '';
        });
    }

    /**
     * Apply search filter (for simple search across multiple columns)
     * 
     * @param Builder $query
     * @param string $searchTerm
     * @param array $columns
     * @return Builder
     */
    protected function applySearch(Builder $query, string $searchTerm, array $columns): Builder
    {
        if (empty($searchTerm)) {
            return $query;
        }

        $query->where(function($q) use ($searchTerm, $columns) {
            foreach ($columns as $column) {
                $q->orWhere($column, 'like', "%{$searchTerm}%");
            }
        });

        return $query;
    }
}
