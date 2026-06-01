<?php

namespace MuhammedSalama\Base\Interfaces;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface RepositoryInterface
{
    /**
     * @param array<int, string> $columns
     * @param array<int, string> $relations
     * @return Collection<int, Model>
     */
    public function all(array $columns = ['*'], array $relations = []): Collection;

    /**
     * @param array<int, string> $columns
     * @param array<int, string> $relations
     */
    public function paginate(int $perPage = 15, array $columns = ['*'], array $relations = []): LengthAwarePaginator;

    /**
     * Find by primary key (returns null when absent).
     *
     * @param array<int, string> $columns
     * @param array<int, string> $relations
     */
    public function find(int|string $id, array $columns = ['*'], array $relations = []): ?Model;

    /**
     * Find by primary key or throw ModelNotFoundException.
     *
     * @param array<int, string> $columns
     * @param array<int, string> $relations
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findOrFail(int|string $id, array $columns = ['*'], array $relations = []): Model;

    /**
     * Find the first record matching the given column/value pair.
     *
     * @param array<int, string> $columns
     */
    public function findBy(string $column, mixed $value, array $columns = ['*']): ?Model;

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): Model;

    /**
     * @param array<string, mixed> $data
     */
    public function update(int|string $id, array $data): Model;

    public function delete(int|string $id): bool;

    public function query(): Builder;
}
