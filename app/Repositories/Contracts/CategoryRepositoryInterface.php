<?php

namespace App\Repositories\Contracts;

interface CategoryRepositoryInterface
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
     * @return \App\Models\Category|null
     */
    public function find($id);

    /**
     * Get categories for use in product selection.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getCategoryForProduct();
}
