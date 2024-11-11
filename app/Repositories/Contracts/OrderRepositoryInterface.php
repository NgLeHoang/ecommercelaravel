<?php

namespace App\Repositories\Contracts;

interface OrderRepositoryInterface
{
    public function getAll();
    public function find($id);
    public function findAllByUserId($user_id);
    public function findByUserIdAndOrderId($user_id, $order_id);
    // public function create(array $data);
    // public function update($id, array $data);
    // public function delete($id);
}
