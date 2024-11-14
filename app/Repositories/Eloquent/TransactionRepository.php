<?php

namespace App\Repositories\Eloquent;

use App\Models\Transaction;
use App\Repositories\Contracts\TransactionRepositoryInterface;

class TransactionRepository implements TransactionRepositoryInterface
{
    /**
     * The model instance that this repository will interact with.
     *
     * @var \App\Models\Transaction
     */
    protected $model;

    /**
     * TransactionRepository constructor.
     *
     * @param \App\Models\Transaction $model
     */
    public function __construct(Transaction $model)
    {
        $this->model = $model;
    }

    /**
     * Get all transaction items, paginated.
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
     * Find an order item by its order ID.
     *

     * @param int $order_id 
     * @return \App\Models\OrderItem|null
     */
    public function findByOrderId($order_id)
    {
        return $this->model->where('order_id', $order_id)->first();
    }

    /**
     * Create a new transaction with the given data.
     *
     * @param array $data
     * @return \App\Models\Transaction
     */
    public function createTransaction(array $data)
    {
        return Transaction::create($data);
    }

    /**
     * Update the status of a transaction based on the order ID.
     *
     * @param int $orderId
     * @param string $status
     * @return void
     */
    public function updateStatusByOrderId(int $orderId, string $status): void
    {
        $transaction = $this->model->where('order_id', $orderId)->first();

        // Update the transaction status
        if ($transaction) {
            $transaction->status = $status;
            $transaction->save();
        }
    }
}
