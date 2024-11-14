<?php

namespace App\Repositories\Contracts;

interface SlideRepositoryInterface
{
    /**
     * Get all slides with pagination.
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getAll();

    /**
     * Find a record by its ID.
     *
     * @param int $id
     * @return \App\Models\Slide|null
     */
    public function find($id);

    /**
     * Retrieve active records for the homepage.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getSlideForHomePage();

    /**
     * Store a new slide in the database.
     *
     * @param array $data
     * @return \App\Models\slide
     */
    public function storeSlide(array $data);

    /**
     * Update an existing slide.
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updateSlide(int $id, array $data): bool;

    /**
     * Delete a slide by ID.
     *
     * @param int $id
     * @return bool
     */
    public function deleteSlide(int $id): bool;

    /**
     * Save the slide image to the 'categories' folder using the ImageUploadTrait.
     *
     * @param \Illuminate\Http\UploadedFile $image
     * @return string
     */
    public function saveSlideImage($image): string;

    /**
     * Delete the image of a slide.
     *
     * @param int $id
     * @return void
     */
    public function deleteSlideImage(int $id): void;
}