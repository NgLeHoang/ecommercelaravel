<?php

namespace App\Repositories\Eloquent;

use App\Models\Order;
use App\Repositories\Contracts\OrderRepositoryInterface;

class OrderRepository implements OrderRepositoryInterface
{
    protected $model;

    public function __construct(Order $model)
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

    public function findAllByUserId($user_id)
    {
        return $this->model->where('user_id', $user_id)->orderBy('created_at', 'DESC')->paginate(10);
    }

    public function findByUserIdAndOrderId($user_id, $order_id)
    {
        return $this->model->where('user_id', $user_id)->where('id', $order_id)->first();
    }
}
