<?php

namespace MuhammedSalama\Base\Services;

use MuhammedSalama\Base\Interfaces\RepositoryInterface;
use MuhammedSalama\Base\Interfaces\ServiceInterface;

abstract class BaseService implements ServiceInterface
{
    /**
     * @var RepositoryInterface
     */
    protected RepositoryInterface $repository;

    public function __construct(RepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function all(array $columns = ['*'], array $relations = [])
    {
        return $this->repository->all($columns, $relations);
    }

    public function paginate(int $perPage = 15, array $columns = ['*'], array $relations = [])
    {
        return $this->repository->paginate($perPage, $columns, $relations);
    }

    public function find($id, array $columns = ['*'], array $relations = [])
    {
        return $this->repository->findOrFail($id, $columns, $relations);
    }

    public function store(array $data)
    {
        return $this->repository->create($data);
    }

    public function update($id, array $data)
    {
        return $this->repository->update($id, $data);
    }

    public function destroy($id): bool
    {
        return $this->repository->delete($id);
    }

    /**
     * Access the underlying repository if you need custom queries.
     *
     * @return RepositoryInterface
     */
    public function repository(): RepositoryInterface
    {
        return $this->repository;
    }
}
