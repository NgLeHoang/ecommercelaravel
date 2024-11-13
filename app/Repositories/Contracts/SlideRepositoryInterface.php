<?php

namespace App\Repositories\Contracts;

interface SlideRepositoryInterface
{
    /**
     * Retrieve active records for the homepage.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getSlideForHomePage();
}