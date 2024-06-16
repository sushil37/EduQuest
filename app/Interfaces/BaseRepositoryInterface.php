<?php


namespace App\Interfaces;

interface BaseRepositoryInterface
{
    public function get_all(array $filters = []);

    public function get_one(int $id);

    public function get_by_field(string $key, string $value);

    public function paginated_list(int $limit, array $filters = []);

    public function create(array $data);

    public function update(array $data, int $id);

    public function delete(int $id);

    public function count();

    public function count_where(string $key, string $value);

}
