<?php

namespace App\Repositories\Contracts;

use App\Models\User;

interface UserRepositoryInterface
{
    public function getAll();
    public function find($id);
    public function getAuthenticatedUser(): ?User;
    public function updateAccountDetails(int $user_id, array $data): bool;
    // public function create(array $data);
    // public function update($id, array $data);
    // public function delete($id);
}
