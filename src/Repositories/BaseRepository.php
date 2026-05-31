<?php

namespace MuhammedSalama\Base\Repositories;

use MuhammedSalama\Base\Interfaces\RepositoryInterface;
use Illuminate\Database\Eloquent\Model;

abstract class BaseRepository implements RepositoryInterface
{
    /**
     * @var Model
     */
    protected Model $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * Each child repository must define which model it works with.
     *
     * Example:
     *   protected function model(): string
     *   {
     *       return \App\Models\Product::class;
     *   }
     *
     * (Optional helper if you prefer not to inject the model manually.)
     */
    public function all(array $columns = ['*'], array $relations = [])
    {
        return $this->model->with($relations)->get($columns);
    }

    public function paginate(int $perPage = 15, array $columns = ['*'], array $relations = [])
    {
        return $this->model->with($relations)->paginate($perPage, $columns);
    }

    public function find($id, array $columns = ['*'], array $relations = [])
    {
        return $this->model->with($relations)->find($id, $columns);
    }

    public function findOrFail($id, array $columns = ['*'], array $relations = [])
    {
        return $this->model->with($relations)->findOrFail($id, $columns);
    }

    public function findBy(string $column, $value, array $columns = ['*'])
    {
        return $this->model->where($column, $value)->first($columns);
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update($id, array $data)
    {
        $record = $this->findOrFail($id);
        $record->update($data);

        return $record->fresh();
    }

    public function delete($id): bool
    {
        $record = $this->findOrFail($id);

        return (bool) $record->delete();
    }

    public function query()
    {
        return $this->model->newQuery();
    }

    /**
     * Get the underlying model instance.
     *
     * @return Model
     */
    public function getModel(): Model
    {
        return $this->model;
    }
}
