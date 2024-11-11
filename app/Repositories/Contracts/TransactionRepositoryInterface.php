<?php

namespace App\Repositories\Contracts;

interface TransactionRepositoryInterface
{
    public function getAll();
    public function find($id);
    public function findByOrderId($order_id);
    // public function create(array $data);
    // public function update($id, array $data);
    // public function delete($id);
}
