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
     * Get all contacts with pagination.
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getAll()
    {
        return $this->model->orderBy('created_at', 'DESC')->paginate(10);
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

    /**
     * Delete a contact by ID.
     *
     * @param int $id
     * @return bool
     */
    public function deleteContact(int $id): bool
    {
        $contact = $this->model->find($id);
        return $contact->delete();
    }
}