<?php

namespace App\Repositories\Contracts;

interface AddressRepositoryInterface
{
    /**
     * Get all categories with pagination.
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getAll();

    /**
     * Find a record by its ID.
     *
     * @param int $id The ID of the record to find.
     * @return \App\Models\Address|null
     */
    public function find($id);

    /**
     * Find an address by the user ID.
     *
     * @param int $user_id The user ID to search the address for.
     * @return \App\Models\Address|null
     */
    public function findByUserId($user_id);

    /**
     * Store a new address in the database.
     *
     * @param array $data The address data to store.
     * @return bool Returns true if the address is successfully saved, otherwise false.
     */
    public function storeAddress(array $data): bool;


    /**
     * Update an existing address.
     * 
     * @param int $id The ID of the address to update.
     * @param array $data The updated data for the address.
     * @return bool Returns true if the address is successfully updated, otherwise false.
     */
    public function updateAddress($id, array $data): bool;
}
