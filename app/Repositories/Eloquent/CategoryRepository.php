<?php

namespace App\Repositories\Eloquent;

use App\Models\Category;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use App\Traits\ImageUploadTrait;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;

class CategoryRepository implements CategoryRepositoryInterface
{
    use ImageUploadTrait;

    /**
     * The Category model instance.
     *
     * @var \App\Models\Category
     */
    protected $model;

    /**
     * Constructor to initialize the model instance.
     *
     * @param \App\Models\Category $model
     */
    public function __construct(Category $model)
    {
        $this->model = $model;
    }

    /**
     * Get all categories with pagination.
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getAll()
    {
        return $this->model->orderBy('id', 'DESC')->paginate(12);
    }

    /**
     * Find a record by its ID.
     *
     * @param int $id The ID of the record to find.
     * @return \App\Models\Category|null
     */
    public function find($id)
    {
        return $this->model->find($id);
    }

    /**
     * Get categories for use in product selection.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getCategoryForProduct()
    {
        return $this->model->select('id', 'name')->orderBy('name')->get();
    }

    /**
     * Get categories for display on homepage.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getCategoryForHomePage()
    {
        return $this->model->orderBy('name')->get();
    }

    /**
     * Store a new category in the database.
     *
     * @param array $data
     * @return \App\Models\category
     */
    public function storeCategory(array $data)
    {
        return $this->model::create($data);
    }

    /**
     * Update an existing category.
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updateCategory(int $id, array $data): bool
    {
        $category = $this->model->find($id);
        return $category->update($data);
    }

    /**
     * Delete a category by ID.
     *
     * @param int $id
     * @return bool
     */
    public function deleteCategory(int $id): bool
    {
        $category = $this->model->find($id);
        return $category->delete();
    }

    /**
     * Save the category image to the 'categories' folder using the ImageUploadTrait.
     *
     * @param \Illuminate\Http\UploadedFile $image
     * @return string
     */
    public function saveCategoryImage($image): string
    {
        $fileExtension = $image->extension();
        $fileName = Carbon::now()->timestamp . '.' . $fileExtension;

        $this->saveImageToFolder($image, $fileName, 'categories', 124, 124);

        return $fileName;
    }

    /**
     * Delete the image of a category.
     *
     * @param int $id
     * @return void
     */
    public function deleteCategoryImage(int $id): void
    {
        $category = $this->model->find($id);
        $imagePath = public_path('uploads/categories') . '/' . $category->image;

        if (File::exists($imagePath)) {
            File::delete($imagePath);
        }
    }
}
