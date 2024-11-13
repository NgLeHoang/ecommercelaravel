<?php

namespace App\Repositories\Eloquent;

use App\Models\Category;
use App\Repositories\Contracts\CategoryRepositoryInterface;

class CategoryRepository implements CategoryRepositoryInterface
{
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
}
