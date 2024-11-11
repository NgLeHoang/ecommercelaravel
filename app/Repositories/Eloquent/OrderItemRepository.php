<?php

namespace App\Repositories\Eloquent;

use App\Models\OrderItem;
use App\Repositories\Contracts\OrderItemRepositoryInterface;

class OrderItemRepository implements OrderItemRepositoryInterface
{
    protected $model;

    public function __construct(OrderItem $model)
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

    public function findAllByOrderId($order_id) 
    {
        return $this->model->where('order_id', $order_id)->orderBy('id')->paginate(10);
    }
}
