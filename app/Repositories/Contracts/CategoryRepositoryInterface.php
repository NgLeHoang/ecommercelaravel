<?php

namespace App\Repositories\Contracts;

interface CategoryRepositoryInterface
{
    public function getAll();
    public function find($id);
    public function getCategoryForProduct();
    // public function create(array $data);
    // public function update($id, array $data);
    // public function delete($id);
}
