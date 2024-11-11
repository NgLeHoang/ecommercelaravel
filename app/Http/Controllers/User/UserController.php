<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Repositories\Contracts\OrderRepositoryInterface;
use App\Repositories\Contracts\OrderItemRepositoryInterface;
use App\Repositories\Contracts\TransactionRepositoryInterface;
use App\Repositories\Contracts\AddressRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;

class UserController extends Controller
{
    /**
     * Repository for handling product-related data operations.
     *
     * @var \App\Repositories\Eloquent\OrderRepositoryInterface
     * @var \App\Repositories\Eloquent\OrderItemRepositoryInterface
     * @var \App\Repositories\Eloquent\TransactionRepositoryInterface
     * @var \App\Repositories\Eloquent\AddressRepositoryInterface
     * @var \App\Repositories\Eloquent\UserRepositoryInterface
     */
    protected $orderRepo;
    protected $orderItemRepo;
    protected $transactionRepo;
    protected $addressRepo;
    protected $userRepo;

    /**
     * Create a new controller instance and inject dependencies.
     *
     * @param \App\Repositories\Eloquent\OrderRepositoryInterface $orderRepo
     * @param \App\Repositories\Eloquent\OrderItemRepositoryInterface $orderItemRepo
     * @param \App\Repositories\Eloquent\TransactionRepositoryInterface $transactionRepo
     * @param \App\Repositories\Eloquent\AddressRepositoryInterface $addressRepo
     * @param \App\Repositories\Eloquent\UserRepositoryInterface $userRepo
     */
    public function __construct(
        OrderRepositoryInterface $orderRepo,
        OrderItemRepositoryInterface $orderItemRepo,
        TransactionRepositoryInterface $transactionRepo,
        AddressRepositoryInterface $addressRepo,
        UserRepositoryInterface $userRepo
    ) {
        $this->orderRepo = $orderRepo;
        $this->orderItemRepo = $orderItemRepo;
        $this->transactionRepo = $transactionRepo;
        $this->addressRepo = $addressRepo;
        $this->userRepo = $userRepo;
    }

    /**
     * Display the main dashboard or index page for the authenticated user.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('user.index');
    }

    /**
     * Display a paginated list of the authenticated user's orders.
     *
     * @return \Illuminate\View\View
     */
    public function getUserOrders()
    {
        $orders = $this->orderRepo->findAllByUserId(Auth::user()->id);
        return view('user.orders', compact('orders'));
    }

    /**
     * Display the details of a specific order for the authenticated user.
     *
     * @param int $order_id
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function showOrderDetails($order_id)
    {
        $order = $this->orderRepo->findByUserIdAndOrderId(Auth::user()->id, $order_id);
        if ($order) {
            $orderItems = $this->orderItemRepo->findAllByOrderId($order_id);
            $transaction = $this->transactionRepo->findByOrderId($order_id);
        } else {
            return redirect()->route('login');
        }
        return view('user.order-details', compact('order', 'orderItems', 'transaction'));
    }

    /**
     * Cancel a specific order for the authenticated user.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function cancelUserOrder(Request $request)
    {
        $order = $this->orderRepo->find($request->id);

        $order->status = 'canceled';
        $order->canceled_date = Carbon::now();
        $order->save();

        return back()->with('status', 'Order has been canceled successfully!');
    }

    /**
     * Display the default address for the authenticated user.
     *
     * @return \Illuminate\View\View
     */
    public function showUserAddress()
    {
        $address = $this->addressRepo->findByUserId(Auth::user()->id);
        if ($address) {
            return view('user.address', compact('address'));
        }

        return view('user.address-add');
    }

    /**
     * Store a new address for the authenticated user.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeUserAddress(Request $request)
    {
        $request->validate([
            'name' => 'required|max:100',
            'phone' => 'required|numeric|digits:10',
            'address' => 'required',
            'locality' => 'required',
            'city' => 'required',
            'district' => 'required',
        ]);

        $this->addressRepo->storeAddress($request->only([
            'name',
            'phone',
            'district',
            'city',
            'address',
            'locality',
            'is_default'
        ]));

        return redirect()->route('user.address')->with('status', 'Address has added successfully');
    }

    /**
     * Display the form to edit the specified address for the authenticated user.
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function editUserAddress($id)
    {
        $address = $this->addressRepo->find($id);
        return view('user.address-edit', compact('address'));
    }

    /**
     * Update the specified address for the authenticated user.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateUserAddress(Request $request)
    {
        $request->validate([
            'name' => 'required|max:100',
            'phone' => 'required|numeric|digits:10',
            'address' => 'required',
            'locality' => 'required',
            'city' => 'required',
            'district' => 'required',
        ]);

        $updated = $this->addressRepo->updateAddress($request->id, $request->only([
            'name',
            'phone',
            'district',
            'city',
            'address',
            'locality',
            'is_default'
        ]));

        if ($updated) {
            return redirect()->route('user.address')->with('status', 'Address updated successfully');
        }

        return redirect()->route('user.address')->with('error', 'Failed to update address');
    }

    /**
     * Display the account details for the authenticated user.
     *
     * @return \Illuminate\View\View
     */
    public function accountDetails()
    {
        return view('user.account-details');
    }

    /**
     * Update the authenticated user's account details.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateUserAccountDetails(Request $request)
    {
        $user = $this->userRepo->getAuthenticatedUser(); 

        $request->validate([
            'name' => 'required|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'required|numeric|digits:10',
        ]);

        // Check if the user wants to change the password
        if ($request->filled('old_password') || $request->filled('new_password')) {
            $request->validate([
                'old_password' => 'required',
                'new_password' => 'required|confirmed',
            ]);
        }

        // Call the repository method to update the user account details
        $updated = $this->userRepo->updateAccountDetails($user->id, $request->only([
            'name',
            'email',
            'phone',
            'old_password',
            'new_password'
        ]));

        if ($updated) {
            return redirect()->route('user.account.details')->with('success', 'User profile updated successfully');
        }

        return back()->withErrors(['old_password' => 'The old password is incorrect.']);
    }
}
