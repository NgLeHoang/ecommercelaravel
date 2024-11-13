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

    /**
     * Get categories for display on homepage.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getCategoryForHomePage();

    /**
     * Store a new category in the database.
     *
     * @param array $data
     * @return \App\Models\Category
     */
    public function storeCategory(array $data);

    /**
     * Update an existing category.
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updateCategory(int $id, array $data): bool;

    /**
     * Delete a category by ID.
     *
     * @param int $id
     * @return bool
     */
    public function deleteCategory(int $id): bool;

    /**
     * Save the category image to the 'categories' folder using the ImageUploadTrait.
     *
     * @param \Illuminate\Http\UploadedFile $image
     * @return string
     */
    public function saveCategoryImage($image): string;

    /**
     * Delete the image of a category.
     *
     * @param int $id
     * @return void
     */
    public function deleteCategoryImage(int $id): void;
}
