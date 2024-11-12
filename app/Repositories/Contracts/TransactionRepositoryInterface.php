<?php

namespace App\Repositories\Contracts;

interface TransactionRepositoryInterface
{
    /**
     * Get all transaction items, paginated.
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
     * Find an order item by its order ID.
     *

     * @param int $order_id 
     * @return \App\Models\OrderItem|null
     */
    public function findByOrderId($order_id);

    /**
     * Create a new transaction with the given data.
     *
     * @param array $data
     * @return \App\Models\Transaction
     */
    public function createTransaction(array $data);
}
