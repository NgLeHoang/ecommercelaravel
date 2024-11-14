<?php

namespace App\Repositories\Eloquent;

use App\Models\Slide;
use App\Repositories\Contracts\SlideRepositoryInterface;
use App\Traits\ImageUploadTrait;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;

class SlideRepository implements SlideRepositoryInterface
{
    use ImageUploadTrait;
    /**
     * The Slide model instance.
     *
     * @var \App\Models\Slide
     */
    protected $model;

    /**
     * Constructor to initialize the model instance.
     *
     * @param \App\Models\Slide $model
     */
    public function __construct(Slide $model)
    {
        $this->model = $model;
    }

    /**
     * Get all slides with pagination.
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
     * @param int $id
     * @return \App\Models\Slide|null
     */
    public function find($id)
    {
        return $this->model->find($id);
    }

    /**
     * Retrieve active records for the homepage.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getSlideForHomePage()
    {
        return $this->model->where('status', 1)->get()->take(3);
    }

    /**
     * Store a new slide in the database.
     *
     * @param array $data
     * @return \App\Models\slide
     */
    public function storeSlide(array $data)
    {
        return $this->model::create($data);
    }

    /**
     * Update an existing slide.
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updateSlide(int $id, array $data): bool
    {
        $slide = $this->model->find($id);
        return $slide->update($data);
    }

    /**
     * Delete a slide by ID.
     *
     * @param int $id
     * @return bool
     */
    public function deleteSlide(int $id): bool
    {
        $slide = $this->model->find($id);
        return $slide->delete();
    }

    /**
     * Save the slide image to the 'slides' folder using the ImageUploadTrait.
     *
     * @param \Illuminate\Http\UploadedFile $image
     * @return string
     */
    public function saveSlideImage($image): string
    {
        $fileExtension = $image->extension();
        $fileName = Carbon::now()->timestamp . '.' . $fileExtension;

        $this->saveImageToFolder($image, $fileName, 'slides', 400, 690);

        return $fileName;
    }

    /**
     * Delete the image of a slide.
     *
     * @param int $id
     * @return void
     */
    public function deleteSlideImage(int $id): void
    {
        $slide = $this->model->find($id);
        $imagePath = public_path('uploads/categories') . '/' . $slide->image;

        if (File::exists($imagePath)) {
            File::delete($imagePath);
        }
    }
}
