<?php

namespace App\Repositories\Eloquent;

use App\Models\Slide;
use App\Repositories\Contracts\SlideRepositoryInterface;

class SlideRepository implements SlideRepositoryInterface
{
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
     * Retrieve active records for the homepage.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getSlideForHomePage()
    {
        $this->model->where('status', 1)->get()->take(3);
    }
}
