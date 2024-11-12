<?php

namespace App\Repositories\Eloquent;

use App\Models\Order;
use App\Repositories\Contracts\OrderRepositoryInterface;

class OrderRepository implements OrderRepositoryInterface
{
    /**
     * The model instance that this repository will interact with.
     *
     * @var \App\Models\Order
     */
    protected $model;

    /**
     * OrderRepository constructor.
     *
     * @param \App\Models\Order $model
     */
    public function __construct(Order $model)
    {
        $this->model = $model;
    }

    /**
     * Get all order items, paginated.
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getAll()
    {
        return $this->model->orderBy('id', 'DESC')->paginate(12);
    }

    /**
     * Find a record item by its ID.
     *
     * @param int $id
     * @return \App\Models\Order|null
     */
    public function find($id)
    {
        return $this->model->find($id);
    }

    /**
     * Get all order items associated with a specific user ID.
     *
     * @param int $user_id
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function findAllByUserId($user_id)
    {
        return $this->model->where('user_id', $user_id)->orderBy('created_at', 'DESC')->paginate(10);
    }

    /**
     * Find a specific order item by user ID and order ID.
     *
     * @param int $user_id
     * @param int $order_id
     * @return \App\Models\OrderItem|null
     */
    public function findByUserIdAndOrderId($user_id, $order_id)
    {
        return $this->model->where('user_id', $user_id)->where('id', $order_id)->first();
    }

    /**
     * Create a new order with the given data.
     *
     * @param array $data
     * @return \App\Models\Order
     */
    public function createOrder(array $data)
    {
        return $this->model::create($data);
    }
}
