<?php

namespace App\Repositories\Contracts;

interface ContactRepositoryInterface
{
    /**
     * Get all contacts with pagination.
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getAll();

    /**
     * Create a new contact record.
     *
     * @param array $data
     * @return bool
     */
    public function create(array $data): bool;

    /**
     * Delete a contact by ID.
     *
     * @param int $id
     * @return bool
     */
    public function deleteContact(int $id): bool;
}