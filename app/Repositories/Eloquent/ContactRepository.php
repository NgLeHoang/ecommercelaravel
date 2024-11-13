<?php

namespace App\Repositories\Eloquent;

use App\Models\Contact;
use App\Repositories\Contracts\ContactRepositoryInterface;

class ContactRepository implements ContactRepositoryInterface
{
    /**
     * The model instance that this repository will interact with.
     *
     * @var \App\Models\OrderItem
     */
    protected $model;

    /**
     * ContactRepository constructor.
     *
     * @param \App\Models\Contact $model
     */
    public function __construct(Contact $model)
    {
        $this->model = $model;
    }

    /**
     * Create a new contact record.
     *
     * @param array $data
     * @return bool
     */
    public function create(array $data): bool
    {
        $contact = new $this->model;
        $contact->name = $data['name'];
        $contact->email = $data['email'];
        $contact->phone = $data['phone'];
        $contact->comment = $data['comment'];

        return $contact->save();
    }
}