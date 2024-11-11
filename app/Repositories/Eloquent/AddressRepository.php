<?php

namespace App\Repositories\Eloquent;

use App\Models\Address;
use App\Repositories\Contracts\AddressRepositoryInterface;
use Illuminate\Support\Facades\Auth;

class AddressRepository implements AddressRepositoryInterface
{
    protected $model;

    public function __construct(Address $model)
    {
        $this->model = $model;
    }

    public function getAll()
    {
        return $this->model->orderBy('id', 'DESC')->paginate(12);
    }

    public function find($id)
    {
        return $this->model->find($id);
    }

    public function findByUserId($user_id)
    {
        return $this->model->where('user_id', $user_id)->where('is_default', true)->first();
    }

    public function storeAddress(array $data) : bool
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
