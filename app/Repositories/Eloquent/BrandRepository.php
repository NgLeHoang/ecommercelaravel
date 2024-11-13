<?php

namespace App\Repositories\Eloquent;

use App\Models\Brand;
use App\Repositories\Contracts\BrandRepositoryInterface;
use App\Traits\ImageUploadTrait;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;

class BrandRepository implements BrandRepositoryInterface
{
    use ImageUploadTrait;
    /**
     * The Brand model instance.
     *
     * @var \App\Models\Brand
     */
    protected $model;

    /**
     * Constructor to initialize the model instance.
     *
     * @param \App\Models\Brand $model
     */
    public function __construct(Brand $model)
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
     * Get all the records from the repository.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function find($id)
    {
        return $this->model->find($id);
    }

    /**
     * Store a new brand in the database.
     *
     * @param array $data
     * @return \App\Models\Brand
     */
    public function storeBrand(array $data)
    {
        return $this->model::create($data);
    }

    /**
     * Update an existing brand.
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updateBrand(int $id, array $data): bool
    {
        $brand = $this->model->find($id);
        return $brand->update($data);
    }

    /**
     * Delete a brand by ID.
     *
     * @param int $id
     * @return bool
     */
    public function deleteBrand(int $id): bool
    {
        $brand = $this->model->find($id);
        return $brand->delete();
    }

    /**
     * Save the brand image to the 'brands' folder using the ImageUploadTrait.
     *
     * @param \Illuminate\Http\UploadedFile $image
     * @return string
     */
    public function saveBrandImage($image): string
    {
        $fileExtension = $image->extension();
        $fileName = Carbon::now()->timestamp . '.' . $fileExtension;

        $this->saveImageToFolder($image, $fileName, 'brands', 124, 124);

        return $fileName;
    }

    /**
     * Delete the image of a brand.
     *
     * @param int $id
     * @return void
     */
    public function deleteBrandImage(int $id): void
    {
        $brand = $this->model->find($id);
        $imagePath = public_path('uploads/brands') . '/' . $brand->image;

        if (File::exists($imagePath)) {
            File::delete($imagePath);
        }
    }
}
