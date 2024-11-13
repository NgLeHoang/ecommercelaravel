<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Transaction;
use App\Models\Slide;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Intervention\Image\Laravel\Facades\Image;
use App\Repositories\Contracts\DashboardRepositoryInterface;

class AdminController extends Controller
{
    /**
     * Repository for handling dashboard-related data operations.
     *
     * @var \App\Repositories\Eloquent\DashboardRepositoryInterface 
     */
    protected $dashboardRepo;

    /**
     * Create a new controller instance and inject dependencies.
     *
     * @param \App\Repositories\Eloquent\DashboardRepositoryInterface $dashboardRepo
     */
    public function __construct(
        DashboardRepositoryInterface $dashboardRepo
    ) {
        $this->dashboardRepo = $dashboardRepo;
    }

    public function index()
    {
        $orders = Order::orderBy('created_at', 'DESC')->get()->take(10);
        $dashboardDatas = $this->dashboardRepo->getDashboardData();
        $monthlyDatas = $this->dashboardRepo->getMonthlyData();

        function getMonthlyDataString($monthlyDatas, $column)
        {
            return implode(',', collect($monthlyDatas)->pluck($column)->toArray());
        }

        $AmountMonthly = getMonthlyDataString($monthlyDatas, 'TotalAmount');
        $AmountOrderedMonthly = getMonthlyDataString($monthlyDatas, 'TotalOrderedAmount');
        $AmountDeliveredMonthly = getMonthlyDataString($monthlyDatas, 'TotalDeliveredAmount');
        $AmountCanceledMonthly = getMonthlyDataString($monthlyDatas, 'TotalCanceledAmount');

        $TotalAmount = collect($monthlyDatas)->sum('TotalAmount');
        $TotalOrderedAmount = collect($monthlyDatas)->sum('TotalOrderedAmount');
        $TotalDeliveredAmount = collect($monthlyDatas)->sum('TotalDeliveredAmount');
        $TotalCanceledAmount = collect($monthlyDatas)->sum('TotalCanceledAmount');
        return view('admin.index', compact(
            'orders',
            'dashboardDatas',
            'AmountMonthly',
            'AmountOrderedMonthly',
            'AmountDeliveredMonthly',
            'AmountCanceledMonthly',
            'TotalAmount',
            'TotalOrderedAmount',
            'TotalDeliveredAmount',
            'TotalCanceledAmount'
        ));
    }

    public function saveImageToFolder($image, $imageName, $folderName, $width, $height)
    {
        $destinationPath = public_path('uploads/' . $folderName);
        $img = Image::read($image->path());

        //Cover and resize image and then save image to folder
        $img->cover($width, $height, 'top');
        $img->resize($width, $height, function ($constraint) {
            $constraint->aspectRatio();
        })->save($destinationPath . '/' . $imageName);
    }

    //Coupon management
    public function coupons()
    {
        $coupons = Coupon::orderBy('expired_date', 'DESC')->paginate(12);
        return view('admin.coupons', compact('coupons'));
    }

    public function addCoupon()
    {
        return view('admin.coupon-add');
    }

    public function storeCoupon(Request $request)
    {
        $request->validate([
            'code' => 'required',
            'type' => 'required',
            'value' => 'required|numeric',
            'cart_value' => 'required|numeric',
            'expired_date' => 'required|date'
        ]);

        $coupon = new Coupon();
        $coupon->code = $request->code;
        $coupon->type = $request->type;
        $coupon->value = $request->value;
        $coupon->cart_value = $request->cart_value;
        $coupon->expired_date = $request->expired_date;

        $coupon->save();

        return redirect()->route('admin.coupons')->with('status', 'Coupon has added sucessfully');
    }

    public function editCoupon($id)
    {
        $coupon = Coupon::find($id);
        return view('admin.coupon-edit', compact('coupon'));
    }

    public function updateCoupon(Request $request)
    {
        $request->validate([
            'code' => 'required',
            'type' => 'required',
            'value' => 'required|numeric',
            'cart_value' => 'required|numeric',
            'expired_date' => 'required|date',
        ]);

        $coupon = Coupon::find($request->id);
        $coupon->code = $request->code;
        $coupon->type = $request->type;
        $coupon->value = $request->value;
        $coupon->cart_value = $request->cart_value;
        $coupon->expired_date = $request->expired_date;

        $coupon->save();

        return redirect()->route('admin.coupons')->with('status', 'Coupon has updated successfully');
    }

