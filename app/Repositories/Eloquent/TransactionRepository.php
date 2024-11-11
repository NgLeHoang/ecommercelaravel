<?php

namespace App\Repositories\Eloquent;

use App\Models\Transaction;
use App\Repositories\Contracts\TransactionRepositoryInterface;

class TransactionRepository implements TransactionRepositoryInterface
{
    protected $model;

    public function __construct(Transaction $model)
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

    public function findByOrderId($order_id)
    {
        return $this->model->where('order_id', $order_id)->first();
    }
}
