<?php

declare(strict_types=1);

namespace MuhammedSalama\Base\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use MuhammedSalama\Base\Interfaces\RepositoryInterface;
use MuhammedSalama\Base\Interfaces\ServiceInterface;

abstract class BaseService implements ServiceInterface
{
    protected RepositoryInterface $repository;

    public function __construct(RepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param  array<int, string>  $columns
     * @param  array<int, string>  $relations
     * @return Collection<int, Model>
     */
    public function all(array $columns = ['*'], array $relations = []): Collection
    {
        return $this->repository->all($columns, $relations);
    }

    /**
     * @param  array<int, string>  $columns
     * @param  array<int, string>  $relations
     * @return LengthAwarePaginator<int, Model>
     */
    public function paginate(int $perPage = 15, array $columns = ['*'], array $relations = []): LengthAwarePaginator
    {
        return $this->repository->paginate($perPage, $columns, $relations);
    }

    /**
     * @param  array<int, string>  $columns
     * @param  array<int, string>  $relations
     */
    public function find(int|string $id, array $columns = ['*'], array $relations = []): Model
    {
        return $this->repository->findOrFail($id, $columns, $relations);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function store(array $data): Model
    {
        return $this->repository->create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(int|string $id, array $data): Model
    {
        return $this->repository->update($id, $data);
    }

    public function destroy(int|string $id): bool
    {
        return $this->repository->delete($id);
    }

    public function repository(): RepositoryInterface
    {
        return $this->repository;
    }
}
