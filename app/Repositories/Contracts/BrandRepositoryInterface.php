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

    /**
     * Store a new brand in the database.
     *
     * @param array $data
     * @return \App\Models\Brand
     */
    public function storeBrand(array $data);

    /**
     * Update an existing brand.
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updateBrand(int $id, array $data): bool;

    /**
     * Delete a brand by ID.
     *
     * @param int $id
     * @return bool
     */
    public function deleteBrand(int $id): bool;

    /**
     * Save the brand image to the 'brands' folder using the ImageUploadTrait.
     *
     * @param \Illuminate\Http\UploadedFile $image
     * @return string
     */
    public function saveBrandImage($image): string;

    /**
     * Delete the image of a brand.
     *
     * @param int $id
     * @return void
     */
    public function deleteBrandImage(int $id): void;
}
