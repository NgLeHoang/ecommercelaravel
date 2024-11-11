<?php

namespace App\Repositories\Contracts;

interface OrderItemRepositoryInterface
{
    public function getAll();
    public function find($id);
    public function findAllByOrderId($order_id);
    // public function create(array $data);
    // public function update($id, array $data);
    // public function delete($id);
}
