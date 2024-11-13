<?php

namespace App\Repositories\Contracts;

interface ContactRepositoryInterface
{
    /**
     * Create a new contact record.
     *
     * @param array $data
     * @return bool
     */
    public function create(array $data): bool;
}