<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Base Repository Interface
 * 
 * This interface defines the standard CRUD operations that all repositories must implement.
 * It provides a consistent API for data access across the application.
 * 
 * @template T of Model
 */
interface BaseRepositoryInterface
{
    /**
     * Find a record by its ID.
     *
     * @param int $id
     * @return Model|null
     */
    public function find(int $id): ?Model;

    /**
     * Find a record by its ID or throw an exception if not found.
     *
     * @param int $id
     * @return Model
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findOrFail(int $id): Model;

    /**
     * Find a record by its ID with loaded relations.
     *
     * @param int $id
     * @param array $relations
     * @return Model|null
     */
    public function findWithRelations(int $id, array $relations = []): ?Model;

    /**
     * Get all records.
     *
     * @param array<string> $columns
     * @return Collection
     */
    public function all(array $columns = ['*']): Collection;

    /**
     * Get paginated records.
     *
     * @param int $perPage
     * @param array<string> $columns
     * @return LengthAwarePaginator
     */
    public function paginate(int $perPage = 15, array $columns = ['*']): LengthAwarePaginator;

    /**
     * Create a new record.
     *
     * @param array<string, mixed> $data
     * @return Model
     */
    public function create(array $data): Model;

    /**
     * Update a record by its ID.
     *
     * @param int $id
     * @param array<string, mixed> $data
     * @return Model
     */
    public function update(int $id, array $data): Model;

    /**
     * Delete a record by its ID.
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool;

    /**
     * Count all records.
     *
     * @return int
     */
    public function count(): int;

    /**
     * Check if a record exists by its ID.
     *
     * @param int $id
     * @return bool
     */
    public function exists(int $id): bool;
}
