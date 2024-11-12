<?php

namespace App\Repositories\Eloquent;

use App\Models\OrderItem;
use App\Repositories\Contracts\OrderItemRepositoryInterface;

class OrderItemRepository implements OrderItemRepositoryInterface
{
    /**
     * The model instance that this repository will interact with.
     *
     * @var \App\Models\OrderItem
     */
    protected $model;

    /**
     * OrderItemRepository constructor.
     *
     * @param \App\Models\OrderItem $model
     */
    public function __construct(OrderItem $model)
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
     * @return \App\Models\OrderItem|null
     */
    public function find($id)
    {
        return $this->model->find($id);
    }

    /**
     * Get all order items associated with a specific order ID.
     *
     * @param int $order_id
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function findAllByOrderId($order_id)
    {
        return $this->model->where('order_id', $order_id)->orderBy('id')->paginate(10);
    }

    /**
     * Create a new order item with the given data.
     *
     * @param array $data
     * @return \App\Models\OrderItem
     */
    public function createOrderItem(array $data)
    {
        return $this->model::create($data);
    }
}
