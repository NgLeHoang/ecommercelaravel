<?php

namespace App\Repositories\Contracts;

interface BrandRepositoryInterface
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
     * @return \App\Models\Brand|null
     */
    public function find($id);
}
