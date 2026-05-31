<?php

namespace MuhammedSalama\Base\Interfaces;

interface RepositoryInterface
{
    /**
     * Get all records.
     *
     * @param array $columns
     * @param array $relations
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function all(array $columns = ['*'], array $relations = []);

    /**
     * Get paginated records.
     *
     * @param int $perPage
     * @param array $columns
     * @param array $relations
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function paginate(int $perPage = 15, array $columns = ['*'], array $relations = []);

    /**
     * Find a record by its primary key.
     *
     * @param int|string $id
     * @param array $columns
     * @param array $relations
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function find($id, array $columns = ['*'], array $relations = []);

    /**
     * Find a record by its primary key or throw an exception.
     *
     * @param int|string $id
     * @param array $columns
     * @param array $relations
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function findOrFail($id, array $columns = ['*'], array $relations = []);

    /**
     * Find a record by a given column/value.
     *
     * @param string $column
     * @param mixed $value
     * @param array $columns
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function findBy(string $column, $value, array $columns = ['*']);

    /**
     * Create a new record.
     *
     * @param array $data
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function create(array $data);

    /**
     * Update an existing record.
     *
     * @param int|string $id
     * @param array $data
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function update($id, array $data);

    /**
     * Delete a record.
     *
     * @param int|string $id
     * @return bool
     */
    public function delete($id): bool;

    /**
     * Get the query builder for advanced queries.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query();
}