    public function deleteCoupon($id)
    {
        $coupon = Coupon::find($id);
        $coupon->delete();

        return redirect()->route('admin.coupons')->with('status', 'Coupon has deleted successfully');
    }

    //Order management
    public function orders()
    {
        $orders = Order::orderBy('created_at', 'DESC')->paginate(12);
        return view('admin.orders', compact('orders'));
    }

    public function orderDetails($order_id)
    {
        $order = Order::find($order_id);
        $orderItems = OrderItem::where('order_id', $order_id)->orderBy('id')->paginate(12);
        $transaction = Transaction::where('order_id', $order_id)->first();

        return view('admin.order-details', compact('order', 'orderItems', 'transaction'));
    }

    public function updateOrderStatus(Request $request)
    {
        $order = Order::find($request->order_id);
        $order->status = $request->order_status;
        if ($request->order_status == 'delivered') {
            $order->delivered_date = Carbon::now();
        } elseif ($request->order_status == 'canceled') {
            $order->canceled_date = Carbon::now();
        }

        $order->save();

        if ($request->order_status == 'delivered') {
            $transaction = Transaction::where('order_id', $request->order_id)->first();
            $transaction->status = 'approved';
            $transaction->save();
        }

        return back()->with('status', 'Status changed successfully');
    }

    //Slide management
    public function slides()
    {
        $slides = Slide::orderBy('id', 'DESC')->paginate(12);
        return view('admin.slides', compact('slides'));
    }

    public function addSlide()
    {
        return view('admin.slide-add');
    }

    public function storeSlide(Request $request)
    {
        $request->validate([
            'tagline' => 'required',
            'title' => 'required',
            'subtitle' => 'required',
            'link' => 'required',
            'image' => 'required|mimes:jpg,jpeg,png|max:2048',
            'status' => 'required'
        ]);

        $slide = new Slide();
        $slide->tagline = $request->tagline;
        $slide->title = $request->title;
        $slide->subtitle = $request->subtitle;
        $slide->link = $request->link;
        $slide->status = $request->status;

        $image = $request->file('image');
        $fileExtension = $image->extension();
        $fileName = Carbon::now()->timestamp . '.' . $fileExtension;
        $this->saveImageToFolder($image, $fileName, 'slides', 400, 690);
        $slide->image = $fileName;
        $slide->save();

        return redirect()->route('admin.slides')->with('status', 'Slide has added successfully');
    }

    public function editSlide($id)
    {
        $slide = Slide::find($id);
        return view('admin.slide-edit', compact('slide'));
    }

    public function updateSlide(Request $request)
    {
        $request->validate([
            'tagline' => 'required',
            'title' => 'required',
            'subtitle' => 'required',
            'link' => 'required',
            'image' => 'mimes:jpg,jpeg,png|max:2048',
            'status' => 'required'
        ]);

        $slide = Slide::find($request->id);
        $slide->tagline = $request->tagline;
        $slide->title = $request->title;
        $slide->subtitle = $request->subtitle;
        $slide->link = $request->link;
        $slide->status = $request->status;

        if ($request->hasFile('image')) {
            $img = public_path('uploads/slides') . '/' . $slide->image;
            if (File::exists($img)) File::delete($img);

            $image = $request->file('image');
            $fileExtension = $image->extension();
            $fileName = Carbon::now()->timestamp . '.' . $fileExtension;
            $this->saveImageToFolder($image, $fileName, 'slides', 400, 690);
            $slide->image = $fileName;
        }
        $slide->save();

        return redirect()->route('admin.slides')->with('status', 'Slide has updated successfully');
    }

    public function deleteSlide($id)
    {
        $slide = Slide::find($id);
        $img = public_path('uploads/slides') . '/' . $slide->image;
        if (File::exists($img)) {
            File::delete($img);
        }
        $slide->delete();

        return redirect()->route('admin.slides')->with('status', 'Slide has deleted successfully!');
    }

    //Contact management
    public function contacts()
    {
        $contacts = Contact::orderBy('created_at', 'DESC')->paginate(10);
        return view('admin.contacts', compact('contacts'));
    }

    public function deleteContact($id)
    {
        $contact = Contact::find($id);
        $contact->delete();
        return redirect()->route('admin.contacts')->with('status', 'Contact message has deleted successfully!');
    }
}
