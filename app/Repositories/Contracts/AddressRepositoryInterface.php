<?php

namespace App\Repositories\Contracts;

interface AddressRepositoryInterface
{
    public function getAll();
    public function find($id);
    public function findByUserId($user_id);
    public function storeAddress(array $data) : bool;
    public function updateAddress($id, array $data) : bool;
    // public function delete($id);
}
