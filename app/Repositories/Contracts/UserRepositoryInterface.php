<?php

namespace App\Repositories\Contracts;

use App\Models\User;

interface UserRepositoryInterface
{
    /**
     * Get all user items, paginated.
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getAll();

    /**
     * Find a record item by its ID.
     *
     * @param int $id
     * @return \App\Models\Order|null
     */
    public function find($id);

    /**
     * Get the currently authenticated user.
     *
     * @return \App\Models\User|null
     */
    public function getAuthenticatedUser(): ?User;

    /**
     * Update the account details of a user.
     *
     * @param int $user_id 
     * @param array $data 
     * @return bool 
     */
    public function updateAccountDetails(int $user_id, array $data): bool;
}
