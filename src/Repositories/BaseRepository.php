<?php

declare(strict_types=1);

namespace MuhammedSalama\Base\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use MuhammedSalama\Base\Interfaces\RepositoryInterface;

abstract class BaseRepository implements RepositoryInterface
{
    protected Model $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * @param  array<int, string>  $columns
     * @param  array<int, string>  $relations
     * @return Collection<int, Model>
     */
    public function all(array $columns = ['*'], array $relations = []): Collection
    {
        return $this->model->with($relations)->get($columns);
    }

    /**
     * @param  array<int, string>  $columns
     * @param  array<int, string>  $relations
     * @return LengthAwarePaginator<int, Model>
     */
    public function paginate(int $perPage = 15, array $columns = ['*'], array $relations = []): LengthAwarePaginator
    {
        return $this->model->with($relations)->paginate($perPage, $columns);
    }

    /**
     * @param  array<int, string>  $columns
     * @param  array<int, string>  $relations
     */
    public function find(int|string $id, array $columns = ['*'], array $relations = []): ?Model
    {
        return $this->model->with($relations)->find($id, $columns);
    }

    /**
     * @param  array<int, string>  $columns
     * @param  array<int, string>  $relations
     */
    public function findOrFail(int|string $id, array $columns = ['*'], array $relations = []): Model
    {
        return $this->model->with($relations)->findOrFail($id, $columns);
    }

    /**
     * @param  array<int, string>  $columns
     */
    public function findBy(string $column, mixed $value, array $columns = ['*']): ?Model
    {
        return $this->model->where($column, $value)->first($columns);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Model
    {
        return $this->model->create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(int|string $id, array $data): Model
    {
        $record = $this->findOrFail($id);
        $record->update($data);

        return $record;
    }

    public function delete(int|string $id): bool
    {
        $record = $this->findOrFail($id);

        return (bool) $record->delete();
    }

    /** @return Builder<Model> */
    public function query(): Builder
    {
        return $this->model->newQuery();
    }

    public function getModel(): Model
    {
        return $this->model;
    }
}
