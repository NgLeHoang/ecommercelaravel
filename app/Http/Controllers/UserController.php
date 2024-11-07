<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        return view('user.index');
    }

    public function orders()
    {
        $orders = Order::where('user_id', Auth::user()->id)->orderBy('created_at', 'DESC')->paginate(10);
        return view('user.orders', compact('orders'));
    }

    public function order_details($order_id)
    {
        $order = Order::where('user_id', Auth::user()->id)->where('id', $order_id)->first();
        if ($order) {
            $orderItems = OrderItem::where('order_id', $order_id)->orderBy('id')->paginate(10);
            $transaction = Transaction::where('order_id', $order_id)->first();
        } else {
            return redirect()->route('login');
        }
        return view('user.order-details', compact('order', 'orderItems', 'transaction'));
    }

    public function order_cancel(Request $request)
    {
        $order = Order::find($request->order_id);
        $order->status = "canceled";
        $order->canceled_date = Carbon::now();
        $order->save();

        return back()->with('status', 'Order has been canceled successfully!');
    }

    public function address()
    {
        $address = Address::where('user_id', Auth::user()->id)->where('is_default', true)->first();
        if ($address) {
            return view('user.address', compact('address'));
        }

        return view('user.address-add');
    }

    public function address_store(Request $request)
    {
        $request->validate([
            'name' => 'required|max:100',
            'phone' => 'required|numeric|digits:10',
            'address' => 'required',
            'locality' => 'required',
            'city' => 'required',
            'district' => 'required',
        ]);

        $address = new Address();
        $address->name = $request->name;
        $address->phone = $request->phone;
        $address->district = $request->district;
        $address->city = $request->city;
        $address->address = $request->address;
        $address->locality = $request->locality;
        $address->country = "Vietnam";
        $address->user_id = Auth::user()->id;
        $address->is_default = $request->is_default;

        $address->save();

        return redirect()->route('user.address')->with('status', 'Address has added successfully');
    }

    public function address_edit($id)
    {
        $address = Address::find($id);
        return view('user.address-edit', compact('address'));
    }

    public function address_update(Request $request)
    {
        $request->validate([
            'name' => 'required|max:100',
            'phone' => 'required|numeric|digits:10',
            'address' => 'required',
            'locality' => 'required',
            'city' => 'required',
            'district' => 'required',
        ]);

        $address = Address::find($request->id);
        $address->name = $request->name;
        $address->phone = $request->phone;
        $address->district = $request->district;
        $address->city = $request->city;
        $address->address = $request->address;
        $address->locality = $request->locality;
        $address->country = "Vietnam";
        $address->user_id = Auth::user()->id;
        $address->is_default = $request->is_default;

        $address->save();

        return redirect()->route('user.address');
    }

    public function account_details()
    {
        return view('user.account-details');
    }

    public function account_save_details(Request $request)
    {
        $user = User::find(Auth::user()->id);

        $request->validate([
            'name' => 'required|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'required|numeric|digits:10',
        ]);

        if ($request->filled('old_password') || $request->filled('new_password')) {
            $request->validate([
                'old_password' => 'required',
                'new_password' => 'required|confirmed',
            ]);

            if (!Hash::check($request->old_password, $user->password)) {
                return back()->withErrors(['old_password' => 'The old password is incorrect.']);
            }
        }

        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->phone;

        if ($request->filled('new_password')) {
            $user->password = Hash::make($request->new_password);
        }

        $user->save();

        return redirect()->route('user.account.details')->with('success', 'User has changed profile succeccfully');
    }
}
