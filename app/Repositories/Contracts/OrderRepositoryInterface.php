<?php

namespace App\Repositories\Contracts;

interface OrderRepositoryInterface
{
    /**
     * Get all order items, paginated.
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getAll();

    /**
     * Retrieve the latest 10 records for display on the admin page.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getForAdminPage();

    /**
     * Find a record item by its ID.
     *
     * @param int $id
     * @return \App\Models\Order|null
     */
    public function find($id);

    /**
     * Get all order items associated with a specific user ID.
     *
     * @param int $user_id
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function findAllByUserId($user_id);

    /**
     * Find a specific order item by user ID and order ID.
     *
     * @param int $user_id
     * @param int $order_id
     * @return \App\Models\OrderItem|null
     */
    public function findByUserIdAndOrderId($user_id, $order_id);

    /**
     * Create a new order with the given data.
     *
     * @param array $data
     * @return \App\Models\Order
     */
    public function createOrder(array $data);

    /**
     * Update the status and dates of an order based on the new status.
     *
     * @param int $orderId
     * @param string $status
     * @return void
     */
    public function updateStatus(int $orderId, string $status): void;
}
