<?php

namespace App\Repositories\Eloquent;

use App\Models\Brand;
use App\Repositories\Contracts\BrandRepositoryInterface;

class BrandRepository implements BrandRepositoryInterface
{
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
}
