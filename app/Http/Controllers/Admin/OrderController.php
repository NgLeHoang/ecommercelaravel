<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\OrderRepositoryInterface;
use App\Repositories\Contracts\OrderItemRepositoryInterface;
use App\Repositories\Contracts\TransactionRepositoryInterface;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Repository for handling order data operations.
     *
     * @var \App\Repositories\Contracts\OrderRepositoryInterface
     */
    protected $orderRepo;

    /**
     * Repository for handling order item data operations.
     *
     * @var \App\Repositories\Contracts\OrderItemRepositoryInterface
     */
    protected $orderItemRepo;

    /**
     * Repository for handling transaction data operations.
     *
     * @var \App\Repositories\Contracts\TransactionRepositoryInterface
     */
    protected $transactionRepo;

    /**
     * Create a new controller instance and inject dependencies.
     *
     * @param \App\Repositories\Eloquent\OrderRepositoryInterface $orderRepo
     * @param \App\Repositories\Eloquent\OrderItemRepositoryInterface $orderItemRepo
     * @param \App\Repositories\Eloquent\TransactionRepositoryInterface $transactionRepo
     */
    public function __construct(
        OrderRepositoryInterface $orderRepo,
        OrderItemRepositoryInterface $orderItemRepo,
        TransactionRepositoryInterface $transactionRepo
    ) {
        $this->orderRepo = $orderRepo;
        $this->orderItemRepo = $orderItemRepo;
        $this->transactionRepo = $transactionRepo;
    }

    /**
     * Display a listing of all orders in the admin view.
     *
     * @return \Illuminate\View\View
     */
    public function orders()
    {
        $orders = $this->orderRepo->getAll();
        return view('admin.orders', compact('orders'));
    }

    /**
     * Display a details of order by id in the admin view.
     *
     * @param int $order_id
     * @return \Illuminate\View\View
     */
    public function orderDetails($order_id)
    {
        $order = $this->orderRepo->find($order_id);
        $orderItems = $this->orderItemRepo->findAllByOrderId($order_id);
        $transaction = $this->transactionRepo->findByOrderId($order_id);

        return view('admin.order-details', compact('order', 'orderItems', 'transaction'));
    }

    /**
     * Update the status of an order and associated transaction if necessary.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateOrderStatus(Request $request)
    {
        // Update the order status and relevant dates
        $this->orderRepo->updateStatus($request->order_id, $request->order_status);

        // If the order was delivered, update the transaction status to 'approved'
        if ($request->order_status == 'delivered') {
            $this->transactionRepo->updateStatusByOrderId($request->order_id, 'approved');
        }

        return back()->with('status', 'Status changed successfully');
    }
}
