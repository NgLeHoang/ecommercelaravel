<?php

namespace App\Repositories\Contracts;

interface OrderItemRepositoryInterface
{
    /**
     * Get all order items, paginated.
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getAll();

    /**
     * Find a record item by its ID.
     *
     * @param int $id
     * @return \App\Models\OrderItem|null
     */
    public function find($id);

    /**
     * Get all order items associated with a specific order ID.
     *
     * @param int $order_id
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function findAllByOrderId($order_id);

    /**
     * Create a new order item with the given data.
     *
     * @param array $data
     * @return \App\Models\OrderItem
     */
    public function createOrderItem(array $data);
}
