<?php

namespace MuhammedSalama\Base\Interfaces;

interface ServiceInterface
{
    public function all(array $columns = ['*'], array $relations = []);

    public function paginate(int $perPage = 15, array $columns = ['*'], array $relations = []);

    public function find($id, array $columns = ['*'], array $relations = []);

    public function store(array $data);

    public function update($id, array $data);

    public function destroy($id): bool;
}
