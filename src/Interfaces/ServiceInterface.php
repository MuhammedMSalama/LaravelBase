<?php

namespace MuhammedSalama\Base\Interfaces;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface ServiceInterface
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
     * Find by primary key or throw ModelNotFoundException.
     *
     * @param array<int, string> $columns
     * @param array<int, string> $relations
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function find(int|string $id, array $columns = ['*'], array $relations = []): Model;

    /**
     * @param array<string, mixed> $data
     */
    public function store(array $data): Model;

    /**
     * @param array<string, mixed> $data
     */
    public function update(int|string $id, array $data): Model;

    public function destroy(int|string $id): bool;
}
