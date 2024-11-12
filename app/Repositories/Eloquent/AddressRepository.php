<?php

namespace App\Repositories\Eloquent;

use App\Models\Address;
use App\Repositories\Contracts\AddressRepositoryInterface;
use Illuminate\Support\Facades\Auth;

class AddressRepository implements AddressRepositoryInterface
{
    /**
     * The Address model instance.
     *
     * @var \App\Models\Address
     */
    protected $model;

    /**
     * Constructor to initialize the model instance.
     *
     * @param \App\Models\Address $model
     */
    public function __construct(Address $model)
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
     * @return \App\Models\Address|null
     */
    public function find($id)
    {
        return $this->model->find($id);
    }

    /**
     * Find an address by the user ID.
     *
     * @param int $user_id The user ID to search the address for.
     * @return \App\Models\Address|null
     */
    public function findByUserId($user_id)
    {
        return $this->model->where('user_id', $user_id)->where('is_default', true)->first();
    }

    /**
     * Store a new address in the database.
     *
     * @param array $data The address data to store.
     * @return bool Returns true if the address is successfully saved, otherwise false.
     */
    public function storeAddress(array $data): bool
    {
        $address = new $this->model;
        $address->name = $data['name'];
        $address->phone = $data['phone'];
        $address->district = $data['district'];
        $address->city = $data['city'];
        $address->address = $data['address'];
        $address->locality = $data['locality'];
        $address->country = 'Vietnam';
        $address->user_id = Auth::id();
        $address->is_default = $data['is_default'];

        return $address->save();
    }

    /**
     * Update an existing address.
     * 
     * @param int $id The ID of the address to update.
     * @param array $data The updated data for the address.
     * @return bool Returns true if the address is successfully updated, otherwise false.
     */
    public function updateAddress($id, array $data): bool
    {
        $address = $this->model->find($id);

        if (!$address) {
            return false;
        }

        $address->name = $data['name'];
        $address->phone = $data['phone'];
        $address->district = $data['district'];
        $address->city = $data['city'];
        $address->address = $data['address'];
        $address->locality = $data['locality'];
        $address->country = 'Vietnam';
        $address->user_id = Auth::id();
        $address->is_default = $data['is_default'];

        return $address->save();
    }
}
